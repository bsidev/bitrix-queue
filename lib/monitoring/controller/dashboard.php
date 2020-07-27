<?php

namespace Bsi\Queue\Monitoring\Controller;

use Bitrix\Main\Error;
use Bitrix\Main\Request;
use Bsi\Queue\Monitoring\ConsumerCounter;
use Bsi\Queue\Monitoring\MessageStats;
use Bsi\Queue\Monitoring\MessageStatuses;
use Bsi\Queue\Monitoring\Repository\MessageStatsRepositoryInterface;
use Bsi\Queue\Stamp\UuidStamp;
use Bsi\Queue\Utils\ChartIntervalCalculator;
use Symfony\Component\Messenger\Stamp\BusNameStamp;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class Dashboard extends AbstractController
{
    /** @var MessageStatsRepositoryInterface */
    private $messageStatsRepository;
    /** @var ConsumerCounter */
    private $consumerCounter;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        $this->messageStatsRepository = $this->getAdapter()->getMessageStatsRepository();
        $this->consumerCounter = new ConsumerCounter();
    }

    public function summaryAction(string $from, string $to): array
    {
        $fromDate = new \DateTimeImmutable($from);
        $toDate = new \DateTimeImmutable($to);

        return [
            'consumers' => $this->consumerCounter->get(),
            'sent' => $this->messageStatsRepository->countSent($fromDate, $toDate),
            'received' => $this->messageStatsRepository->countReceived($fromDate, $toDate),
            'handled' => $this->messageStatsRepository->countHandled($fromDate, $toDate),
            'failed' => $this->messageStatsRepository->countFailed($fromDate, $toDate),
        ];
    }

    public function queryRangeAction(string $from, string $to): ?array
    {
        $fromTs = strtotime($from);
        if ($fromTs === false) {
            $this->errorCollection->setError(new Error(sprintf('Invalid time %s', $from)));
        }
        $toTs = strtotime($to);
        if ($toTs === false) {
            $this->errorCollection->setError(new Error(sprintf('Invalid time %s', $to)));
        }
        if (!$this->errorCollection->isEmpty()) {
            return null;
        }

        $intervalCalculator = new ChartIntervalCalculator(200);
        $interval = $intervalCalculator->calculate($fromTs, $toTs);

        $fromTs = floor($fromTs / $interval) * $interval;
        $toTs = floor($toTs / $interval) * $interval;

        $fromDate = new \DateTimeImmutable('@' . $fromTs);
        $toDate = new \DateTimeImmutable('@' . $toTs);

        $sentDataset = $this->messageStatsRepository->getSentChartDataset($fromDate, $toDate, $interval);
        $receivedDataset = $this->messageStatsRepository->getReceivedChartDataset($fromDate, $toDate, $interval);
        $handledDataset = $this->messageStatsRepository->getHandledChartDataset($fromDate, $toDate, $interval);
        $failedDataset = $this->messageStatsRepository->getFailedChartDataset($fromDate, $toDate, $interval);

        return [
            ['status' => MessageStatuses::SENT, 'values' => $sentDataset],
            ['status' => MessageStatuses::RECEIVED, 'values' => $receivedDataset],
            ['status' => MessageStatuses::HANDLED, 'values' => $handledDataset],
            ['status' => MessageStatuses::FAILED, 'values' => $failedDataset],
        ];
    }

    public function recentMessagesAction(string $from, string $to, int $pageSize = 10, int $page = 1): ?array
    {
        $fromDate = new \DateTimeImmutable($from);
        $toDate = new \DateTimeImmutable($to);
        $offset = ($page - 1) * $pageSize;

        $collection = $this->messageStatsRepository->getRecentList($fromDate, $toDate, $pageSize, $offset);

        $data = [];
        /** @var MessageStats $messageStats */
        foreach ($collection as $messageStats) {
            $envelope = $messageStats->getEnvelope();

            $uuidStamp = $envelope->last(UuidStamp::class);

            $busNames = [];
            /** @var BusNameStamp $busNameStamp */
            foreach ($envelope->all(BusNameStamp::class) as $busNameStamp) {
                $busNames[] = $busNameStamp->getBusName();
            }

            $sentAt = $messageStats->getSentAt();
            $receivedAt = $messageStats->getReceivedAt();
            $handledAt = $messageStats->getHandledAt();
            $failedAt = $messageStats->getFailedAt();

            $data[] = [
                'uuid' => $uuidStamp instanceof UuidStamp ? $uuidStamp->getUuid()->toString() : null,
                'message' => get_class($envelope->getMessage()),
                'data' => serialize($envelope->getMessage()),
                'status' => $messageStats->getStatus(),
                'transport_name' => $messageStats->getTransportName(),
                'buses' => $busNames,
                'error' => $messageStats->getError(),
                'sent_at' => $sentAt->format(\DateTime::ATOM),
                'received_at' => $receivedAt ? $receivedAt->format(\DateTime::ATOM) : null,
                'handled_at' => $handledAt ? $handledAt->format(\DateTime::ATOM) : null,
                'failed_at' => $failedAt ? $failedAt->format(\DateTime::ATOM) : null,
            ];
        }

        return [
            'data' => $data,
            'total' => $this->messageStatsRepository->countAll($fromDate, $toDate),
        ];
    }
}

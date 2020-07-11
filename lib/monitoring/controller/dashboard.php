<?php

namespace Bsi\Queue\Monitoring\Controller;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class Dashboard extends AbstractController
{
    public function summaryAction(string $from = null, string $to = null): array
    {
        $fromDate = new \DateTimeImmutable($from);
        $toDate = new \DateTimeImmutable($to);

        $metricRepository = $this->adapter->getMetricRepository();

        return [
            'sent' => $metricRepository->countSentMessages($fromDate, $toDate),
            'received' => $metricRepository->countReceivedMessages($fromDate, $toDate),
            'handled' => $metricRepository->countHandledMessages($fromDate, $toDate),
            'failed' => $metricRepository->countFailedMessages($fromDate, $toDate),
        ];
    }
}

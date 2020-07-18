<?php

namespace Bsi\Queue\Monitoring\Repository;

use Bsi\Queue\Monitoring\MessageStatsCollection;
use Symfony\Component\Messenger\Envelope;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
interface MessageStatsRepositoryInterface
{
    /**
     * Returns a count of all messages.
     *
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     *
     * @return int
     */
    public function countAll(\DateTimeInterface $from, \DateTimeInterface $to): int;

    /**
     * Returns a count of sent messages.
     *
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     *
     * @return int
     */
    public function countSent(\DateTimeInterface $from, \DateTimeInterface $to): int;

    /**
     * Returns a count of received messages.
     *
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     *
     * @return int
     */
    public function countReceived(\DateTimeInterface $from, \DateTimeInterface $to): int;

    /**
     * Returns a count of handled messages.
     *
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     *
     * @return int
     */
    public function countHandled(\DateTimeInterface $from, \DateTimeInterface $to): int;

    /**
     * Returns a count of failed messages.
     *
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     *
     * @return int
     */
    public function countFailed(\DateTimeInterface $from, \DateTimeInterface $to): int;

    /**
     * Returns a chart dataset of sent messages.
     *
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @param int $interval
     *
     * @return array of the format [[$timestamp, $value]]
     */
    public function getSentChartDataset(\DateTimeInterface $from, \DateTimeInterface $to, int $interval): array;

    /**
     * Returns a chart dataset of received messages.
     *
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @param int $interval
     *
     * @return array of the format [[$timestamp, $value]]
     */
    public function getReceivedChartDataset(\DateTimeInterface $from, \DateTimeInterface $to, int $interval): array;

    /**
     * Returns a chart dataset of handled messages.
     *
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @param int $interval
     *
     * @return array of the format [[$timestamp, $value]]
     */
    public function getHandledChartDataset(\DateTimeInterface $from, \DateTimeInterface $to, int $interval): array;

    /**
     * Returns a chart dataset of failed messages.
     *
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @param int $interval
     *
     * @return array of the format [[$timestamp, $value]]
     */
    public function getFailedChartDataset(\DateTimeInterface $from, \DateTimeInterface $to, int $interval): array;

    /**
     * Returns a collection of recent message stats.
     *
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @param int $limit
     * @param int $offset
     *
     * @return MessageStatsCollection
     */
    public function getRecentList(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        int $limit,
        int $offset
    ): MessageStatsCollection;
}

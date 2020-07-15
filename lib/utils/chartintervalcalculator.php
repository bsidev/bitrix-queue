<?php

namespace Bsi\Queue\Utils;

class ChartIntervalCalculator
{
    /** @var int */
    private $resolution;

    public function __construct(int $resolution = 100)
    {
        $this->resolution = $resolution;
    }

    public function calculate(int $from, int $to): int
    {
        return $this->roundInterval(($to - $from) / $this->resolution);
    }

    private function roundInterval(int $interval): int
    {
        // 2s
        if ($interval < 2) {
            return 1; // 1s
        }
        // 4s
        if ($interval < 4) {
            return 2; // 2s
        }
        // 8s
        if ($interval < 8) {
            return 5; // 5s
        }
        // 13s
        if ($interval < 13) {
            return 10; // 10s
        }
        // 18s
        if ($interval < 18) {
            return 15; // 15s
        }
        // 25s
        if ($interval < 25) {
            return 20; // 20s
        }
        // 45s
        if ($interval < 45) {
            return 30; // 30s
        }
        // 1.5m
        if ($interval < 90) {
            return 60; // 1m
        }
        // 3.5m
        if ($interval < 120) {
            return 120; // 2m
        }
        // 7.5m
        if ($interval < 450) {
            return 300; // 5m
        }
        // 12.5m
        if ($interval < 750) {
            return 600; // 10m
        }
        // 17.5m
        if ($interval < 1050) {
            return 900; // 15m
        }
        // 25m
        if ($interval < 1500) {
            return 1200; // 20m
        }
        // 45m
        if ($interval < 2700) {
            return 1800; // 30m
        }
        // 1.5h
        if ($interval < 5400) {
            return 3600; // 1h
        }
        // 2.5h
        if ($interval < 9000) {
            return 7200; // 2h
        }
        // 4.5h
        if ($interval < 16200) {
            return 10800; // 3h
        }
        // 9h
        if ($interval < 32400) {
            return 21600; // 6h
        }
        // 1d
        if ($interval < 86400) {
            return 43200; // 12h
        }
        // 1w
        if ($interval < 604800) {
            return 86400; // 1d
        }
        // 3w
        if ($interval < 1814400) {
            return 604800; // 1w
        }
        // 6w
        if ($interval < 3628800) {
            return 2592000; // 30d
        }

        return 31536000; // 1y
    }
}

<?php

namespace Bsi\Queue\Monitoring;

use Symfony\Component\Process\Process;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class ConsumerCounter
{
    /**
     * Returns a count of running consumers.
     *
     * @param string $command
     *
     * @return int
     */
    public function get(string $command = 'messenger:consume'): int
    {
        $process = Process::fromShellCommandline(
            "ps aux | grep -v grep | grep '{$command}'",
            null,
            ['COLUMNS' => '2000']
        );

        $process->run();

        return substr_count($process->getOutput(), $command);
    }
}

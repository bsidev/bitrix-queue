<?php

namespace Bsi\Queue\Monitoring;

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
        $output = shell_exec(sprintf('ps aux | grep -v grep | grep \'%s\'', escapeshellarg($command)));

        return substr_count($output ?: '', $command);
    }
}

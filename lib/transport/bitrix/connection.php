<?php

namespace Bsi\Queue\Transport\Bitrix;

use Bitrix\Main\Application;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\DateTime;
use Bsi\Queue\Entity\MessageTable;
use Symfony\Component\Messenger\Exception\InvalidArgumentException;
use Symfony\Component\Messenger\Exception\TransportException;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class Connection
{
    protected const DEFAULT_OPTIONS = [
        'queue_name' => 'default',
        'redeliver_timeout' => 3600,
    ];

    /**
     * Configuration of the connection.
     *
     * Available options:
     *
     * * queue_name: name of the queue
     * * redeliver_timeout: Timeout before redeliver messages still in handling state (i.e: delivered_at is not null and message is still in table). Default: 3600
     */
    protected $configuration = [];

    public function __construct(array $configuration = [])
    {
        $optionsExtraKeys = array_diff(array_keys($configuration), array_keys(static::DEFAULT_OPTIONS));
        if (count($optionsExtraKeys) > 0) {
            throw new InvalidArgumentException(sprintf('Unknown option found: [%s]. Allowed options are [%s].', implode(', ', $optionsExtraKeys), implode(', ', array_keys(static::DEFAULT_OPTIONS))));
        }

        $this->configuration = array_replace_recursive(static::DEFAULT_OPTIONS, $configuration);
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function send(string $body, array $headers, int $delay = 0): int
    {
        $now = new DateTime();
        $availableAt = (clone $now)->add(sprintf('+%d seconds', $delay / 1000));

        $result = MessageTable::add([
            'BODY' => $body,
            'HEADERS' => $headers,
            'QUEUE_NAME' => $this->configuration['queue_name'],
            'CREATED_AT' => $now,
            'AVAILABLE_AT' => $availableAt,
        ]);
        if (!$result->isSuccess()) {
            throw new TransportException(implode("\n", $result->getErrorMessages()), 0);
        }

        return (int) $result->getId();
    }

    public function get(): ?array
    {
        $driverConnection = Application::getConnection(MessageTable::getConnectionName());

        $driverConnection->startTransaction();
        try {
            $query = $this->createAvailableMessagesQuery()
                ->addOrder('AVAILABLE_AT', 'ASC')
                ->setLimit(1);

            $bitrixEnvelope = $query->exec()->fetch();

            if ($bitrixEnvelope === false || $bitrixEnvelope === null) {
                return null;
            }

            $result = MessageTable::update($bitrixEnvelope['ID'], ['DELIVERED_AT' => new DateTime()]);
            if (!$result->isSuccess()) {
                throw new TransportException(implode("\n", $result->getErrorMessages()), 0);
            }

            $driverConnection->commitTransaction();

            return $bitrixEnvelope;
        } catch (\Throwable $e) {
            $driverConnection->rollbackTransaction();
            throw $e;
        }
    }

    public function ack(int $id): bool
    {
        $result = MessageTable::delete($id);
        if (!$result->isSuccess()) {
            throw new TransportException(implode("\n", $result->getErrorMessages()), 0);
        }

        return true;
    }

    public function reject(int $id): bool
    {
        return $this->ack($id);
    }

    public function getMessageCount(): int
    {
        $query = $this->createAvailableMessagesQuery()
            ->setSelect(['CNT' => Query::expr()->count('ID')])
            ->setLimit(1);

        $data = $query->exec()->fetch();

        return (int) $data['CNT'];
    }

    public function findAll(int $limit = null): array
    {
        $query = $this->createAvailableMessagesQuery();
        if ($limit !== null) {
            $query->setLimit($limit);
        }

        return $query->fetchAll();
    }

    public function find(int $id): ?array
    {
        return MessageTable::getRowById($id);
    }

    private function createAvailableMessagesQuery(): Query
    {
        $now = new DateTime();
        $redeliverLimit = (clone $now)->add(sprintf('-%d seconds', $this->configuration['redeliver_timeout']));

        return MessageTable::query()
            ->setSelect(['*'])
            ->where(
                Query::filter()->logic('OR')
                    ->whereNull('DELIVERED_AT')
                    ->where('DELIVERED_AT', '<', $redeliverLimit)
            )
            ->where('AVAILABLE_AT', '<=', $now)
            ->where('QUEUE_NAME', $this->configuration['queue_name']);
    }
}

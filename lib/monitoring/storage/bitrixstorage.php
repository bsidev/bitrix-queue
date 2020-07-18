<?php

namespace Bsi\Queue\Monitoring\Storage;

use Bitrix\Main\Type\DateTime;
use Bsi\Queue\Exception\InvalidArgumentException;
use Bsi\Queue\Exception\LogicException;
use Bsi\Queue\Exception\RuntimeException;
use Bsi\Queue\Monitoring\Adapter\Bitrix\BitrixMessageStatTable;
use Bsi\Queue\Monitoring\MessageStatuses;
use Bsi\Queue\Stamp\UuidStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class BitrixStorage implements StorageInterface
{
    /** @var SerializerInterface */
    private $serializer;

    public function __construct(SerializerInterface $serializer = null)
    {
        $this->serializer = $serializer ?? new PhpSerializer();
    }

    public function pushSentMessageStats(Envelope $envelope): void
    {
        /** @var UuidStamp|null $uuidStamp */
        $uuidStamp = $envelope->last(UuidStamp::class);
        if ($uuidStamp === null) {
            throw new LogicException('No UuidStamp found on the Envelope.');
        }
        $uuid = $uuidStamp->getUuid()->toString();


        $encodedMessage = $this->serializer->encode($envelope);

        $result = BitrixMessageStatTable::add([
            'UUID' => $uuid,
            'MESSAGE' => get_class($envelope->getMessage()),
            'STATUS' => MessageStatuses::SENT,
            'BODY' => $encodedMessage['body'],
            'HEADERS' => $encodedMessage['headers'] ?? [],
        ]);
        if (!$result->isSuccess()) {
            throw new RuntimeException(implode("\n", $result->getErrorMessages()));
        }
    }

    public function pushConsumedMessageStats(
        Envelope $envelope,
        string $status,
        string $transportName,
        \Throwable $error = null
    ): void {
        /** @var UuidStamp|null $uuidStamp */
        $uuidStamp = $envelope->last(UuidStamp::class);
        if ($uuidStamp === null) {
            throw new LogicException('No UuidStamp found on the Envelope.');
        }
        $uuid = $uuidStamp->getUuid()->toString();

        $row = BitrixMessageStatTable::getRowByUuid($uuid);
        if ($row === null) {
            throw new RuntimeException(sprintf('Envelope with uuid "%s" not found.', $uuid));
        }

        $encodedMessage = $this->serializer->encode($envelope);

        $data = [
            'STATUS' => $status,
            'BODY' => $encodedMessage['body'],
            'HEADERS' => $encodedMessage['headers'] ?? [],
            'TRANSPORT_NAME' => $transportName,
        ];

        if ($status === MessageStatuses::RECEIVED) {
            $data['RECEIVED_AT'] = new DateTime();
        } elseif ($status === MessageStatuses::HANDLED) {
            $data['HANDLED_AT'] = new DateTime();
        } elseif ($status === MessageStatuses::FAILED) {
            $data['FAILED_AT'] = new DateTime();
        } else {
            throw new InvalidArgumentException(sprintf('The given status is invalid: %s', $status));
        }

        if ($error) {
            $data['ERROR'] = (string) $error;
        }

        $result = BitrixMessageStatTable::update($row['ID'], $data);
        if (!$result->isSuccess()) {
            throw new RuntimeException(implode("\n", $result->getErrorMessages()));
        }
    }
}

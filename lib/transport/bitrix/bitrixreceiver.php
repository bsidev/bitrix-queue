<?php

namespace Bsi\Queue\Transport\Bitrix;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\LogicException;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class BitrixReceiver implements ReceiverInterface, MessageCountAwareInterface, ListableReceiverInterface
{
    private const MAX_RETRIES = 3;
    /** @var int */
    private $retryingSafetyCounter = 0;
    /** @var Connection */
    private $connection;
    /** @var SerializerInterface */
    private $serializer;

    public function __construct(Connection $connection, SerializerInterface $serializer = null)
    {
        $this->connection = $connection;
        $this->serializer = $serializer ?? new PhpSerializer();
    }

    /**
     * {@inheritdoc}
     */
    public function get(): iterable
    {
        try {
            $bitrixEnvelope = $this->connection->get();
            $this->retryingSafetyCounter = 0;
        } catch (TransportException $exception) {
            if (++$this->retryingSafetyCounter >= self::MAX_RETRIES) {
                $this->retryingSafetyCounter = 0;
                throw new TransportException($exception->getMessage(), 0, $exception);
            }

            return [];
        }

        if ($bitrixEnvelope === null) {
            return [];
        }

        return [$this->createEnvelopeFromData($bitrixEnvelope)];
    }

    /**
     * {@inheritdoc}
     */
    public function ack(Envelope $envelope): void
    {
        $this->connection->ack($this->findBitrixReceivedStamp($envelope)->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function reject(Envelope $envelope): void
    {
        $this->connection->reject($this->findBitrixReceivedStamp($envelope)->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageCount(): int
    {
        return $this->connection->getMessageCount();
    }

    /**
     * {@inheritdoc}
     */
    public function all(int $limit = null): iterable
    {
        $bitrixEnvelopes = $this->connection->findAll($limit);

        foreach ($bitrixEnvelopes as $bitrixEnvelope) {
            yield $this->createEnvelopeFromData($bitrixEnvelope);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function find($id): ?Envelope
    {
        $bitrixEnvelope = $this->connection->find($id);

        if ($bitrixEnvelope === null) {
            return null;
        }

        return $this->createEnvelopeFromData($bitrixEnvelope);
    }

    private function findBitrixReceivedStamp(Envelope $envelope): BitrixReceivedStamp
    {
        /** @var BitrixReceivedStamp|null $bitrixReceivedStamp */
        $bitrixReceivedStamp = $envelope->last(BitrixReceivedStamp::class);

        if ($bitrixReceivedStamp === null) {
            throw new LogicException('No BitrixReceivedStamp found on the Envelope.');
        }

        return $bitrixReceivedStamp;
    }

    private function createEnvelopeFromData(array $data): Envelope
    {
        try {
            $envelope = $this->serializer->decode([
                'body' => $data['BODY'],
                'headers' => $data['HEADERS'],
            ]);
        } catch (MessageDecodingFailedException $exception) {
            $this->connection->reject($data['ID']);

            throw $exception;
        }

        return $envelope->with(
            new BitrixReceivedStamp($data['ID']),
            new TransportMessageIdStamp($data['ID'])
        );
    }
}

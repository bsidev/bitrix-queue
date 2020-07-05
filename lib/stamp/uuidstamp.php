<?php

namespace Bsi\Queue\Stamp;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class UuidStamp implements StampInterface
{
    /** @var UuidInterface */
    private $uuid;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }
}

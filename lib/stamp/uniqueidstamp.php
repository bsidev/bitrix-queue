<?php

namespace Bsi\Queue\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
final class UniqueIdStamp implements StampInterface
{
    /** @var string */
    private $uniqueId;

    public function __construct()
    {
        $this->uniqueId = bin2hex(random_bytes(16));
    }

    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }
}

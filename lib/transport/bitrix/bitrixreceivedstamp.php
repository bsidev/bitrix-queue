<?php

namespace Bsi\Queue\Transport\Bitrix;

use Symfony\Component\Messenger\Stamp\NonSendableStampInterface;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class BitrixReceivedStamp implements NonSendableStampInterface
{
    private $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }
}

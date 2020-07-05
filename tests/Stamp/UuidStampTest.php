<?php

namespace Bsi\Queue\Tests\Stamp;

use Bsi\Queue\Stamp\UuidStamp;
use Bsi\Queue\Tests\AbstractTestCase;
use Ramsey\Uuid\Uuid;

class UuidStampTest extends AbstractTestCase
{
    public function testIsValid(): void
    {
        $stamp = new UuidStamp();

        $this->assertTrue(true, Uuid::isValid($stamp->getUuid()->toString()));
    }
}

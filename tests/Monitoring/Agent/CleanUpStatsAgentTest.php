<?php

namespace Bsi\Queue\Tests\Monitoring\Agent;

use Bsi\Queue\Monitoring\Agent\CleanUpStatsAgent;
use Bsi\Queue\Tests\AbstractTestCase;
use Mockery;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CleanUpStatsAgentTest extends AbstractTestCase
{
    public function testItsReturnsValidSelf(): void
    {
        $mock = Mockery::mock('overload:Bitrix\Main\Config\Option');
        $mock->shouldReceive('get')->andReturn(1);

        $this->assertSame(CleanUpStatsAgent::class . '::run();', CleanUpStatsAgent::run());
    }
}

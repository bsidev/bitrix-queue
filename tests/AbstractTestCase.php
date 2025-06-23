<?php

namespace Bsi\Queue\Tests;

use Mockery;
use Bitrix\Main\Data\Cache;
use PHPUnit\Framework\TestCase;
use Bitrix\Main\Data\CacheEngineNone;
use Bsi\Queue\Tests\Fixtures\DummyCache;

abstract class AbstractTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mockery::resetContainer();

        Mockery::getConfiguration()->setConstantsMap([
            'Bitrix\Main\EventResult' => [
                'SUCCESS' => 1,
            ],
        ]);

        $this->injectBitrixApplicationMock();
        $this->injectBitrixDateTimeMock();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    protected function injectBitrixApplicationMock(): void
    {

        $mock = Mockery::mock('overload:Bitrix\Main\Application');
        $mock->shouldReceive('getInstance')->andReturnSelf();

        $mock->shouldReceive('getConnection->startTransaction');
        $mock->shouldReceive('getConnection->commitTransaction');
        $mock->shouldReceive('getConnection->rollbackTransaction');
    }

    protected function injectBitrixDateTimeMock(): void
    {
        $mock = Mockery::mock('overload:Bitrix\Main\Type\DateTime')->makePartial();

        $mock->shouldReceive('__construct')
            ->once()
            ->andReturnUsing(function () {
                new \DateTime();
            });

        $mock->shouldReceive('add')->andReturnUsing(function ($modify) {
            return (new \DateTime())->modify($modify);
        });
    }

    protected function getBitrixConfigurationMock(array $options = []): Mockery\MockInterface
    {
        $mock = Mockery::mock('overload:Bitrix\Main\Config\Configuration');

        $mock->shouldReceive('getValue')->andReturn($options);

        return $mock;
    }

    protected function getBitrixEventMock(array $results = []): Mockery\MockInterface
    {
        $mock = Mockery::mock('overload:Bitrix\Main\Event');

        $mock->shouldReceive('send')->andReturnNull();
        $mock->shouldReceive('getResults')->andReturn($results);

        return $mock;
    }

    protected function getBitrixEventResultMock($type, $params): Mockery\MockInterface
    {
        $mock = Mockery::mock('overload:Bitrix\Main\EventResult');

        $mock->shouldReceive('getType')->andReturn($type);
        $mock->shouldReceive('getParameters')->andReturn($params);

        return $mock;
    }

    protected function getBitrixOrmResultMock(bool $isSuccess, array $errors = []): Mockery\MockInterface
    {
        $mock = Mockery::mock('BitrixOrmResult');

        $mock->shouldReceive('isSuccess')->andReturn($isSuccess);
        $mock->shouldReceive('getErrorMessages')->andReturn($errors);

        return $mock;
    }
}

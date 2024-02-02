<?php

namespace Bsi\Queue\Tests;

use Bsi\Queue\Queue;
use Bsi\Queue\Tests\Fixtures\DummyMessage;
use Bsi\Queue\Tests\Fixtures\DummyMessageHandler;
use RedisException;
use Symfony\Component\Messenger\Bridge\Redis\Transport\RedisTransportFactory;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class QueueTest extends AbstractTestCase
{
    /**
     * @dataProvider buildConfigurationProvider
     */
    public function testConfiguration(
        array $options,
        array $expectedBuses,
        ?string $expectedDefaultBus,
        array $expectedTransports,
        ?string $expectedFailureTransport,
        ?array $expectedRouting
    ): void {
        $this->getBitrixEventMock();
        $this->getBitrixConfigurationMock($options);

        $queue = Queue::getInstance();
        $config = $queue->getConfig();
        $this->assertEquals($expectedBuses, $config['buses']);
        $this->assertSame($expectedDefaultBus, $config['default_bus']);
        $this->assertEquals($expectedTransports, $config['transports']);
        $this->assertSame($expectedFailureTransport, $config['failure_transport']);
        $this->assertEquals($expectedRouting, $config['routing']);
    }

    public function testContainerBuild(): void
    {
        $this->getBitrixEventMock();
        $this->getBitrixConfigurationMock([
            'buses' => ['command_bus', 'query_bus'],
            'default_bus' => 'query_bus',
            'transports' => [
                'sync' => 'sync://',
                'failed' => 'bitrix://',
            ],
        ]);

        $queue = Queue::getInstance();
        $queue->boot();
        $container = $queue->getContainer();

        $buses = $container->findTaggedServiceIds('messenger.bus');
        $this->assertArrayHasKey('command_bus', $buses);
        $this->assertArrayHasKey('query_bus', $buses);

        $transports = $container->findTaggedServiceIds('messenger.receiver');
        $this->assertArrayHasKey('messenger.transport.sync', $transports);
        $this->assertArrayHasKey('messenger.transport.failed', $transports);
    }

    public function testTransportFactoryRegister(): void
    {
        $this->expectException(RedisException::class);
        $this->expectExceptionMessageMatches('/Temporary failure in name resolution/');

        $this->getBitrixEventMock();
        $this->getBitrixConfigurationMock([
            'buses' => ['command_bus', 'query_bus'],
            'default_bus' => 'query_bus',
            'transports' => [
                'sync' => 'redis://dummy',
                'failed' => 'bitrix://',
            ],
            'routing' => [
                DummyMessage::class => 'sync',
            ],
        ]);

        $queue = Queue::getInstance();
        $queue->addMessageHandler(DummyMessageHandler::class);
        $queue->registerTransportFactory('redis', RedisTransportFactory::class);
        $queue->boot();
        $container = $queue->getContainer();

        $container->get('messenger.default_bus')->dispatch(new DummyMessage('hello'));
    }

    public function buildConfigurationProvider(): iterable
    {
        yield 'no options' => [
            'options' => [],
            'expectedBuses' => [
                'default' => ['default_middleware' => true, 'middleware' => []],
            ],
            'expectedDefaultBus' => 'default',
            'expectedTransports' => [],
            'expectedFailureTransport' => null,
            'expectedRouting' => [],
        ];

        yield 'single bus with numeric key' => [
            'options' => [
                'buses' => ['command_bus'],
            ],
            'expectedBuses' => ['command_bus' => ['default_middleware' => true, 'middleware' => []]],
            'expectedDefaultBus' => 'command_bus',
            'expectedTransports' => [],
            'expectedFailureTransport' => null,
            'expectedRouting' => [],
        ];

        yield 'single bus with string key' => [
            'options' => [
                'buses' => ['command_bus' => []],
            ],
            'expectedBuses' => ['command_bus' => ['default_middleware' => true, 'middleware' => []]],
            'expectedDefaultBus' => 'command_bus',
            'expectedTransports' => [],
            'expectedFailureTransport' => null,
            'expectedRouting' => [],
        ];

        yield 'multiple buses with mixed styles' => [
            'options' => [
                'buses' => [
                    'command_bus' => null,
                    'query_bus' => ['middleware' => ['dummy_middleware']],
                ],
                'default_bus' => 'command_bus',
            ],
            'expectedBuses' => [
                'command_bus' => ['default_middleware' => true, 'middleware' => []],
                'query_bus' => ['default_middleware' => true, 'middleware' => ['dummy_middleware']],
            ],
            'expectedDefaultBus' => 'command_bus',
            'expectedTransports' => [],
            'expectedFailureTransport' => null,
            'expectedRouting' => [],
        ];

        yield 'all values' => [
            'options' => [
                'buses' => [
                    'command_bus' => null,
                    'query_bus' => null,
                    'event_bus' => null,
                ],
                'default_bus' => 'command_bus',
                'transports' => [
                    'async' => [
                        'dsn' => 'redis://localhost:6379/messages',
                        'retry_strategy' => [
                            'max_retries' => 5,
                            'multiplier' => 3,
                        ],
                    ],
                    'failed' => 'bitrix://',
                    'sync' => 'sync://',
                ],
                'failure_transport' => 'failed',
                'routing' => [
                    DummyMessage::class => 'async',
                ],
            ],
            'expectedBuses' => [
                'command_bus' => ['default_middleware' => true, 'middleware' => []],
                'query_bus' => ['default_middleware' => true, 'middleware' => []],
                'event_bus' => ['default_middleware' => true, 'middleware' => []],
            ],
            'expectedDefaultBus' => 'command_bus',
            'expectedTransports' => [
                'async' => [
                    'dsn' => 'redis://localhost:6379/messages',
                    'options' => [],
                    'failure_transport' => null,
                    'serializer' => null,
                    'retry_strategy' => [
                        'max_retries' => 5,
                        'multiplier' => 3,
                        'service' => null,
                        'delay' => 1000,
                        'max_delay' => 0,
                    ],
                ],
                'failed' => [
                    'dsn' => 'bitrix://',
                    'options' => [],
                    'failure_transport' => null,
                    'serializer' => null,
                    'retry_strategy' => [
                        'max_retries' => 3,
                        'multiplier' => 2,
                        'service' => null,
                        'delay' => 1000,
                        'max_delay' => 0,
                    ],
                ],
                'sync' => [
                    'dsn' => 'sync://',
                    'options' => [],
                    'failure_transport' => null,
                    'serializer' => null,
                    'retry_strategy' => [
                        'max_retries' => 3,
                        'multiplier' => 2,
                        'service' => null,
                        'delay' => 1000,
                        'max_delay' => 0,
                    ],
                ],
            ],
            'expectedFailureTransport' => 'failed',
            'expectedRouting' => [
                DummyMessage::class => ['senders' => ['async']],
            ],
        ];
    }
}

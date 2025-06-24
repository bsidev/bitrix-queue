<?php

namespace Bsi\Queue\Tests;

use Mockery;
use RedisException;
use Bsi\Queue\Queue;
use Bsi\Queue\Tests\Fixtures\DummyMessage;
use Bsi\Queue\Tests\Fixtures\DummyService;
use Bsi\Queue\Tests\Fixtures\DummyMessageHandler;
use Bsi\Queue\Monitoring\Adapter\AdapterInterface;
use Bsi\Queue\Tests\Fixtures\Monitoring\DummyAdapter;
use Bsi\Queue\Tests\Fixtures\Monitoring\DummyAdapterFactory;
use Bsi\Queue\Tests\Fixtures\Transport\DummyTransportFactory;
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
        array $expectedFactories,
        array $expectedMessageHandlers,
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
        $this->assertEquals($expectedFactories, $config['factories']);
        $this->assertEquals($expectedMessageHandlers, $config['message_handlers']);
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

    public function testMessageHandlerRegister()
    {
        $mockService = Mockery::mock(DummyService::class);
        $mockService->shouldReceive('handle')->once();

        $this->getBitrixEventMock();
        $this->getBitrixConfigurationMock([
            'buses' => ['command_bus', 'query_bus'],
            'default_bus' => 'query_bus',
            'transports' => [
                'sync' => 'sync://',
            ],
            'routing' => [
                DummyMessage::class => 'sync',
            ],
            'monitoring' => ['enabled' => false],
        ]);
        $queue = Queue::getInstance();
        $queue->registerMessageHandler(DummyMessageHandler::class, [$mockService]);
        $queue->boot();

        $container = $queue->getContainer();
        $container->get('messenger.default_bus')->dispatch(new DummyMessage('hello'));

        $this->assertTrue($container->has(DummyMessageHandler::class));
    }

    public function testMessageHandlerRegisterFromConfig()
    {
        $this->getBitrixEventMock();
        $this->getBitrixConfigurationMock([
            'buses' => ['command_bus', 'query_bus'],
            'default_bus' => 'query_bus',
            'transports' => [
                'sync' => 'sync://',
            ],
            'message_handlers' => [
                DummyMessageHandler::class
            ],
            'routing' => [
                DummyMessage::class => 'sync',
            ],
            'monitoring' => ['enabled' => false],
        ]);
        $queue = Queue::getInstance();
        $queue->boot();

        $container = $queue->getContainer();
        $container->get('messenger.default_bus')->dispatch(new DummyMessage('hello'));

        $this->assertTrue($container->has(DummyMessageHandler::class));
    }

    public function testMessageHandlerRegisterFromConfigWithArguments()
    {
        $mockService = Mockery::mock(DummyService::class);
        $mockService->shouldReceive('handle')->once();

        $this->getBitrixEventMock();
        $this->getBitrixConfigurationMock([
            'buses' => ['command_bus', 'query_bus'],
            'default_bus' => 'query_bus',
            'transports' => [
                'sync' => 'sync://',
            ],
            'message_handlers' => [
                [
                    'class' => DummyMessageHandler::class,
                    'arguments' => [$mockService]
                ],
            ],
            'routing' => [
                DummyMessage::class => 'sync',
            ],
            'monitoring' => ['enabled' => false],
        ]);
        $queue = Queue::getInstance();
        $queue->boot();

        $container = $queue->getContainer();
        $container->get('messenger.default_bus')->dispatch(new DummyMessage('hello'));

        $this->assertTrue($container->has(DummyMessageHandler::class));
    }

    public function testTransportFactoryRegister(): void
    {
        $this->expectException(RedisException::class);

        $mockService = Mockery::mock(DummyService::class);
        $mockService->shouldReceive('handle')->once();

        $this->getBitrixEventMock();
        $this->getBitrixConfigurationMock([
            'buses' => ['command_bus', 'query_bus'],
            'default_bus' => 'query_bus',
            'transports' => [
                'async' => 'redis://dummy',
                'sync' => 'dummy://',
                'failed' => 'bitrix://',
            ],
            'routing' => [
                DummyMessage::class => ['sync', 'async'],
            ],
        ]);

        $queue = Queue::getInstance();
        $queue->registerMessageHandler(DummyMessageHandler::class);
        $queue->registerTransportFactory('redis', RedisTransportFactory::class);
        $queue->registerTransportFactory('dummy', DummyTransportFactory::class, [$mockService]);
        $queue->boot();

        $container = $queue->getContainer();
        $container->get('messenger.default_bus')->dispatch(new DummyMessage('hello'));
    }

    public function testTransportFactoryRegisterFromConfig(): void
    {
        $this->expectException(RedisException::class);

        $mockService = Mockery::mock(DummyService::class);
        $mockService->shouldReceive('handle')->once();

        $this->getBitrixEventMock();
        $this->getBitrixConfigurationMock([
            'buses' => ['command_bus', 'query_bus'],
            'default_bus' => 'query_bus',
            'transports' => [
                'async' => 'redis://dummy',
                'sync' => 'dummy://',
                'failed' => 'bitrix://',
            ],
            'factories' => [
                'transport' => [
                    'redis' => RedisTransportFactory::class,
                    'dummy' => [
                        'class' => DummyTransportFactory::class,
                        'arguments' => [$mockService]
                    ]
                ]
            ],
            'routing' => [
                DummyMessage::class => ['sync', 'async'],
            ],
        ]);

        $queue = Queue::getInstance();
        $queue->boot();

        $container = $queue->getContainer();
        $container->get('messenger.default_bus')->dispatch(new DummyMessage('hello'));
    }

    public function testMonitoringAdapterFactoryRegister(): void
    {
        $mockService = Mockery::mock(DummyService::class);
        $mockService->shouldReceive('handle')->once();

        $this->getBitrixEventMock();
        $this->getBitrixConfigurationMock([
            'buses' => ['command_bus', 'query_bus'],
            'default_bus' => 'command_bus',
            'monitoring' => [
                'enabled' => true,
                'adapter' => 'dummy',
                'buses' => ['command_bus'],
            ],
        ]);

        $queue = Queue::getInstance();
        $queue->registerMonitoringAdapterFactory('dummy', DummyAdapterFactory::class, [$mockService]);
        $queue->boot();

        /** @var AdapterInterface $monitoringAdapter */
        $monitoringAdapter = $queue->getContainer()->get(AdapterInterface::class);
        $this->assertInstanceOf(DummyAdapter::class, $monitoringAdapter);
    }

    public function testMonitoringAdapterFactoryRegisterFromConfig(): void
    {
        $this->getBitrixEventMock();
        $this->getBitrixConfigurationMock([
            'buses' => ['command_bus', 'query_bus'],
            'default_bus' => 'command_bus',
            'monitoring' => [
                'enabled' => true,
                'adapter' => 'dummy',
                'buses' => ['command_bus'],
            ],
            'factories' => [
                'monitoring' => [
                    'dummy' => DummyAdapterFactory::class
                ]
            ],
        ]);

        $queue = Queue::getInstance();
        $queue->boot();

        /** @var AdapterInterface $monitoringAdapter */
        $monitoringAdapter = $queue->getContainer()->get(AdapterInterface::class);
        $this->assertInstanceOf(DummyAdapter::class, $monitoringAdapter);
    }

    public function testMonitoringAdapterFactoryRegisterFromConfigWithArguments(): void
    {
        $mockService = Mockery::mock(DummyService::class);
        $mockService->shouldReceive('handle')->once();

        $this->getBitrixEventMock();
        $this->getBitrixConfigurationMock([
            'buses' => ['command_bus', 'query_bus'],
            'default_bus' => 'command_bus',
            'monitoring' => [
                'enabled' => true,
                'adapter' => 'dummy',
                'buses' => ['command_bus'],
            ],
            'factories' => [
                'monitoring' => [
                    'dummy' => [
                        'class' => DummyAdapterFactory::class,
                        'arguments' => [$mockService]
                    ]
                ]
            ],
        ]);

        $queue = Queue::getInstance();
        $queue->boot();

        /** @var AdapterInterface $monitoringAdapter */
        $monitoringAdapter = $queue->getContainer()->get(AdapterInterface::class);
        $this->assertInstanceOf(DummyAdapter::class, $monitoringAdapter);
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
            'expectedFactories' => ['transport' => [], 'monitoring' => []],
            'expectedMessageHandlers' => [],
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
            'expectedFactories' => ['transport' => [], 'monitoring' => []],
            'expectedMessageHandlers' => [],
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
            'expectedFactories' => ['transport' => [], 'monitoring' => []],
            'expectedMessageHandlers' => [],
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
            'expectedFactories' => ['transport' => [], 'monitoring' => []],
            'expectedMessageHandlers' => [],
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
                'factories' => [
                    'transport' => [
                        'redis' => RedisTransportFactory::class,
                    ],
                    'monitoring' => [
                        'dummy' => DummyAdapterFactory::class
                    ],
                ],
                'message_handlers' => [
                    DummyMessageHandler::class,
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
            'expectedFactories' => [
                'transport' => [
                    'redis' => RedisTransportFactory::class,
                ],
                'monitoring' => [
                    'dummy' => DummyAdapterFactory::class
                ],
            ],
            'expectedMessageHandlers' => [
                DummyMessageHandler::class,
            ],
            'expectedFailureTransport' => 'failed',
            'expectedRouting' => [
                DummyMessage::class => ['senders' => ['async']],
            ],
        ];
    }
}

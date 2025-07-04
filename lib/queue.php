<?php

namespace Bsi\Queue;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use BsiQueueCachedContainer;
use Bitrix\Main\Config\Configuration;
use Bsi\Queue\Exception\LogicException;
use Bsi\Queue\Exception\RuntimeException;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Messenger\Envelope;
use Bsi\Queue\Event\SyncMessageFailedEvent;
use Symfony\Component\Messenger\MessageBus;
use Bsi\Queue\Event\SyncMessageHandledEvent;
use Bsi\Queue\Monitoring\Adapter\AdapterInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Bsi\Queue\Monitoring\Adapter\AdapterFactoryInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class Queue
{
    /** @var Container */
    protected Container $container;
    /** @var array */
    protected array $config;
    /** @var bool */
    protected bool $booted = false;
    /** @var bool */
    protected bool $useCache = false;

    /** @var Queue */
    private static $instance;

    protected const CONFIG_KEY = 'bsi.queue';
    protected const DEFAULT_CONFIG = [
        'buses' => [
            'default' => [
                'default_middleware' => true,
                'middleware' => [],
            ],
        ],
        'default_bus' => null,
        'transports' => [],
        'failure_transport' => null,
        'routing' => [],
        'monitoring' => [],
        'message_handlers' => [],
        'factories' => [
            'transport' => [],
            'monitoring' => [],
        ],
    ];
    protected const DEFAULT_BUS_CONFIG = [
        'default_middleware' => true,
        'middleware' => [],
    ];
    protected const DEFAULT_TRANSPORT_CONFIG = [
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
    ];
    protected const DEFAULT_MONITORING_CONFIG = [
        'enabled' => true,
        'adapter' => 'bitrix',
        'buses' => [],
    ];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $config = (array) Configuration::getValue(static::CONFIG_KEY);

        $event = new Event('bsi.queue', QueueEvents::LOAD_CONFIGURATION, $config);
        $event->send();
        $resultList = $event->getResults();
        foreach ($resultList as $eventResult) {
            if ($eventResult->getType() !== EventResult::SUCCESS) {
                continue;
            }

            $params = $eventResult->getParameters();
            if (!empty($params) && is_array($params)) {
                foreach ($params as $key => $value) {
                    $config[$key] = $value;
                }
            }
        }

        $config = $this->normalizeConfiguration($config);

        if ($config['default_bus'] === null && count($config['buses']) === 1) {
            $config['default_bus'] = key($config['buses']);
        }

        $this->config = $config;
        $this->container = new ContainerBuilder();
    }

    public function useCache(bool $useCache): void
    {
        $this->useCache = $useCache;
    }

    public function boot(string $cacheFile = '/bitrix/cache/bsi_queue_container.php'): void
    {
        if ($this->booted === true) {
            return;
        }

        if ($this->useCache) {
            $cacheFileFull = $_SERVER['DOCUMENT_ROOT'] . $cacheFile;
            $containerConfigCache = new ConfigCache($cacheFileFull, false);

            if (!$containerConfigCache->isFresh()) {
                $this->initializeContainer();

                $dumper = new PhpDumper($this->container);
                $containerConfigCache->write(
                    $dumper->dump([
                        'class' => 'BsiQueueCachedContainer',
                        'base_class' => Container::class
                    ]),
                    $this->container->getResources()
                );
            }

            require_once $cacheFileFull;
            /** @noinspection PhpUndefinedClassInspection */
            $this->container = new BsiQueueCachedContainer();
        } else {
            $this->initializeContainer();
        }

        $this->booted = true;
    }

    /**
     * @deprecated Use registerMessageHandler() instead
     * @see registerMessageHandler()
     */
    public function addMessageHandler(string $class, array $arguments = [], array $options = []): void
    {
        $this->registerMessageHandler($class, $arguments, $options);
    }

    public function registerMessageHandler(string $class, array $arguments = [], array $options = []): void
    {
        $service = $this->container->register($class, $class);
        foreach ($arguments as $argument) {
            $service->addArgument($argument);
        }
        $service->addTag('messenger.message_handler', $options);
    }

    public function registerTransportFactory(string $code, string $class, array $arguments = []): void
    {
        if (!is_subclass_of($class, TransportFactoryInterface::class)) {
            throw new RuntimeException(sprintf('Class "%s" must implement interface "%s".', $class, TransportFactoryInterface::class));
        }

        $service = $this->container->register('messenger.transport.' . $code . '.factory', $class);
        foreach ($arguments as $argument) {
            $service->addArgument($argument);
        }
        $service->addTag('messenger.transport_factory');
    }

    public function registerMonitoringAdapterFactory(string $code, string $class, array $arguments = []): void
    {
        if (!is_subclass_of($class, AdapterFactoryInterface::class)) {
            throw new RuntimeException(sprintf('Class "%s" must implement interface "%s".', $class, AdapterFactoryInterface::class));
        }

        $service = $this->container->register('monitoring.adapter.' . $code . '.factory', $class);
        foreach ($arguments as $argument) {
            $service->addArgument($argument);
        }
        $service->addTag('monitoring.adapter_factory');
    }

    public function dispatchMessage(object $message, ?string $busName = null, array $stamps = []): Envelope
    {
        if ($this->booted === false) {
            throw new RuntimeException('Dispatching the message from a non-booted Queue is forbidden.');
        }

        $busName = $busName ?? $this->config['default_bus'];
        /** @var MessageBusInterface|null $bus */
        $bus = $this->container->get($busName);
        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->container->get('event_dispatcher');

        if ($bus === null) {
            throw new RuntimeException(sprintf('Bus "%s" does not exist.', $busName));
        }

        try {
            $envelope = $bus->dispatch(Envelope::wrap($message, $stamps), $stamps);

            /** @var HandledStamp|null $handledStamp */
            $handledStamp = $envelope->last(HandledStamp::class);
            if ($handledStamp) {
                $eventDispatcher->dispatch(new SyncMessageHandledEvent($envelope));
            }
        } catch (HandlerFailedException $e) {
            $eventDispatcher->dispatch(new SyncMessageFailedEvent($e->getEnvelope(), $e));
            throw $e;
        }

        return $envelope;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    protected function normalizeConfiguration(array $config): array
    {
        $config = array_merge(static::DEFAULT_CONFIG, $config);

        $newBusesConfig = [];
        foreach ($config['buses'] as $id => $bus) {
            if (is_int($id)) {
                $id = $bus;
                $bus = [];
            }
            $newBusesConfig[$id] = array_replace_recursive(static::DEFAULT_BUS_CONFIG, (array) $bus);
        }
        $config['buses'] = $newBusesConfig;

        if (!empty($config['factories'])) {
            $config['factories'] = array_merge_recursive(static::DEFAULT_CONFIG['factories'], $config['factories']);
        }

        $newTransportsConfig = [];
        foreach ($config['transports'] as $id => $transport) {
            if (is_string($transport)) {
                $transport = [
                    'dsn' => $transport,
                ];
            }
            $newTransportsConfig[$id] = array_replace_recursive(static::DEFAULT_TRANSPORT_CONFIG, (array) $transport);
        }
        $config['transports'] = $newTransportsConfig;

        $newRoutingConfig = [];
        foreach ($config['routing'] as $message => $transports) {
            $newRoutingConfig[$message] = ['senders' => (array) $transports];
        }
        $config['routing'] = $newRoutingConfig;

        return $config;
    }

    protected function initializeContainer(): void
    {
        $this->container->addObjectResource($this);
        $this->container->addCompilerPass(new RegisterListenersPass());
        $this->container->addCompilerPass(new MessengerPass());
        $this->container->addCompilerPass(new AddConsoleCommandPass());

        $this->container->register('event_dispatcher', EventDispatcher::class)->setPublic(true);

        $loader = new XmlFileLoader($this->container, new FileLocator(dirname(__DIR__) . '/config'));
        $loader->load('messenger.xml');
        $loader->load('monitoring.xml');

        $this->registerMessengerConfiguration($this->config, $this->container);
        $this->registerMonitoringConfiguration($this->config, $this->container);

        $this->container->compile();
    }

    private function registerMessengerConfiguration(array $config, ContainerBuilder $container): void
    {
        if ($config['default_bus'] === null && count($config['buses']) === 1) {
            $config['default_bus'] = key($config['buses']);
        }

        $defaultMiddleware = [
            'before' => [
                ['id' => 'add_bus_name_stamp_middleware'],
                ['id' => 'add_uuid_stamp_middleware'],
                ['id' => 'reject_redelivered_message_middleware'],
                ['id' => 'dispatch_after_current_bus'],
                ['id' => 'failed_message_processing_middleware'],
            ],
            'after' => [
                ['id' => 'send_message'],
                ['id' => 'handle_message'],
            ],
        ];
        foreach ($config['buses'] as $busId => $bus) {
            $middleware = $bus['middleware'];

            if ($bus['default_middleware']) {
                if ($bus['default_middleware'] === 'allow_no_handlers') {
                    $defaultMiddleware['after'][1]['arguments'] = [true];
                } else {
                    unset($defaultMiddleware['after'][1]['arguments']);
                }

                // argument to add_bus_name_stamp_middleware
                $defaultMiddleware['before'][0]['arguments'] = [$busId];

                $middleware = array_merge($defaultMiddleware['before'], $middleware, $defaultMiddleware['after']);
            }

            $container->setParameter($busId . '.middleware', $middleware);
            $container->register($busId, MessageBus::class)
                ->addArgument([])
                ->setPublic(true)
                ->addTag('messenger.bus');

            if ($config['default_bus'] === $busId) {
                $container->setAlias('messenger.default_bus', $busId)->setPublic(true);
                $container->setAlias(MessageBusInterface::class, $busId);
            } else {
                $container->registerAliasForArgument($busId, MessageBusInterface::class);
            }
        }

        if (!empty($config['message_handlers'])) {
            foreach ($config['message_handlers'] as $messageHandlerIndex => $messageHandler) {
                if (is_array($messageHandler)) {
                    if (!isset($messageHandler['class'])) {
                        throw new LogicException(sprintf('Invalid message handler configuration: missing required "class" key for message handler with index %s.', $messageHandlerIndex));
                    }
                    $messageHandlerArguments = isset($messageHandler['arguments']) && is_array($messageHandler['arguments']) ? $messageHandler['arguments'] : [];
                    $messageHandlerOptions = isset($messageHandler['options']) && is_array($messageHandler['options']) ? $messageHandler['options'] : [];
                    $this->registerMessageHandler($messageHandler['class'], $messageHandlerArguments, $messageHandlerOptions);
                } else {
                    $this->registerMessageHandler($messageHandler);
                }
            }
        }

        if (!empty($config['factories']['transport'])) {
            foreach ($config['factories']['transport'] as $transportCode => $transportFactory) {
                if (is_array($transportFactory)) {
                    if (!isset($transportFactory['class'])) {
                        throw new LogicException(sprintf('Invalid transport factory configuration: missing required "class" key for transport factory with code %s.', $transportCode));
                    }
                    $transportFactoryArguments = isset($transportFactory['arguments']) && is_array($transportFactory['arguments']) ? $transportFactory['arguments'] : [];
                    $this->registerTransportFactory($transportCode, $transportFactory['class'], $transportFactoryArguments);
                } else {
                    $this->registerTransportFactory($transportCode, $transportFactory);
                }
            }
        }

        if (empty($config['transports'])) {
            $container->removeDefinition('messenger.transport.symfony_serializer');
            $container->removeDefinition('messenger.transport.amqp.factory');
            $container->removeDefinition('messenger.transport.redis.factory');
            $container->removeDefinition('messenger.transport.sqs.factory');
            $container->removeDefinition('messenger.transport.beanstalkd.factory');
        }

        $failureTransports = [];
        if ($config['failure_transport']) {
            if (!isset($config['transports'][$config['failure_transport']])) {
                throw new LogicException(sprintf('Invalid Messenger configuration: the failure transport "%s" is not a valid transport or service id.', $config['failure_transport']));
            }

            $container->setAlias('messenger.failure_transports.default', 'messenger.transport.' . $config['failure_transport']);
            $failureTransports[] = $config['failure_transport'];
        }

        $failureTransportsByName = [];
        foreach ($config['transports'] as $name => $transport) {
            if ($transport['failure_transport']) {
                $failureTransports[] = $transport['failure_transport'];
                $failureTransportsByName[$name] = $transport['failure_transport'];
            } elseif ($config['failure_transport']) {
                $failureTransportsByName[$name] = $config['failure_transport'];
            }
        }

        $senderAliases = [];
        $transportRetryReferences = [];
        foreach ($config['transports'] as $name => $transport) {
            $serializerId = $transport['serializer'] ?? 'messenger.default_serializer';
            $transportDefinition = (new Definition(TransportInterface::class))
                ->setFactory([new Reference('messenger.transport_factory'), 'createTransport'])
                ->setArguments([$transport['dsn'], $transport['options'] + ['transport_name' => $name], new Reference($serializerId)])
                ->addTag('messenger.receiver', [
                        'alias' => $name,
                        'is_failure_transport' => in_array($name, $failureTransports, true),
                    ]);
            $container->setDefinition($transportId = 'messenger.transport.' . $name, $transportDefinition);
            $senderAliases[$name] = $transportId;

            if ($transport['retry_strategy']['service'] !== null) {
                $transportRetryReferences[$name] = new Reference($transport['retry_strategy']['service']);
            } else {
                $retryServiceId = sprintf('messenger.retry.multiplier_retry_strategy.%s', $name);
                $retryDefinition = new ChildDefinition('messenger.retry.abstract_multiplier_retry_strategy');
                $retryDefinition
                    ->replaceArgument(0, $transport['retry_strategy']['max_retries'])
                    ->replaceArgument(1, $transport['retry_strategy']['delay'])
                    ->replaceArgument(2, $transport['retry_strategy']['multiplier'])
                    ->replaceArgument(3, $transport['retry_strategy']['max_delay']);
                $container->setDefinition($retryServiceId, $retryDefinition);

                $transportRetryReferences[$name] = new Reference($retryServiceId);
            }
        }

        $senderReferences = [];
        // alias => service_id
        foreach ($senderAliases as $alias => $serviceId) {
            $senderReferences[$alias] = new Reference($serviceId);
        }
        // service_id => service_id
        foreach ($senderAliases as $serviceId) {
            $senderReferences[$serviceId] = new Reference($serviceId);
        }

        foreach ($config['transports'] as $transport) {
            if ($transport['failure_transport'] && !isset($senderReferences[$transport['failure_transport']])) {
                throw new LogicException(sprintf('Invalid Messenger configuration: the failure transport "%s" is not a valid transport or service id.', $transport['failure_transport']));
            }
        }

        $failureTransportReferencesByTransportName = array_map(static function ($failureTransportName) use ($senderReferences) {
            return $senderReferences[$failureTransportName];
        }, $failureTransportsByName);

        $messageToSendersMapping = [];
        foreach ($config['routing'] as $message => $messageConfiguration) {
            if ($message !== '*' && !class_exists($message) && !interface_exists($message, false)) {
                throw new LogicException(sprintf('Invalid Messenger routing configuration: class or interface "%s" not found.', $message));
            }

            // make sure senderAliases contains all senders
            foreach ($messageConfiguration['senders'] as $sender) {
                if (!isset($senderReferences[$sender])) {
                    throw new LogicException(sprintf('Invalid Messenger routing configuration: the "%s" class is being routed to a sender called "%s". This is not a valid transport or service id.', $message, $sender));
                }
            }

            $messageToSendersMapping[$message] = $messageConfiguration['senders'];
        }

        $sendersServiceLocator = ServiceLocatorTagPass::register($container, $senderReferences);

        $container->getDefinition('messenger.senders_locator')
            ->replaceArgument(0, $messageToSendersMapping)
            ->replaceArgument(1, $sendersServiceLocator)
        ;

        $container->getDefinition('messenger.retry.send_failed_message_for_retry_listener')
            ->replaceArgument(0, $sendersServiceLocator)
        ;

        $container->getDefinition('messenger.retry_strategy_locator')
            ->replaceArgument(0, $transportRetryReferences);

        if ($failureTransports) {
            $container->getDefinition('console.command.messenger_failed_messages_retry')
                ->replaceArgument(0, $config['failure_transport']);
            $container->getDefinition('console.command.messenger_failed_messages_show')
                ->replaceArgument(0, $config['failure_transport']);
            $container->getDefinition('console.command.messenger_failed_messages_remove')
                ->replaceArgument(0, $config['failure_transport']);

            $failureTransportsByTransportNameServiceLocator = ServiceLocatorTagPass::register($container, $failureTransportReferencesByTransportName);
            $container->getDefinition('messenger.failure.send_failed_message_to_failure_transport_listener')
                ->replaceArgument(0, $failureTransportsByTransportNameServiceLocator);
        } else {
            $container->removeDefinition('messenger.failure.send_failed_message_to_failure_transport_listener');
            $container->removeDefinition('console.command.messenger_failed_messages_retry');
            $container->removeDefinition('console.command.messenger_failed_messages_show');
            $container->removeDefinition('console.command.messenger_failed_messages_remove');
        }
    }

    private function registerMonitoringConfiguration(array $config, Container $container): void
    {
        $monitoringConfig = array_replace_recursive(static::DEFAULT_MONITORING_CONFIG, $config['monitoring']);

        $busNames = $monitoringConfig['buses'] ?? [];
        $allowedBuses = array_keys($config['buses']);

        if (count($busNames) !== count(array_intersect($busNames, $allowedBuses))) {
            throw new RuntimeException(sprintf('Unknown bus found: [%s]. Allowed buses are [%s].', implode(', ', $busNames), implode(', ', $allowedBuses)));
        }

        if (!empty($config['factories']['monitoring'])) {
            foreach ($config['factories']['monitoring'] as $monitoringFactoryCode => $monitoringFactory) {
                if (is_array($monitoringFactory)) {
                    if (!isset($monitoringFactory['class'])) {
                        throw new LogicException(sprintf('Invalid monitoring factory configuration: missing required "class" key for monitoring factory with code %s.', $monitoringFactoryCode));
                    }
                    $monitoringFactoryArguments = isset($monitoringFactory['arguments']) && is_array($monitoringFactory['arguments']) ? $monitoringFactory['arguments'] : [];
                    $this->registerMonitoringAdapterFactory($monitoringFactoryCode, $monitoringFactory['class'], $monitoringFactoryArguments);
                } else {
                    $this->registerMonitoringAdapterFactory($monitoringFactoryCode, $monitoringFactory);
                }
            }
        }

        $adapterDefinition = (new Definition(AdapterInterface::class))
            ->setFactory([new Reference('monitoring.adapter_factory'), 'createAdapter'])
            ->setArguments([
                $monitoringConfig['adapter'],
                $monitoringConfig['options'] ?? [],
            ]);

        $container->setDefinition('monitoring.adapter', $adapterDefinition);
        $container->setAlias(AdapterInterface::class, 'monitoring.adapter')->setPublic(true);

        $container->getDefinition('monitoring.push_stats_listener')
            ->replaceArgument(0, new Reference('monitoring.adapter'))
            ->replaceArgument(1, array_values($busNames));

        $isEnabled = $monitoringConfig['enabled'] ?? false;
        if (!$isEnabled) {
            $container->removeDefinition('monitoring.push_stats_listener');
        }
    }
}

<?php

namespace Bsi\Queue;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bsi\Queue\Exception\LogicException;
use Bsi\Queue\Exception\RuntimeException;
use Bsi\Queue\Monitoring\Adapter\AdapterFactoryInterface;
use Bsi\Queue\Monitoring\Adapter\AdapterInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @author Sergey Balasov <sbalasov@gmail.com>
 */
class Queue
{
    /** @var ContainerBuilder */
    protected $container;
    /** @var array */
    protected $config;
    /** @var bool */
    protected $booted = false;

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
    ];
    protected const DEFAULT_BUS_CONFIG = [
        'default_middleware' => true,
        'middleware' => [],
    ];
    protected const DEFAULT_TRANSPORT_CONFIG = [
        'options' => [],
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

    public function boot(): void
    {
        if ($this->booted === true) {
            return;
        }

        $this->initializeContainer();

        $this->booted = true;
    }

    public function addMessageHandler(string $class, array $options = []): void
    {
        if (!is_subclass_of($class, MessageHandlerInterface::class)) {
            throw new RuntimeException(sprintf('Class "%s" must implement interface "%s".', $class, MessageHandlerInterface::class));
        }

        $this->container->register($class)
            ->addTag('messenger.message_handler', $options);
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

        if ($bus === null) {
            throw new RuntimeException(sprintf('Bus "%s" does not exist.', $busName));
        }

        return $bus->dispatch(Envelope::wrap($message, $stamps), $stamps);
    }

    public function getContainer(): ContainerBuilder
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
        $defaultMiddleware = [
            'before' => [
                ['id' => 'add_bus_name_stamp'],
                ['id' => 'add_uuid_stamp'],
                ['id' => 'reject_redelivered_message'],
                ['id' => 'dispatch_after_current_bus'],
                ['id' => 'failed_message_processing'],
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

                // argument to add_bus_name_stamp
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

        $senderAliases = [];
        $transportRetryReferences = [];
        foreach ($config['transports'] as $name => $transport) {
            $serializerId = $transport['serializer'] ?? 'messenger.default_serializer';

            $transportDefinition = (new Definition(TransportInterface::class))
                ->setFactory([new Reference('messenger.transport_factory'), 'createTransport'])
                ->setArguments(
                    [
                        $transport['dsn'],
                        $transport['options'] + ['transport_name' => $name],
                        new Reference($serializerId),
                    ]
                )
                ->addTag('messenger.receiver', ['alias' => $name]);
            $container->setDefinition($transportId = 'transport.' . $name, $transportDefinition);
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
            ->replaceArgument(1, $sendersServiceLocator);

        $container->getDefinition('messenger.retry.send_failed_message_for_retry_listener')
            ->replaceArgument(0, $sendersServiceLocator);

        $container->getDefinition('messenger.retry_strategy_locator')
            ->replaceArgument(0, $transportRetryReferences);

        if ($config['failure_transport']) {
            if (!isset($senderReferences[$config['failure_transport']])) {
                throw new LogicException(sprintf('Invalid Messenger configuration: the failure transport "%s" is not a valid transport or service id.', $config['failure_transport']));
            }

            $container->getDefinition('messenger.failure.send_failed_message_to_failure_transport_listener')
                ->replaceArgument(0, $senderReferences[$config['failure_transport']]);
            $container->getDefinition('console.command.messenger_failed_messages_retry')
                ->replaceArgument(0, $config['failure_transport']);
            $container->getDefinition('console.command.messenger_failed_messages_show')
                ->replaceArgument(0, $config['failure_transport']);
            $container->getDefinition('console.command.messenger_failed_messages_remove')
                ->replaceArgument(0, $config['failure_transport']);
        } else {
            $container->removeDefinition('messenger.failure.send_failed_message_to_failure_transport_listener');
            $container->removeDefinition('console.command.messenger_failed_messages_retry');
            $container->removeDefinition('console.command.messenger_failed_messages_show');
            $container->removeDefinition('console.command.messenger_failed_messages_remove');
        }
    }

    private function registerMonitoringConfiguration(array $config, ContainerBuilder $container): void
    {
        $monitoringConfig = array_replace_recursive(static::DEFAULT_MONITORING_CONFIG, $config['monitoring']);

        $isEnabled = $monitoringConfig['enabled'] ?? false;
        if ($isEnabled) {
            $busNames = $monitoringConfig['buses'] ?? [];

            $allowedBuses = array_keys($config['buses']);
            if (count($busNames) !== count(array_intersect($busNames, $allowedBuses))) {
                throw new RuntimeException(sprintf('Unknown bus found: [%s]. Allowed buses are [%s].', implode(', ', $busNames), implode(', ', $allowedBuses)));
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
        } else {
            $container->removeDefinition('monitoring.push_stats_listener');
        }
    }
}

# Адаптер мониторинга

Для добавления собственного адаптера мониторинга необходимо реализовать два компонента:

- **Адаптер**, реализующий интерфейс **Bsi\Queue\Monitoring\Adapter\AdapterInterface**, содержащий логику работы с хранилищем статистики и репозиторием сообщений.
- **Фабрику адаптера**, реализующую интерфейс **Bsi\Queue\Monitoring\Adapter\AdapterFactoryInterface**, которая отвечает за создание экземпляра адаптера и проверку поддержки конфигурации.

Пример:

```php
<?php

namespace App\Monitoring;

use Bsi\Queue\Monitoring\Adapter\AdapterFactoryInterface;
use Bsi\Queue\Monitoring\Adapter\AdapterInterface;

class ElasticAdapter implements AdapterInterface 
{
    public function getStorage(): StorageInterface
    {
        //...
    }

    public function getMessageStatsRepository(): MessageStatsRepositoryInterface 
    {
        //...
    }
}

class ElasticAdapterFactory implements AdapterFactoryInterface
{
    public function createAdapter(string $name, array $options): AdapterInterface
    {
        return new ElasticAdapter();
    }

    public function supports(string $name, array $options): bool
    {
        return $name === 'elastic';
    }
}
```

# Регистрация адаптера мониторинга

Адаптеры мониторинга регистрируются в [конфигурации](configuration.md#factoriesmonitoring) модуля или с помощью метода `registerMonitoringAdapterFactory`

Пример:

```php
<?php

// local/php_interface/init.php

use App\Monitoring\ElasticAdapterFactory;
use Bitrix\Main\Loader;
use Bsi\Queue\Queue;

if (Loader::includeModule('bsi.queue')) {
    $queue = Queue::getInstance();
    $queue->registerMonitoringAdapterFactory('elastic', ElasticAdapterFactory::class);
    $queue->boot();
}
```

::: warning ВАЖНО
Метод `registerMonitoringAdapterFactory()` должен быть вызван **до** инициализации очередей, то есть **до** вызова метода `boot()`.
:::
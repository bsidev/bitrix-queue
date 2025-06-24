# Регистрация транспортов

::: warning ВАЖНО
Начиная с версии `5.1` пакета `symfony/messenger` транспорты AMQP, Redis и Doctrine вынесены в отдельные пакеты и в будущем будут удалены из основного пакета.
:::

## Регистрация транспорта на примере Redis

### Composer

```sh
composer require symfony/redis-messenger
```

### Регистрация фабрики

Фабрики регистрируются в [конфигурации](configuration.md#factoriestransport) модуля или с помощью метода `registerTransportFactory`

```php
<?php

// local/php_interface/init.php

use Bitrix\Main\Loader;
use Bsi\Queue\Queue;
use Symfony\Component\Messenger\Bridge\Redis\Transport\RedisTransportFactory;

if (Loader::includeModule('bsi.queue')) {
    $queue = Queue::getInstance();
    $queue->registerTransportFactory('redis', RedisTransportFactory::class);
    $queue->boot();
}
```

### Пример конфигурации транспортов

```php
[
    // ...
    'transports' => [
       'async' => [
           'dsn' => 'redis://redis:6379/messages',
       ],
    ],
    // ...
];
```

::: warning ВАЖНО
Метод `registerTransportFactory()` должен быть вызван **до** инициализации очередей, то есть **до** вызова метода `boot()`.
:::

**Ссылки по теме:**

- [Messenger: Sync & Queued Message Handling](https://symfony.com/doc/current/messenger.html)
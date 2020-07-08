# Модуль очередей

Модуль очередей для 1С-Битрикс. Позволяет отложенно обрабатывать команды из приложения.

Модуль является "мостом" для компонента [symfony/messenger](https://symfony.com/doc/current/messenger.html).

Более детально про принцип его работы можно почитать в [официальной документации](https://symfony.com/doc/current/components/messenger.html).

## Возможности

- Поддержка почти всех возможностей оригинального компонента.
- Дополнительный "транспорт" `bitrix://` для передачи сообщений через Bitrix ORM.
- Возможность вносить правки в конфигурацию модуля извне посредством обработчиков событий.

## Требования

- PHP `>=7.2.5`
- 1С-Битрикс `>=17.5.10`

## Установка и настройка

### Composer

```sh
composer require bsidev/bitrix-queue
```

Composer установит модуль в папку `/bitrix/modules/bsi.queue`

### Установите модуль через Marketplace

Перейдите по ссылке ниже и следуйте инструкциям:

```
http://домен/bitrix/admin/partner_modules.php?id=bsi.queue&lang=ru&install=Y
```

### Проинициализуйте ядро модуля

```php
// local/php_interface/init.php
use Bitrix\Main\Loader;
use Bsi\Queue\Queue;

Loader::includeModule('bsi.queue');

$queue = Queue::getInstance();
$queue->boot();
```

### Пример конфигурации

```php
// bitrix/.settings_extra.php
return [
    // ...
    'bsi.queue' => [
        'value' => [
            'buses' => [
                'command_bus',
                'query_bus',
            ],
            'default_bus' => 'command_bus',
            'transports' => [
                'sync' => 'sync://',
                'async' => [
                    'dsn' => 'redis://localhost:6379/messages',
                    'retry_strategy' => [
                        'max_retries' => 3,
                        'multiplier' => 2,
                    ],
                ],
                'failed' => 'bitrix://default?queue_name=failed',
            ],
            'failure_transport' => 'failed',
            'routing' => [
                'App\Message\TestMessage' => 'async',
            ],
        ],
        'readonly' => true,
    ],
    // ...
];
```

## Примеры использования

TODO

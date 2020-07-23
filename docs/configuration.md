# Конфигурация

Настройки выполняются в файле `/bitrix/.settings.php` или `/bitrix/.settings_extra.php`.

## Пример конфигурации

```php
// bitrix/.settings_extra.php
return [
    // ...
   'bsi.queue' => [
       'value' => [
           'buses' => ['command_bus', 'query_bus'],
           'default_bus' => 'command_bus',
           'transports' => [
               'sync' => 'sync://',
               'async' => [
                   'dsn' => 'redis://redis:6379/messages',
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
           'monitoring' => [
               'enabled' => true,
               'adapter' => 'bitrix',
               'buses' => ['command_bus'],
           ],
       ],
       'readonly' => true,
   ],
    // ...
];
```

## Параметры

- [buses](#buses)
- [default_bus](#default_bus)
- [transports](#transports)
- [failure_transport](#failure_transport)
- [routing](#routing)
- [monitoring](#monitoring-enabled)

### buses

- Тип: `array`
- По умолчанию:
```php
[
    'buses' => [
        'default' => [
            'default_middleware' => true,
            'middleware' => [],
        ],
    ],
];
```

Шины для передачи сообщений.

### default_bus

- Тип: `string`
- По умолчанию: `null`

Имя шина по умолчанию. При наличии более одной шины - **обязательно** (иначе, автоматически выбирается первая).

### transports

- Тип: `array`
- По умолчанию: `[]`

Транспорты для отправки и получения сообщений.

### failure_transport

- Тип: `string`
- По умолчанию: `null`

Имя транспорта для отправки и получения неудачных сообщений.  
По умолчанию, ошибочные сообщения повторно обрабатываются несколько раз (`max_retries`) и после этого "отбрасываются". С помощью `failure_transport` можно перенаправить такие сообщения в отдельный транспорт для повторной обработки.

Пример:

```php
[
    'failure_transport' => 'failed',
    'transports' => [
       'async' => [
           'dsn' => 'redis://redis:6379/messages',
           'retry_strategy' => [
               'max_retries' => 3,
               'multiplier' => 2,
           ],
       ],
       'failed' => 'bitrix://default?queue_name=failed',
    ],
];
```

### routing

- Тип: `array`
- По умолчанию: `[]`

Маршрутизация сообщений в нужный транспорт. Можно указать несколько транспортов для одного сообщения.

Пример:

```php
[
    'routing' => [
        'App\Message\AbstractAsyncMessage' => 'async',
        'App\Message\AsyncMessageInterface' => 'async',
        'My\Message\ToBeSentToTwoSenders' => ['async', 'audit'],
    ],
];
```

### monitoring.enabled

- Тип: `bool`
- По умолчанию: `true`

Активность мониторинга.

### monitoring.adapter

- Тип: `string`
- По умолчанию: `'bitrix'`

Адаптер для хранения и вывода статистики.

### monitoring.buses

- Тип: `array`
- По умолчанию: `[]`

Имена шин, которые отслеживает мониторинг. Если передан пустой массив, то отслеживаются все шины.

**Ссылки по теме:**

- [Настройка параметров ядра](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=2795)
- [The Messenger Component](https://symfony.com/doc/current/components/messenger.html)
- [Messenger: Sync & Queued Message Handling](https://symfony.com/doc/current/messenger.html)
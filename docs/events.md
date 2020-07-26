# События

### QueueEvents::LOAD_CONFIGURATION

Позволяет вносить изменения в конфигурацию динамически, без прямой правки файла конфигурации.

Пример использования:

```php
// local/php_interface/init.php

use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;
use Bsi\Queue\Queue;
use Bsi\Queue\QueueEvents;

EventManager::getInstance()->addEventHandler('', QueueEvents::LOAD_CONFIGURATION, function(Event $event) {
    return new EventResult(EventResult::SUCCESS, [
        'routing' => [
            'App\Message\TestMessage' => 'async',
        ],
    ]);
});

if (Loader::includeModule('bsi.queue')) {
    $queue = Queue::getInstance();
    $queue->boot();
}
```

**Ссылки по теме:**

- [EventManager](https://dev.1c-bitrix.ru/api_d7/bitrix/main/EventManager/index.php)

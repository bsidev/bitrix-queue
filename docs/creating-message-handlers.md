# Создание обработчиков

Для обработки сообщения необходимы два класса:
- **Класс-сообщение**, который содержит необходимые данные.
- **Класс-обработчик**, который занимается обработкой сообщения.

Пример **класс-сообщение**:

```php
<?php

namespace App\Message;

class MyMessage 
{
    private string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
```

Пример **класс-обработчик**:

```php
<?php

namespace App\MessageHandler;

use App\Message\MyMessage;

class MyMessageHandler
{
    public function __invoke(MyMessage $message)
    {
        // do something
    }
}
```

::: warning ВАЖНО
Класс-обработчик должен иметь метод `__invoke()`, который принимает в качестве **входного** параметра объект **класса-сообщения**.
:::

## Регистрация обработчика

Обработчики регистрируются в [конфигурации](configuration.md#message_handlers) модуля или с помощью метода `registerMessageHandler`

Пример:

```php
<?php

// local/php_interface/init.php

use App\MessageHandler\MyMessageHandler;
use Bitrix\Main\Loader;
use Bsi\Queue\Queue;

if (Loader::includeModule('bsi.queue')) {
    $queue = Queue::getInstance();
    $queue->registerMessageHandler(MyMessageHandler::class);
    $queue->boot();
}
```

::: warning ВАЖНО
Метод `registerMessageHandler()` должен быть вызван **до** инициализации очередей, то есть **до** вызова метода `boot()`.
:::

Пример отправки сообщения:

```php
<?php

use Bsi\Queue\Queue;
use App\Message\MyMessage;

Queue::getInstance()->dispatchMessage(new MyMessage('Hello, world'));
```

**Ссылки по теме:**

- [Messenger: Sync & Queued Message Handling](https://symfony.com/doc/current/messenger.html)

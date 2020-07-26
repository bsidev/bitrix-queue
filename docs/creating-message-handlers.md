# Создание обработчиков

Для обработки сообщения необходимы два класса:
- Класс-сообщение, который содержит необходимые данные.
- Класс-обработчик, который занимается обработкой сообщения.

Класс-обработчик должен реализовывать интерфейс `Symfony\Component\Messenger\Handler\MessageHandlerInterface` и иметь метод `__invoke()`, который принимает в качестве входного параметра объект класса-сообщения.

## Регистрация обработчика

Обработчики регистрируются с помощью метода `addMessageHandler`

Пример:

```php
<?php
// local/php_interface/init.php
use App\MessageHandler\MyMessageHandler;
use Bitrix\Main\Loader;
use Bsi\Queue\Queue;

if (Loader::includeModule('bsi.queue')) {
    $queue = Queue::getInstance();
    $queue->addMessageHandler(MyMessageHandler::class);
    $queue->boot();
}
```

::: warning ВАЖНО
Обработчики должны добавляться до инициализации системы очередей (вызова метода `boot()`).
:::

**Ссылки по теме:**

- [Messenger: Sync & Queued Message Handling](https://symfony.com/doc/current/messenger.html)
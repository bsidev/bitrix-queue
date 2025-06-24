# Начало работы

## Установка

::: warning Требования
- PHP >=8.0
- 1С-Битрикс >=22.0.0
- composer/installers ^1.0
:::

1. Настройте пути установки модулей в `composer.json`:

```json
{
  "extra": {
    "installer-paths": {
      "bitrix/modules/{$name}/": [
        "type:bitrix-d7-module"
      ]
    }
  }
}
```

> Указывается путь до папки `bitrix/modules` относительно файла `composer.json`.

2. Установите модуль через [Composer](https://getcomposer.org/):

```sh
composer require bsidev/bitrix-queue
```

3. Перейдите в раздел Marketplace административной панели и установите модуль следуя инструкциям.

```
http://домен/bitrix/admin/partner_modules.php?id=bsi.queue&lang=ru&install=Y
```

## Настройка

Проинициализируйте ядро модуля:

```php
<?php

// local/php_interface/init.php

use Bitrix\Main\Loader;
use Bsi\Queue\Queue;

// ...

if (Loader::includeModule('bsi.queue')) {
    Queue::getInstance()->boot();
}
```

## Запуск воркера

Для запуска обработки сообщений используется консольный скрипт:

```
php bitrix/modules/bsi.queue/bin/console messenger:consume async --time-limit=3600
```

[Consuming Messages (Running the Worker)](https://symfony.com/doc/current/messenger.html#consuming-messages-running-the-worker)

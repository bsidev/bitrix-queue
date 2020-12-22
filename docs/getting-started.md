# Начало работы

## Установка

::: warning Требования
- PHP >=7.2.5
- 1С-Битрикс >=17.5.10
- composer/installers ^1.0
:::

1. Настройте пути установки модулей в `composer.json`:

```json
{
  ...

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
// local/php_interface/init.php

// ...

use Bitrix\Main\Loader;
use Bsi\Queue\Queue;

if (Loader::includeModule('bsi.queue')) {
    $queue = Queue::getInstance();
    $queue->boot();
}
```

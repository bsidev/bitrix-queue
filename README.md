<p align="center">
<a href="https://github.com/bsidev/bitrix-queue/actions"><img src="https://github.com/bsidev/bitrix-queue/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/bsidev/bitrix-queue"><img src="https://poser.pugx.org/bsidev/bitrix-queue/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/bsidev/bitrix-queue"><img src="https://poser.pugx.org/bsidev/bitrix-queue/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/bsidev/bitrix-queue"><img src="https://poser.pugx.org/bsidev/bitrix-queue/license.svg" alt="License"></a>
</p>

# Модуль очередей

Модуль очередей для 1С-Битрикс. Позволяет отложено обрабатывать команды из приложения.

Модуль является "мостом" для компонента [symfony/messenger](https://symfony.com/doc/current/messenger.html).

**Основные возможности:**

- Поддержка почти всех возможностей оригинального компонента.
- Дополнительный "транспорт" `bitrix://` для передачи сообщений через Bitrix ORM.
- Возможность вносить правки в конфигурацию модуля извне посредством обработчиков событий.
- Мониторинг очередей с дашбордом.

**Требования:**

- PHP >=8.0
- 1С-Битрикс >=22.0.0
- composer/installers >=1.0

## Документация

- [Начало работы](#начало-работы)
- [Конфигурация](docs/configuration.md)
- [Создание обработчиков](docs/creating-message-handlers.md)
- [Регистрация транспортов](docs/transports-registration.md)
- [Конфигурация Supervisor](docs/supervisor-configuration.md)
- [Мониторинг](docs/monitoring.md)
- [Адаптер мониторинга](docs/monitoring-adapters-registration.md)
- [События](docs/events.md)

## Начало работы

1. Установите модуль через [Composer](https://getcomposer.org/):

    ```sh
    composer require bsidev/bitrix-queue
    ```

2. Перейдите в раздел Marketplace административной панели и установите модуль следуя инструкциям.

    ```
    http://домен/bitrix/admin/partner_modules.php?id=bsi.queue&lang=ru&install=Y
    ```

3. Проинициализируйте ядро модуля:

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

4. Запуск воркера обработки сообщений можно произвести двумя способами:

   <br>Bitrix CLI
   ```shell
   php bitrix/bitrix.php bsi.queue:consume async --time-limit=3600
   ```

   Нативный скрипт
    ```shell
    php bitrix/modules/bsi.queue/bin/console messenger:consume async --time-limit=3600
    ```

    [Consuming Messages (Running the Worker)](https://symfony.com/doc/current/messenger.html#consuming-messages-running-the-worker)

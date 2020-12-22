# Конфигурация Supervisor

Supervisor - это серверная утилита, которая позволяет контролировать процессы-воркеры. Она автоматически перезапускает процессы в случае ошибок или удачного завершения, позволяет масштабировать процессы и др.

Пример установки пакета в ОС CentOS:

```
yum install supervisor
```

Пример файла конфигурации:

```ini
;/etc/supervisor.d/messenger-worker.ini
[program:messenger-worker]
directory=/home/bitrix/www
command=php bitrix/modules/bsi.queue/bin/console messenger:consume async --time-limit=3600
user=bitrix
numprocs=2
startsecs=0
autostart=true
autorestart=true
process_name=%(program_name)s_%(process_num)02d
```

::: warning Внимание
При внесении правок в обработчики сообщений необходимо перезапустить все процессы-воркеры. Для этого можно воспользоваться командой:

```
php bitrix/modules/bsi.queue/bin/console messenger:stop-workers
```

Она ждет успешной обработки последней итерации и останавливает процесс. Затем Supervisor создаст новые рабочие процессы.
:::

**Ссылки по теме:**

- [Supervisor configuration](https://symfony.com/doc/current/messenger.html#supervisor-configuration)

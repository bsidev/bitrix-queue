<?php

require dirname(__DIR__) . '/vendor/autoload.php';

// Bitrix autoloader
spl_autoload_register(static function (string $className) {
    $file = ltrim($className, '\\');
    $file = strtolower($file);
    $ns = 'bsi/queue/';

    $tryFiles = [$file];
    if (substr($file, -5) === 'table') {
        $tryFiles[] = substr($file, 0, -5);
    }

    foreach ($tryFiles as $file) {
        $file = str_replace('\\', '/', $file);
        if (strpos($file, $ns) === 0) {
            $path = dirname(__DIR__) . '/lib' . substr($file, strlen($ns) - 1) . '.php';
            if (file_exists($path)) {
                include $path;
            }
        }
    }
});

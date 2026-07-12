<?php

spl_autoload_register(function (string $class): void {
    $prefixes = [
        'App\\' => __DIR__.'/../app/',
        'Database\\Factories\\' => __DIR__.'/../database/factories/',
        'Database\\Seeders\\' => __DIR__.'/../database/seeders/',
    ];

    foreach ($prefixes as $prefix => $basePath) {
        if (! str_starts_with($class, $prefix)) {
            continue;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = $basePath.str_replace('\\', '/', $relativeClass).'.php';

        if (is_file($file)) {
            require $file;
        }

        return;
    }
}, true, true);

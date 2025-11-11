<?php

spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);

    $baseDirs = [
        __DIR__ . '/app/components/',
        __DIR__ . '/app/controllers/',
        __DIR__ . '/app/models/',
        __DIR__ . '/config/',
        __DIR__ . '/helpers/',
    ];

    foreach ($baseDirs as $baseDir) {
        $file = $baseDir . basename($class) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    die("Autoloader error: Class '$class' not found.");
});

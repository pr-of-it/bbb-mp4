<?php

spl_autoload_register(function ($class) {
    if (preg_match('~^ProfIT\\\\Bbb\\\\(.+)$~', $class, $m)) {
        require __DIR__ . '/src/' . str_replace('\\', '/', $m[1]) . '.php';
    }
});
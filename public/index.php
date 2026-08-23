<?php

// Autoloader ساده برای کلاس‌های App
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Core\Router;

$router = new Router();

// تعریف مسیرها
$router->get('/', 'HomeController@index');
$router->get('/home', 'HomeController@index');

// اجرای Router
$router->dispatch();
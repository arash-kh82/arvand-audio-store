<?php

declare(strict_types=1);

$root = dirname(__DIR__);

require_once $root . '/bootstrap.php';

use App\Core\Router;

/*
|--------------------------------------------------------------------------
| Session Configuration
|--------------------------------------------------------------------------
*/

$sessionName = (string) app_config(
    'session_name',
    'ARVANDSESSID'
);

if ($sessionName !== '') {
    session_name($sessionName);
}

$cookie = app_config('cookie', []);

if (!is_array($cookie)) {
    $cookie = [];
}

$secure = (bool) (
    $cookie['secure']
    ?? (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
);

$httponly = (bool) (
    $cookie['httponly'] ?? true
);

$samesite = (string) (
    $cookie['samesite'] ?? 'Lax'
);

$path = (string) (
    $cookie['path'] ?? '/'
);

$domain = (string) (
    $cookie['domain'] ?? ''
);

$lifetime = (int) (
    $cookie['lifetime'] ?? 0
);

session_set_cookie_params([
    'lifetime' => $lifetime,
    'path' => $path !== '' ? $path : '/',
    'domain' => $domain,
    'secure' => $secure,
    'httponly' => $httponly,
    'samesite' => $samesite,
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Router
|--------------------------------------------------------------------------
*/

$router = new Router();

require $root . '/routes/web.php';

$router->dispatch();
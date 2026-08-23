<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$composerAutoload = $root . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require_once $composerAutoload;
}

spl_autoload_register(static function (string $class) use ($root): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = $root . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

$appConfig = [];
$configFile = $root . '/config/app.php';
if (is_file($configFile)) {
    $loaded = require $configFile;
    if (is_array($loaded)) {
        $appConfig = $loaded;
    }
}

$GLOBALS['app_config'] = $appConfig;

if (!function_exists('app_config')) {
    function app_config(?string $key = null, mixed $default = null): mixed
    {
        $config = $GLOBALS['app_config'] ?? [];
        if ($key === null) {
            return $config;
        }

        return $config[$key] ?? $default;
    }
}

$sessionName = (string) app_config('session_name', 'ARVANDSESSID');
if ($sessionName !== '') {
    session_name($sessionName);
}

$cookie = app_config('cookie', []);
if (!is_array($cookie)) {
    $cookie = [];
}

$secure = (bool) ($cookie['secure'] ?? (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'));
$httponly = (bool) ($cookie['httponly'] ?? true);
$samesite = (string) ($cookie['samesite'] ?? 'Lax');
$path = (string) ($cookie['path'] ?? '/');
$domain = (string) ($cookie['domain'] ?? '');
$lifetime = (int) ($cookie['lifetime'] ?? 0);

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

use App\Core\Router;

$router = new Router();
require $root . '/routes/web.php';
$router->dispatch();
<?php

declare(strict_types=1);

$root = __DIR__;

/*
|--------------------------------------------------------------------------
| Composer Autoload
|--------------------------------------------------------------------------
*/

$composerAutoload = $root . '/vendor/autoload.php';

if (is_file($composerAutoload)) {
    require_once $composerAutoload;
}

/*
|--------------------------------------------------------------------------
| Application Autoload
|--------------------------------------------------------------------------
*/

spl_autoload_register(static function (string $class) use ($root): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));

    $file = $root
        . '/app/'
        . str_replace('\\', '/', $relative)
        . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

/*
|--------------------------------------------------------------------------
| Application Configuration
|--------------------------------------------------------------------------
*/

$configFile = $root . '/config/app.php';

if (!is_file($configFile)) {
    throw new RuntimeException(
        'فایل تنظیمات config/app.php پیدا نشد.'
    );
}

$loadedConfig = require $configFile;

if (!is_array($loadedConfig)) {
    throw new RuntimeException(
        'ساختار config/app.php معتبر نیست.'
    );
}

$GLOBALS['app_config'] = $loadedConfig;

/*
|--------------------------------------------------------------------------
| Configuration Helper
|--------------------------------------------------------------------------
*/

if (!function_exists('app_config')) {
    function app_config(
        ?string $key = null,
        mixed $default = null
    ): mixed {
        $config = $GLOBALS['app_config'] ?? [];

        if ($key === null) {
            return $config;
        }

        /*
         * Support dot notation:
         * database.host
         * database.database
         * cookie.httponly
         */
        $segments = explode('.', $key);

        $value = $config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}
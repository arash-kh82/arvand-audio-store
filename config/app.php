<?php

declare(strict_types=1);

return [
    'name' => 'Arvand Audio Store',

    'env' => 'local',

    // ============================================
    // تنظیمات جدید تلگرام (توکن از secrets.php خوانده می‌شود)
    // ============================================
    'telegram' => [
        'bot_token' => (function (): string {
            $secretsFile = __DIR__ . '/secrets.php';

            if (!is_file($secretsFile)) {
                return '';
            }

            $secrets = require $secretsFile;

            return trim(
                (string) (
                    $secrets['telegram_bot_token']
                    ?? ''
                )
            );
        })(),
    ],

    // ============================================
    // تنظیمات فعلی (دست نزنید)
    // ============================================
    'base_url' => '/arvand-audio-store/public',

    'session_name' => 'ARVANDSESSID',

    'csrf_key' => 'arvand_audio_store_csrf',

    'database' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'arvand_audio_store',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],

    'cookie' => [
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ],
];
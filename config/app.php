<?php

declare(strict_types=1);

return [
    'name' => 'Arvand Audio Store',
    'env' => 'local',
    'base_url' => (function (): string {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        if ($scriptName === '') {
            return '';
        }

        $base = str_replace('\\', '/', dirname($scriptName));
        if ($base === '.' || $base === '/') {
            return '';
        }

        return rtrim($base, '/');
    })(),
    'session_name' => 'ARVANDSESSID',
    'csrf_key' => 'arvand_audio_store_csrf',
    'cookie' => [
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ],
];
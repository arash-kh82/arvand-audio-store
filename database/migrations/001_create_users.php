<?php

declare(strict_types=1);

use App\Core\Database;

function findProjectRootForMigration(string $startDir): string
{
    $dir = $startDir;
    while (true) {
        if (is_file($dir . '/composer.json') && is_dir($dir . '/app') && is_dir($dir . '/public')) {
            return $dir;
        }

        $parent = dirname($dir);
        if ($parent === $dir) {
            break;
        }
        $dir = $parent;
    }

    throw new RuntimeException('Project root not found for migration.');
}

function requireAutoloadOrCore(string $root): void
{
    $candidates = [
        $root . '/vendor/autoload.php',
        dirname($root) . '/vendor/autoload.php',
        dirname($root, 2) . '/vendor/autoload.php',
    ];

    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            require_once $candidate;
            return;
        }
    }

    $core = $root . '/app/Core/Database.php';
    if (is_file($core)) {
        require_once $core;
        return;
    }

    throw new RuntimeException('Unable to load autoload or Database class.');
}

$root = findProjectRootForMigration(__DIR__);
requireAutoloadOrCore($root);

if (!class_exists(Database::class)) {
    throw new RuntimeException('Database class is unavailable.');
}

$pdo = Database::connect();

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS users (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(30) NOT NULL DEFAULT 'customer',
        status VARCHAR(30) NOT NULL DEFAULT 'active',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY users_email_unique (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

$pdo->exec("ALTER TABLE users ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

echo 'Created/Updated: database/migrations/001_create_users.php' . PHP_EOL;
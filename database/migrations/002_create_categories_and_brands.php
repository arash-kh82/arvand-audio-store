<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$bootstrap = $root . '/bootstrap.php';

if (!is_file($bootstrap)) {
    throw new RuntimeException(
        'bootstrap.php not found.'
    );
}

require_once $bootstrap;

use App\Core\Database;

$pdo = Database::connect();

/*
|--------------------------------------------------------------------------
| Categories
|--------------------------------------------------------------------------
*/

$pdo->exec(
    "
    CREATE TABLE IF NOT EXISTS categories (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

        parent_id BIGINT UNSIGNED NULL,

        name VARCHAR(150) NOT NULL,

        slug VARCHAR(180) NOT NULL UNIQUE,

        description TEXT NULL,

        image VARCHAR(255) NULL,

        status TINYINT(1)
            NOT NULL DEFAULT 1,

        created_at TIMESTAMP
            NOT NULL DEFAULT CURRENT_TIMESTAMP,

        updated_at TIMESTAMP
            NOT NULL DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP,

        CONSTRAINT fk_categories_parent
            FOREIGN KEY (parent_id)
            REFERENCES categories(id)
            ON DELETE SET NULL

    ) ENGINE=InnoDB
      DEFAULT CHARSET=utf8mb4
      COLLATE=utf8mb4_unicode_ci
    "
);

/*
|--------------------------------------------------------------------------
| Brands
|--------------------------------------------------------------------------
*/

$pdo->exec(
    "
    CREATE TABLE IF NOT EXISTS brands (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

        name VARCHAR(150) NOT NULL,

        slug VARCHAR(180) NOT NULL UNIQUE,

        logo VARCHAR(255) NULL,

        status TINYINT(1)
            NOT NULL DEFAULT 1,

        created_at TIMESTAMP
            NOT NULL DEFAULT CURRENT_TIMESTAMP,

        updated_at TIMESTAMP
            NOT NULL DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP

    ) ENGINE=InnoDB
      DEFAULT CHARSET=utf8mb4
      COLLATE=utf8mb4_unicode_ci
    "
);

/*
|--------------------------------------------------------------------------
| Character Set
|--------------------------------------------------------------------------
*/

$pdo->exec(
    "
    ALTER TABLE categories
    ENGINE=InnoDB,
    CONVERT TO CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci
    "
);

$pdo->exec(
    "
    ALTER TABLE brands
    ENGINE=InnoDB,
    CONVERT TO CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci
    "
);

echo 'Categories and brands migration completed successfully.'
    . PHP_EOL;
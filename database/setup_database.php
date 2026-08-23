<?php

declare(strict_types=1);

$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$password = '';
$dbName = 'arvand_audio_store';

try {
    /*
     * اتصال بدون انتخاب Database
     */
    $pdo = new PDO(
        "mysql:host={$host};port={$port};charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    /*
     * ساخت Database
     */
    $pdo->exec(
        "CREATE DATABASE IF NOT EXISTS `{$dbName}`
         CHARACTER SET utf8mb4
         COLLATE utf8mb4_unicode_ci"
    );

    $pdo->exec("USE `{$dbName}`");

    /*
     * users
     */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL,
            phone VARCHAR(20) NULL,
            password VARCHAR(255) NOT NULL,

            role ENUM('customer', 'admin')
                NOT NULL DEFAULT 'customer',

            status ENUM('active', 'inactive', 'blocked')
                NOT NULL DEFAULT 'active',

            created_at TIMESTAMP
                NOT NULL DEFAULT CURRENT_TIMESTAMP,

            updated_at TIMESTAMP
                NOT NULL DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,

            UNIQUE KEY users_email_unique (email),
            UNIQUE KEY users_phone_unique (phone)

        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
    ");

    /*
     * categories
     */
    $pdo->exec("
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
    ");

    /*
     * brands
     */
    $pdo->exec("
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
    ");

    /*
     * products
     */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

            category_id BIGINT UNSIGNED NOT NULL,

            brand_id BIGINT UNSIGNED NULL,

            name VARCHAR(200) NOT NULL,

            slug VARCHAR(230) NOT NULL UNIQUE,

            sku VARCHAR(100) NOT NULL UNIQUE,

            description TEXT NULL,

            price DECIMAL(15,2) NOT NULL DEFAULT 0,

            discount_price DECIMAL(15,2) NULL,

            stock INT UNSIGNED NOT NULL DEFAULT 0,

            image VARCHAR(255) NULL,

            status ENUM('active', 'inactive')
                NOT NULL DEFAULT 'active',

            featured TINYINT(1)
                NOT NULL DEFAULT 0,

            created_at TIMESTAMP
                NOT NULL DEFAULT CURRENT_TIMESTAMP,

            updated_at TIMESTAMP
                NOT NULL DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT fk_products_category
                FOREIGN KEY (category_id)
                REFERENCES categories(id)
                ON DELETE RESTRICT,

            CONSTRAINT fk_products_brand
                FOREIGN KEY (brand_id)
                REFERENCES brands(id)
                ON DELETE SET NULL

        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
    ");

    /*
     * addresses
     */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS addresses (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

            user_id BIGINT UNSIGNED NOT NULL,

            title VARCHAR(80) NULL,

            receiver_name VARCHAR(120) NOT NULL,

            phone VARCHAR(20) NOT NULL,

            province VARCHAR(100) NOT NULL,

            city VARCHAR(100) NOT NULL,

            address TEXT NOT NULL,

            postal_code VARCHAR(20) NULL,

            created_at TIMESTAMP
                NOT NULL DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT fk_addresses_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE

        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
    ");

    /*
     * orders
     */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

            user_id BIGINT UNSIGNED NOT NULL,

            address_id BIGINT UNSIGNED NULL,

            order_number VARCHAR(40) NOT NULL UNIQUE,

            status ENUM(
                'pending',
                'paid',
                'processing',
                'shipped',
                'delivered',
                'cancelled'
            ) NOT NULL DEFAULT 'pending',

            payment_status ENUM(
                'pending',
                'success',
                'failed'
            ) NOT NULL DEFAULT 'pending',

            subtotal DECIMAL(15,2)
                NOT NULL DEFAULT 0,

            discount DECIMAL(15,2)
                NOT NULL DEFAULT 0,

            shipping_cost DECIMAL(15,2)
                NOT NULL DEFAULT 0,

            total DECIMAL(15,2)
                NOT NULL DEFAULT 0,

            created_at TIMESTAMP
                NOT NULL DEFAULT CURRENT_TIMESTAMP,

            updated_at TIMESTAMP
                NOT NULL DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT fk_orders_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE RESTRICT,

            CONSTRAINT fk_orders_address
                FOREIGN KEY (address_id)
                REFERENCES addresses(id)
                ON DELETE SET NULL

        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
    ");

    /*
     * order_items
     */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS order_items (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

            order_id BIGINT UNSIGNED NOT NULL,

            product_id BIGINT UNSIGNED NULL,

            product_name VARCHAR(200) NOT NULL,

            price DECIMAL(15,2) NOT NULL,

            quantity INT UNSIGNED NOT NULL,

            total DECIMAL(15,2) NOT NULL,

            CONSTRAINT fk_order_items_order
                FOREIGN KEY (order_id)
                REFERENCES orders(id)
                ON DELETE CASCADE,

            CONSTRAINT fk_order_items_product
                FOREIGN KEY (product_id)
                REFERENCES products(id)
                ON DELETE SET NULL

        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
    ");

    /*
     * payments
     */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS payments (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

            order_id BIGINT UNSIGNED NOT NULL,

            amount DECIMAL(15,2) NOT NULL,

            transaction_code VARCHAR(120) NULL,

            status ENUM(
                'pending',
                'success',
                'failed'
            ) NOT NULL DEFAULT 'pending',

            paid_at DATETIME NULL,

            created_at TIMESTAMP
                NOT NULL DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT fk_payments_order
                FOREIGN KEY (order_id)
                REFERENCES orders(id)
                ON DELETE CASCADE

        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
    ");

    /*
     * cart_items
     *
     * این جدول را از همین الان می‌سازیم
     * تا سبد خرید بعداً روی ساختار آماده پیاده شود.
     */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cart_items (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

            user_id BIGINT UNSIGNED NOT NULL,

            product_id BIGINT UNSIGNED NOT NULL,

            quantity INT UNSIGNED NOT NULL DEFAULT 1,

            created_at TIMESTAMP
                NOT NULL DEFAULT CURRENT_TIMESTAMP,

            updated_at TIMESTAMP
                NOT NULL DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,

            UNIQUE KEY cart_user_product_unique
                (user_id, product_id),

            CONSTRAINT fk_cart_items_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE,

            CONSTRAINT fk_cart_items_product
                FOREIGN KEY (product_id)
                REFERENCES products(id)
                ON DELETE CASCADE

        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
    ");

    /*
     * product_images
     */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS product_images (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

            product_id BIGINT UNSIGNED NOT NULL,

            image VARCHAR(255) NOT NULL,

            alt_text VARCHAR(255) NULL,

            sort_order INT UNSIGNED
                NOT NULL DEFAULT 0,

            created_at TIMESTAMP
                NOT NULL DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT fk_product_images_product
                FOREIGN KEY (product_id)
                REFERENCES products(id)
                ON DELETE CASCADE

        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
    ");

    /*
     * telegram_users
     */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS telegram_users (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

            user_id BIGINT UNSIGNED NULL,

            telegram_id BIGINT NOT NULL UNIQUE,

            username VARCHAR(100) NULL,

            first_name VARCHAR(120) NULL,

            last_name VARCHAR(120) NULL,

            is_active TINYINT(1)
                NOT NULL DEFAULT 1,

            created_at TIMESTAMP
                NOT NULL DEFAULT CURRENT_TIMESTAMP,

            updated_at TIMESTAMP
                NOT NULL DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT fk_telegram_users_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE SET NULL

        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
    ");

    echo "========================================\n";
    echo "Arvand Audio Store Database\n";
    echo "========================================\n\n";

    $tables = [
        'users',
        'categories',
        'brands',
        'products',
        'addresses',
        'orders',
        'order_items',
        'payments',
        'cart_items',
        'product_images',
        'telegram_users',
    ];

    foreach ($tables as $table) {
        echo "[OK] {$table}\n";
    }

    echo "\nDatabase setup completed successfully.\n";
    echo "Database: {$dbName}\n";

} catch (PDOException $exception) {
    echo "\nDatabase setup failed:\n";
    echo $exception->getMessage() . "\n";
    exit(1);
}
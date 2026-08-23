<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

require_once $root . '/bootstrap.php';

use App\Core\Database;

try {
    $db = Database::connect();

    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    */

    $categories = [
        [
            'id' => 1,
            'name' => 'میکروفون استودیویی',
            'slug' => 'studio-microphones',
        ],
        [
            'id' => 2,
            'name' => 'هدفون مانیتورینگ',
            'slug' => 'monitoring-headphones',
        ],
        [
            'id' => 3,
            'name' => 'کارت صدا',
            'slug' => 'audio-interfaces',
        ],
    ];

    $categoryStatement = $db->prepare("
        INSERT IGNORE INTO categories
        (
            id,
            name,
            slug
        )
        VALUES
        (
            :id,
            :name,
            :slug
        )
    ");

    foreach ($categories as $category) {
        $categoryStatement->execute([
            ':id' => $category['id'],
            ':name' => $category['name'],
            ':slug' => $category['slug'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Brands
    |--------------------------------------------------------------------------
    */

    $brands = [
        [
            'id' => 1,
            'name' => 'Shure',
            'slug' => 'shure',
        ],
        [
            'id' => 2,
            'name' => 'Sennheiser',
            'slug' => 'sennheiser',
        ],
        [
            'id' => 3,
            'name' => 'Focusrite',
            'slug' => 'focusrite',
        ],
    ];

    $brandStatement = $db->prepare("
        INSERT IGNORE INTO brands
        (
            id,
            name,
            slug
        )
        VALUES
        (
            :id,
            :name,
            :slug
        )
    ");

    foreach ($brands as $brand) {
        $brandStatement->execute([
            ':id' => $brand['id'],
            ':name' => $brand['name'],
            ':slug' => $brand['slug'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */

    $products = [
        [
            'id' => 1,
            'category_id' => 1,
            'brand_id' => 1,
            'name' => 'میکروفون Shure SM7B',
            'slug' => 'shure-sm7b',
            'sku' => 'SHURE-SM7B',
            'price' => 24500000,
            'stock' => 5,
            'description' =>
                'میکروفون داینامیک حرفه‌ای مناسب پادکست و وکال استودیو',
        ],
        [
            'id' => 2,
            'category_id' => 2,
            'brand_id' => 2,
            'name' => 'هدفون Sennheiser HD 600',
            'slug' => 'sennheiser-hd-600',
            'sku' => 'SENN-HD600',
            'price' => 18900000,
            'stock' => 8,
            'description' =>
                'هدفون پشت باز مانیتورینگ دقیق و شفاف',
        ],
        [
            'id' => 3,
            'category_id' => 3,
            'brand_id' => 3,
            'name' => 'کارت صدا Focusrite Scarlett 2i2 G4',
            'slug' => 'scarlett-2i2-g4',
            'sku' => 'FOCUS-2I2-G4',
            'price' => 11200000,
            'stock' => 12,
            'description' =>
                'کارت صدای محبوب استودیویی نسل چهارم',
        ],
    ];

    $productStatement = $db->prepare("
        INSERT IGNORE INTO products
        (
            id,
            category_id,
            brand_id,
            name,
            slug,
            sku,
            price,
            stock,
            description,
            status,
            featured
        )
        VALUES
        (
            :id,
            :category_id,
            :brand_id,
            :name,
            :slug,
            :sku,
            :price,
            :stock,
            :description,
            'active',
            1
        )
    ");

    foreach ($products as $product) {
        $productStatement->execute([
            ':id' => $product['id'],
            ':category_id' => $product['category_id'],
            ':brand_id' => $product['brand_id'],
            ':name' => $product['name'],
            ':slug' => $product['slug'],
            ':sku' => $product['sku'],
            ':price' => $product['price'],
            ':stock' => $product['stock'],
            ':description' => $product['description'],
        ]);
    }

    echo "========================================\n";
    echo "Seed completed successfully.\n";
    echo "========================================\n";
    echo "Categories: OK\n";
    echo "Brands: OK\n";
    echo "Products: OK\n";

} catch (Throwable $exception) {
    echo "Seed failed:\n";
    echo $exception->getMessage() . "\n";
    exit(1);
}
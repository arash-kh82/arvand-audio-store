<?php
require_once __DIR__ . '/../../app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::connect();

    // 1. دسته‌بندی‌ها
    $db->exec("
        INSERT IGNORE INTO categories (id, name, slug) VALUES 
        (1, 'میکروفون استودیویی', 'studio-microphones'),
        (2, 'هدفون مانیتورینگ', 'monitoring-headphones'),
        (3, 'کارت صدا', 'audio-interfaces');
    ");

    // 2. برندها
    $db->exec("
        INSERT IGNORE INTO brands (id, name, slug) VALUES 
        (1, 'Shure', 'shure'),
        (2, 'Sennheiser', 'sennheiser'),
        (3, 'Focusrite', 'focusrite');
    ");

    // 3. محصولات
    $db->exec("
        INSERT IGNORE INTO products (id, category_id, brand_id, name, slug, price, stock, description) VALUES 
        (1, 1, 1, 'میکروفون Shure SM7B', 'shure-sm7b', 24500000, 5, 'میکروفون داینامیک حرفه‌ای مناسب پادکست و وکال استودیو'),
        (2, 2, 2, 'هدفون Sennheiser HD 600', 'sennheiser-hd-600', 18900000, 8, 'هدفون پشت باز مانیتورینگ دقیق و شفاف'),
        (3, 3, 3, 'کارت صدا Focusrite Scarlett 2i2 G4', 'scarlett-2i2-g4', 11200000, 12, 'کارت صدای محبوب استودیویی نسل چهارم');
    ");

    echo "✅ Seed data inserted successfully!\n";
} catch (Exception $e) {
    echo "❌ Error seeding data: " . $e->getMessage() . "\n";
}
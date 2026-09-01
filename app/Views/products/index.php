<?php

declare(strict_types=1);

$products = is_array($products ?? null) ? $products : [];
$categories = is_array($categories ?? null) ? $categories : [];
$brands = is_array($brands ?? null) ? $brands : [];

$baseUrl = rtrim(
    (string) app_config('base_url', ''),
    '/'
);

$currentSearch = (string) ($_GET['q'] ?? '');
$currentCategory = (string) ($_GET['category'] ?? '');
$currentBrand = (string) ($_GET['brand'] ?? '');
$currentMinPrice = (string) ($_GET['min_price'] ?? '');
$currentMaxPrice = (string) ($_GET['max_price'] ?? '');
$currentSort = (string) ($_GET['sort'] ?? '');

$currentInStock = isset($_GET['in_stock'])
    && (string) $_GET['in_stock'] === '1';

function productIndexImage(
    array $product,
    string $baseUrl
): ?string {
    /*
     * لیست کلیدهای احتمالی برای تصویر اصلی
     */
    $imageKeys = [
        'image',
        'primary_image',
        'image_url',
        'thumbnail',
        'picture',
        'img',
        'photo',
        'main_image',
        'featured_image'
    ];

    $image = '';

    /*
     * بررسی کلیدهای مختلف برای تصویر اصلی
     */
    foreach ($imageKeys as $key) {
        if (!empty($product[$key])) {
            $image = trim((string) $product[$key]);
            if ($image !== '') {
                break;
            }
        }
    }

    /*
     * اگر تصویر اصلی خالی بود،
     * اولین تصویر product_images را بررسی کن.
     */
    if ($image === '') {
        $images = $product['images'] ?? [];

        if (is_array($images)) {
            foreach ($images as $item) {
                if (is_array($item)) {
                    $candidate = trim(
                        (string) (
                            $item['image']
                            ?? $item['path']
                            ?? $item['url']
                            ?? $item['src']
                            ?? $item['file']
                            ?? ''
                        )
                    );
                } elseif (is_string($item)) {
                    $candidate = trim((string) $item);
                } else {
                    $candidate = '';
                }

                if ($candidate !== '') {
                    $image = $candidate;
                    break;
                }
            }
        }
    }

    /*
     * اگر باز هم تصویری پیدا نشد، 
     * بررسی کن که آیا product['images'] یک آرایه ساده از رشته‌هاست
     */
    if ($image === '' && isset($product['images']) && is_array($product['images'])) {
        foreach ($product['images'] as $item) {
            if (is_string($item) && trim($item) !== '') {
                $image = trim($item);
                break;
            }
        }
    }

    if ($image === '') {
        return null;
    }

    /*
     * URL کامل
     */
    if (
        strpos($image, 'http://') === 0
        || strpos($image, 'https://') === 0
    ) {
        return $image;
    }

    /*
     * حذف اسلش اضافی از ابتدا
     */
    $image = ltrim($image, '/');

    /*
     * مسیر نسبی سایت
     */
    return $baseUrl . '/' . $image;
}

function productIndexPrice(array $product): float
{
    $discount = $product['discount_price'] ?? null;

    if (
        $discount !== null
        && $discount !== ''
        && (float) $discount > 0
    ) {
        return (float) $discount;
    }

    return (float) ($product['price'] ?? 0);
}

// فیلتر کردن محصولات بر اساس جستجو
if ($currentSearch !== '') {
    $products = array_filter($products, function ($product) use ($currentSearch) {
        $name = strtolower((string) ($product['name'] ?? $product['title'] ?? ''));
        $search = strtolower(trim($currentSearch));
        return strpos($name, $search) !== false;
    });
}

// فیلتر بر اساس دسته‌بندی
if ($currentCategory !== '') {
    $products = array_filter($products, function ($product) use ($currentCategory) {
        $categorySlug = strtolower((string) ($product['category_slug'] ?? $product['category_id'] ?? ''));
        $categoryName = strtolower((string) ($product['category_name'] ?? ''));
        $search = strtolower(trim($currentCategory));
        return strpos($categorySlug, $search) !== false || strpos($categoryName, $search) !== false;
    });
}

// فیلتر بر اساس برند
if ($currentBrand !== '') {
    $products = array_filter($products, function ($product) use ($currentBrand) {
        $brandSlug = strtolower((string) ($product['brand_slug'] ?? $product['brand_id'] ?? ''));
        $brandName = strtolower((string) ($product['brand_name'] ?? ''));
        $search = strtolower(trim($currentBrand));
        return strpos($brandSlug, $search) !== false || strpos($brandName, $search) !== false;
    });
}

// فیلتر بر اساس قیمت
if ($currentMinPrice !== '') {
    $minPrice = (float) $currentMinPrice;
    $products = array_filter($products, function ($product) use ($minPrice) {
        return productIndexPrice($product) >= $minPrice;
    });
}

if ($currentMaxPrice !== '') {
    $maxPrice = (float) $currentMaxPrice;
    $products = array_filter($products, function ($product) use ($maxPrice) {
        return productIndexPrice($product) <= $maxPrice;
    });
}

// فیلتر موجودی
if ($currentInStock) {
    $products = array_filter($products, function ($product) {
        return (int) ($product['stock'] ?? 0) > 0;
    });
}

// مرتب‌سازی
if ($currentSort !== '') {
    usort($products, function ($a, $b) use ($currentSort) {
        $priceA = productIndexPrice($a);
        $priceB = productIndexPrice($b);

        switch ($currentSort) {
            case 'price_asc':
                return $priceA <=> $priceB;
            case 'price_desc':
                return $priceB <=> $priceA;
            case 'newest':
                $dateA = strtotime($a['created_at'] ?? $a['date'] ?? '2000-01-01');
                $dateB = strtotime($b['created_at'] ?? $b['date'] ?? '2000-01-01');
                return $dateB <=> $dateA;
            default:
                return 0;
        }
    });
}

// بازنشانی کلیدهای آرایه بعد از فیلتر کردن
$products = array_values($products);

?>

<!DOCTYPE html>

<html
    lang="fa"
    dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        محصولات | آروند Audio
    </title>

    <link
        rel="stylesheet"
        href="<?= $baseUrl ?>/assets/css/app.css">

    <style>
        .products-page {
            padding-block: 45px 70px;
        }

        .products-layout {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            gap: 28px;
            align-items: start;
        }

        .products-sidebar {
            position: sticky;
            top: 95px;

            background: #fff;
            border: 1px solid #e7e9ed;
            border-radius: 16px;
            padding: 20px;
        }

        .products-sidebar-title {
            margin: 0 0 18px;
            font-size: 1.05rem;
            font-weight: 900;
        }

        .filter-group {
            padding-bottom: 18px;
            margin-bottom: 18px;
            border-bottom: 1px solid #eceef1;
        }

        .filter-group:last-child {
            border-bottom: 0;
            margin-bottom: 0;
        }

        .filter-label {
            display: block;
            margin-bottom: 8px;

            color: #4b515b;
            font-size: .82rem;
            font-weight: 800;
        }

        .filter-input,
        .filter-select {
            width: 100%;
            min-height: 42px;

            padding: 8px 11px;

            border: 1px solid #dfe2e6;
            border-radius: 9px;

            background: #fff;
            color: #222;

            font-family: inherit;
            font-size: .82rem;

            outline: none;
        }

        .filter-input:focus,
        .filter-select:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, .12);
        }

        .price-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .check-row {
            display: flex;
            align-items: center;
            gap: 8px;

            color: #555b65;
            font-size: .82rem;

            cursor: pointer;
        }

        .check-row input {
            accent-color: #f59e0b;
        }

        .products-main {
            min-width: 0;
        }

        .products-heading {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 22px;
        }

        .products-heading h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 900;
        }

        .products-heading p {
            margin: 4px 0 0;
            color: #7b818b;
            font-size: .86rem;
        }

        .products-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;

            padding: 13px 15px;
            margin-bottom: 20px;

            background: #fff;
            border: 1px solid #e7e9ed;
            border-radius: 13px;
        }

        .products-count {
            color: #737985;
            font-size: .82rem;
        }

        .products-search {
            display: flex;
            flex: 1;
            max-width: 480px;
            gap: 8px;
        }

        .products-search input {
            flex: 1;
        }

        .product-grid {
            display: grid;
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .product-discount {
            position: absolute;
            top: 12px;
            right: 12px;

            padding: 4px 9px;

            border-radius: 999px;

            background: #ef4444;
            color: #fff;

            font-size: .7rem;
            font-weight: 800;
        }

        .product-image-wrap {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden;
            background: #f8f9fa;
            border-radius: 12px 12px 0 0;
        }

        .aa-product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .aa-product-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #d1d5db;
            background: #f3f4f6;
        }

        .product-old-price {
            margin-right: 7px;

            color: #9ca1a9;
            font-size: .75rem;

            text-decoration: line-through;
        }

        .empty-products {
            padding: 70px 20px;

            background: #fff;

            border: 1px dashed #d9dce1;
            border-radius: 16px;

            text-align: center;
        }

        .empty-products-icon {
            margin-bottom: 12px;
            font-size: 3.5rem;
        }

        .empty-products h2 {
            margin: 0 0 5px;
            font-size: 1.15rem;
        }

        .empty-products p {
            margin: 0 0 20px;
            color: #7c828c;
            font-size: .85rem;
        }

        .aa-product-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e7e9ed;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .aa-product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .aa-product-body {
            padding: 15px;
        }

        .aa-product-brand {
            color: #8a9099;
            font-size: .75rem;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .aa-product-title {
            margin: 0 0 8px;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .aa-product-bottom {
            margin-top: 10px;
        }

        .aa-price {
            font-size: 1.1rem;
            font-weight: 800;
            color: #1f2937;
        }

        .aa-price span {
            font-size: .8rem;
            font-weight: 400;
            color: #6b7280;
            margin-right: 4px;
        }

        @media (max-width: 992px) {

            .products-layout {
                grid-template-columns: 1fr;
            }

            .products-sidebar {
                position: static;
            }

            .product-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 576px) {

            .products-page {
                padding-block: 28px 50px;
            }

            .products-heading {
                align-items: start;
                flex-direction: column;
            }

            .products-toolbar {
                align-items: stretch;
                flex-direction: column;
            }

            .products-search {
                max-width: none;
            }

            .product-grid {
                grid-template-columns: 1fr;
            }

            .price-fields {
                grid-template-columns: 1fr;
            }

            .product-image-wrap {
                height: 160px;
            }
        }
    </style>

</head>

<body>

    <!-- =========================================================
     Navbar
========================================================= -->

    <nav class="aa-navbar">

        <div class="aa-container">

            <div class="aa-navbar-inner">

                <a
                    href="<?= $baseUrl ?>"
                    class="aa-brand">

                    <span class="aa-brand-icon">
                        🎧
                    </span>

                    <span>
                        آروند Audio
                    </span>

                </a>


                <nav class="aa-nav">

                    <a href="<?= $baseUrl ?>">
                        خانه
                    </a>

                    <a href="<?= $baseUrl ?>/products">
                        محصولات
                    </a>

                    <?php if (
                        class_exists('App\Core\Auth')
                        && App\Core\Auth::check()
                    ): ?>

                        <a href="<?= $baseUrl ?>/account">
                            حساب کاربری
                        </a>

                    <?php else: ?>

                        <a href="<?= $baseUrl ?>/login">
                            ورود
                        </a>

                    <?php endif; ?>

                </nav>


                <div class="aa-nav-actions">

                    <a
                        href="<?= $baseUrl ?>/products"
                        class="aa-icon-btn"
                        aria-label="محصولات">
                        🔍
                    </a>

                    <a
                        href="<?= $baseUrl ?>/cart"
                        class="aa-icon-btn"
                        aria-label="سبد خرید">
                        🛒
                    </a>

                </div>

            </div>

        </div>

    </nav>


    <!-- =========================================================
     Products
========================================================= -->

    <main class="products-page">

        <div class="aa-container">

            <div class="products-heading">

                <div>

                    <h1>
                        محصولات
                    </h1>

                    <p>
                        تجهیزات صوتی حرفه‌ای برای هر نوع استفاده
                    </p>

                </div>

                <a
                    href="<?= $baseUrl ?>"
                    class="aa-btn aa-btn-outline">
                    ← بازگشت به خانه
                </a>

            </div>


            <div class="products-layout">


                <!-- =================================================
                 Filters
            ================================================== -->

                <aside class="products-sidebar">

                    <h2 class="products-sidebar-title">
                        فیلتر محصولات
                    </h2>


                    <form
                        method="GET"
                        action="<?= $baseUrl ?>/products">

                        <!-- بخش جستجو حذف شد -->

                        <div class="filter-group">

                            <label
                                class="filter-label"
                                for="filter-category">
                                دسته‌بندی
                            </label>

                            <select
                                id="filter-category"
                                name="category"
                                class="filter-select">

                                <option value="">
                                    همه دسته‌بندی‌ها
                                </option>

                                <?php foreach (
                                    $categories
                                    as $category
                                ): ?>

                                    <?php
                                    $categoryValue = (string) (
                                        $category['slug']
                                        ?? $category['id']
                                        ?? ''
                                    );

                                    $categoryName = (string) (
                                        $category['name']
                                        ?? $category['title']
                                        ?? 'دسته‌بندی'
                                    );
                                    ?>

                                    <option
                                        value="<?= htmlspecialchars(
                                                    $categoryValue,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                        <?= $currentCategory ===
                                            $categoryValue
                                            ? 'selected'
                                            : '' ?>>
                                        <?= htmlspecialchars(
                                            $categoryName,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <div class="filter-group">

                            <label
                                class="filter-label"
                                for="filter-brand">
                                برند
                            </label>

                            <select
                                id="filter-brand"
                                name="brand"
                                class="filter-select">

                                <option value="">
                                    همه برندها
                                </option>

                                <?php foreach (
                                    $brands
                                    as $brand
                                ): ?>

                                    <?php
                                    $brandValue = (string) (
                                        $brand['slug']
                                        ?? $brand['id']
                                        ?? ''
                                    );

                                    $brandName = (string) (
                                        $brand['name']
                                        ?? 'برند'
                                    );
                                    ?>

                                    <option
                                        value="<?= htmlspecialchars(
                                                    $brandValue,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                        <?= $currentBrand ===
                                            $brandValue
                                            ? 'selected'
                                            : '' ?>>
                                        <?= htmlspecialchars(
                                            $brandName,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <div class="filter-group">

                            <label class="filter-label">
                                محدوده قیمت
                            </label>

                            <div class="price-fields">

                                <input
                                    type="number"
                                    name="min_price"
                                    class="filter-input"
                                    value="<?= htmlspecialchars(
                                                $currentMinPrice,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                    placeholder="از"
                                    min="0">

                                <input
                                    type="number"
                                    name="max_price"
                                    class="filter-input"
                                    value="<?= htmlspecialchars(
                                                $currentMaxPrice,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                    placeholder="تا"
                                    min="0">

                            </div>

                        </div>


                        <div class="filter-group">

                            <label class="check-row">

                                <input
                                    type="checkbox"
                                    name="in_stock"
                                    value="1"
                                    <?= $currentInStock
                                        ? 'checked'
                                        : '' ?>>

                                فقط محصولات موجود

                            </label>

                        </div>


                        <div class="filter-group">

                            <label
                                class="filter-label"
                                for="filter-sort">
                                مرتب‌سازی
                            </label>

                            <select
                                id="filter-sort"
                                name="sort"
                                class="filter-select">

                                <option value="">
                                    پیش‌فرض
                                </option>

                                <option
                                    value="price_asc"
                                    <?= $currentSort ===
                                        'price_asc'
                                        ? 'selected'
                                        : '' ?>>
                                    ارزان‌ترین
                                </option>

                                <option
                                    value="price_desc"
                                    <?= $currentSort ===
                                        'price_desc'
                                        ? 'selected'
                                        : '' ?>>
                                    گران‌ترین
                                </option>

                                <option
                                    value="newest"
                                    <?= $currentSort ===
                                        'newest'
                                        ? 'selected'
                                        : '' ?>>
                                    جدیدترین
                                </option>

                            </select>

                        </div>


                        <button
                            type="submit"
                            class="aa-btn aa-btn-primary"
                            style="width:100%;">
                            اعمال فیلتر
                        </button>


                        <a
                            href="<?= $baseUrl ?>/products"
                            class="aa-btn aa-btn-outline"
                            style="
                            width:100%;
                            margin-top:8px;
                        ">
                            حذف فیلترها
                        </a>

                    </form>

                </aside>


                <!-- =================================================
                 Product List
            ================================================== -->

                <section class="products-main">


                    <div class="products-toolbar">

                        <div class="products-count">

                            <?= count($products) ?>

                            محصول نمایش داده می‌شود

                        </div>


                        <form
                            method="GET"
                            action="<?= $baseUrl ?>/products"
                            class="products-search">

                            <?php if (
                                $currentCategory !== ''
                            ): ?>

                                <input
                                    type="hidden"
                                    name="category"
                                    value="<?= htmlspecialchars(
                                                $currentCategory,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>">

                            <?php endif; ?>


                            <?php if (
                                $currentBrand !== ''
                            ): ?>

                                <input
                                    type="hidden"
                                    name="brand"
                                    value="<?= htmlspecialchars(
                                                $currentBrand,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>">

                            <?php endif; ?>

                            <?php if (
                                $currentMinPrice !== ''
                            ): ?>

                                <input
                                    type="hidden"
                                    name="min_price"
                                    value="<?= htmlspecialchars(
                                                $currentMinPrice,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>">

                            <?php endif; ?>

                            <?php if (
                                $currentMaxPrice !== ''
                            ): ?>

                                <input
                                    type="hidden"
                                    name="max_price"
                                    value="<?= htmlspecialchars(
                                                $currentMaxPrice,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>">

                            <?php endif; ?>

                            <?php if (
                                $currentInStock
                            ): ?>

                                <input
                                    type="hidden"
                                    name="in_stock"
                                    value="1">

                            <?php endif; ?>

                            <?php if (
                                $currentSort !== ''
                            ): ?>

                                <input
                                    type="hidden"
                                    name="sort"
                                    value="<?= htmlspecialchars(
                                                $currentSort,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>">

                            <?php endif; ?>


                            <input
                                type="search"
                                name="q"
                                class="filter-input"
                                value="<?= htmlspecialchars(
                                            $currentSearch,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                placeholder="جستجوی سریع محصول...">

                            <button
                                type="submit"
                                class="aa-btn aa-btn-dark">
                                جستجو
                            </button>

                        </form>

                    </div>


                    <?php if ($products !== []): ?>

                        <div class="product-grid">

                            <?php foreach (
                                $products
                                as $product
                            ): ?>

                                <?php

                                $productName = (string) (
                                    $product['name']
                                    ?? $product['title']
                                    ?? 'محصول'
                                );

                                $productSlug = (string) (
                                    $product['slug']
                                    ?? ''
                                );

                                $image = productIndexImage(
                                    $product,
                                    $baseUrl
                                );

                                $price = productIndexPrice(
                                    $product
                                );

                                $originalPrice = (float) (
                                    $product['price']
                                    ?? 0
                                );

                                $stock = (int) (
                                    $product['stock']
                                    ?? 0
                                );

                                $brandName = (string) (
                                    $product['brand_name']
                                    ?? ''
                                );

                                $categoryName = (string) (
                                    $product['category_name']
                                    ?? ''
                                );

                                $hasDiscount =
                                    $originalPrice > 0
                                    && $price > 0
                                    && $price < $originalPrice;

                                $discountPercent =
                                    $hasDiscount
                                    ? (int) round(
                                        (
                                            1
                                            - (
                                                $price
                                                / $originalPrice
                                            )
                                        )
                                            * 100
                                    )
                                    : 0;

                                ?>

                                <article
                                    class="aa-product-card">

                                    <div
                                        class="product-image-wrap">

                                        <?php if (
                                            $hasDiscount
                                        ): ?>

                                            <span
                                                class="product-discount">
                                                <?= $discountPercent ?>٪ تخفیف
                                            </span>

                                        <?php endif; ?>


                                        <?php if (
                                            $image !== null
                                        ): ?>

                                            <img
                                                src="<?= htmlspecialchars(
                                                            $image,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>"
                                                alt="<?= htmlspecialchars(
                                                            $productName,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>"
                                                class="aa-product-image"
                                                loading="lazy"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                                            <div
                                                class="aa-product-placeholder"
                                                style="display:none;">
                                                🎧
                                            </div>

                                        <?php else: ?>

                                            <div
                                                class="aa-product-placeholder">
                                                🎧
                                            </div>

                                        <?php endif; ?>

                                    </div>


                                    <div class="aa-product-body">


                                        <?php if (
                                            $brandName !== ''
                                        ): ?>

                                            <div
                                                class="aa-product-brand">
                                                <?= htmlspecialchars(
                                                    $brandName,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </div>

                                        <?php endif; ?>


                                        <h2
                                            class="aa-product-title">
                                            <?= htmlspecialchars(
                                                $productName,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </h2>


                                        <?php if (
                                            $categoryName !== ''
                                        ): ?>

                                            <p
                                                style="
                                                margin:
                                                    0 0 8px;
                                                color:
                                                    #8a9099;
                                                font-size:
                                                    .75rem;
                                            ">
                                                <?= htmlspecialchars(
                                                    $categoryName,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </p>

                                        <?php endif; ?>


                                        <div
                                            class="aa-product-bottom">

                                            <div class="aa-price">

                                                <?= number_format(
                                                    $price,
                                                    0,
                                                    '.',
                                                    ','
                                                ) ?>

                                                <span>
                                                    تومان
                                                </span>


                                                <?php if (
                                                    $hasDiscount
                                                ): ?>

                                                    <span
                                                        class="product-old-price">
                                                        <?= number_format(
                                                            $originalPrice,
                                                            0,
                                                            '.',
                                                            ','
                                                        ) ?>
                                                    </span>

                                                <?php endif; ?>

                                            </div>

                                        </div>


                                        <?php if (
                                            $stock > 0
                                        ): ?>

                                            <div
                                                style="
                                                margin-top:
                                                    8px;
                                                color:
                                                    #16a34a;
                                                font-size:
                                                    .76rem;
                                                font-weight:
                                                    700;
                                            ">
                                                ✓ موجود در انبار
                                            </div>

                                        <?php else: ?>

                                            <div
                                                style="
                                                margin-top:
                                                    8px;
                                                color:
                                                    #dc2626;
                                                font-size:
                                                    .76rem;
                                                font-weight:
                                                    700;
                                            ">
                                                ✕ ناموجود
                                            </div>

                                        <?php endif; ?>


                                        <?php if (
                                            $productSlug !== ''
                                        ): ?>

                                            <a
                                                href="<?= $baseUrl ?>/products/<?= urlencode(
                                                                                    $productSlug
                                                                                ) ?>"
                                                class="aa-btn aa-btn-dark"
                                                style="
                                                width:100%;
                                                margin-top:15px;
                                            ">
                                                مشاهده محصول
                                            </a>

                                        <?php else: ?>

                                            <a
                                                href="<?= $baseUrl ?>/products"
                                                class="aa-btn aa-btn-dark"
                                                style="
                                                width:100%;
                                                margin-top:15px;
                                            ">
                                                مشاهده محصولات
                                            </a>

                                        <?php endif; ?>

                                    </div>

                                </article>

                            <?php endforeach; ?>

                        </div>

                    <?php else: ?>

                        <div class="empty-products">

                            <div class="empty-products-icon">
                                🔍
                            </div>

                            <h2>
                                محصولی پیدا نشد
                            </h2>

                            <p>
                                با تغییر فیلترها یا عبارت جستجو
                                دوباره امتحان کنید.
                            </p>

                            <a
                                href="<?= $baseUrl ?>/products"
                                class="aa-btn aa-btn-primary">
                                نمایش همه محصولات
                            </a>

                        </div>

                    <?php endif; ?>

                </section>

            </div>

        </div>

    </main>


    <!-- =========================================================
     Footer
========================================================= -->

    <footer class="aa-footer">

        <div class="aa-container">

            <div class="aa-footer-grid">

                <div>

                    <h3>
                        🎧 آروند Audio
                    </h3>

                    <p>
                        فروشگاه تخصصی تجهیزات صوتی و استودیویی
                        برای علاقه‌مندان به صدای باکیفیت.
                    </p>

                </div>


                <div>

                    <h4>
                        دسترسی سریع
                    </h4>

                    <div class="aa-footer-links">

                        <a href="<?= $baseUrl ?>">
                            خانه
                        </a>

                        <a href="<?= $baseUrl ?>/products">
                            محصولات
                        </a>

                        <a href="<?= $baseUrl ?>/cart">
                            سبد خرید
                        </a>

                    </div>

                </div>


                <div>

                    <h4>
                        حساب کاربری
                    </h4>

                    <div class="aa-footer-links">

                        <a href="<?= $baseUrl ?>/login">
                            ورود
                        </a>

                        <a href="<?= $baseUrl ?>/register">
                            ثبت‌نام
                        </a>

                        <a href="<?= $baseUrl ?>/account">
                            حساب کاربری
                        </a>

                    </div>

                </div>

            </div>


            <div class="aa-footer-bottom">

                © 2026 Arvand Audio Store
                — تمامی حقوق محفوظ است.

            </div>

        </div>

    </footer>

</body>

</html>
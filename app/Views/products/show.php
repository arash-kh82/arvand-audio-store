<?php

declare(strict_types=1);

$product = is_array($product ?? null)
    ? $product
    : [];

$baseUrl = rtrim(
    (string) app_config('base_url', ''),
    '/'
);

$productName = (string) (
    $product['name']
    ?? $product['title']
    ?? 'محصول'
);

$productSlug = (string) (
    $product['slug']
    ?? ''
);

$description = trim(
    (string) (
        $product['description']
        ?? ''
    )
);

$price = (float) (
    $product['price']
    ?? 0
);

$discountPrice = $product['discount_price']
    ?? null;

$finalPrice = (
    $discountPrice !== null
    && $discountPrice !== ''
    && (float) $discountPrice > 0
)
    ? (float) $discountPrice
    : $price;

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

$sku = (string) (
    $product['sku']
    ?? ''
);

/*
 * =========================================================
 * Product Images
 * =========================================================
 */

$images = is_array($images ?? null)
    ? $images
    : [];

$galleryImages = [];

foreach ($images as $image) {

    if (
        !isset($image['image'])
        || trim((string) $image['image']) === ''
    ) {
        continue;
    }

    $galleryImages[] = [
        'url' => $baseUrl
            . '/'
            . ltrim(
                (string) $image['image'],
                '/'
            ),

        'alt' => (string) (
            $image['alt_text']
            ?? $productName
        ),
    ];
}

if (
    $galleryImages === []
    && !empty($product['image'])
) {
    $galleryImages[] = [
        'url' => $baseUrl
            . '/'
            . ltrim(
                (string) $product['image'],
                '/'
            ),

        'alt' => $productName,
    ];
}

$hasDiscount =
    $price > 0
    && $finalPrice > 0
    && $finalPrice < $price;

$discountPercent = $hasDiscount
    ? (int) round(
        (
            1
            - (
                $finalPrice / $price
            )
        ) * 100
    )
    : 0;

?>

<!DOCTYPE html>

<html
    lang="fa"
    dir="rtl"
>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars(
            $productName,
            ENT_QUOTES,
            'UTF-8'
        ) ?>
        | آروند Audio
    </title>

    <meta
        name="description"
        content="<?= htmlspecialchars(
            mb_substr(
                $description,
                0,
                160
            ),
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
    >

    <link
        rel="stylesheet"
        href="<?= $baseUrl ?>/assets/css/app.css"
    >

    <style>

        .product-page {
            padding-block: 38px 70px;
        }

        .product-breadcrumb {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 7px;

            margin-bottom: 25px;

            color: #858b95;
            font-size: .78rem;
        }

        .product-breadcrumb a {
            color: #737985;
        }

        .product-breadcrumb a:hover {
            color: #111;
        }

        .product-layout {
            display: grid;
            grid-template-columns:
                minmax(0, 1.05fr)
                minmax(340px, .95fr);

            gap: 45px;
            align-items: start;
        }

        .product-gallery {
            position: sticky;
            top: 100px;
        }

        .product-main-image {
            position: relative;

            width: 100%;
            min-height: 470px;

            display: grid;
            place-items: center;

            overflow: hidden;

            background: #fff;

            border: 1px solid #e6e8eb;
            border-radius: 22px;
        }

        .product-main-image img {
            width: 100%;
            height: 470px;

            object-fit: contain;

            padding: 35px;
        }

        .product-image-placeholder {
            font-size: 8rem;
            opacity: .7;
        }

        .product-discount-badge {
            position: absolute;
            top: 18px;
            right: 18px;

            padding: 6px 12px;

            border-radius: 999px;

            background: #ef4444;
            color: #fff;

            font-size: .75rem;
            font-weight: 800;
        }

        .product-thumbnails {
            display: flex;
            flex-wrap: wrap;

            gap: 10px;

            margin-top: 13px;
        }

        .product-thumbnail {
            width: 72px;
            height: 72px;

            padding: 5px;

            object-fit: contain;

            background: #fff;

            border: 1px solid #e1e4e8;
            border-radius: 10px;

            cursor: pointer;
        }

        .product-thumbnail:hover {
            border-color: #f59e0b;
        }

        .product-thumbnail.active {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, .2);
        }

        .product-info {
            padding-top: 8px;
        }

        .product-brand {
            display: inline-block;

            margin-bottom: 8px;

            color: #a16207;

            font-size: .82rem;
            font-weight: 800;
        }

        .product-title {
            margin: 0 0 12px;

            color: #15171b;

            font-size: clamp(
                1.7rem,
                3vw,
                2.45rem
            );

            line-height: 1.35;
            font-weight: 950;
        }

        .product-category {
            margin-bottom: 22px;

            color: #7b818b;
            font-size: .82rem;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 8px;

            margin-bottom: 25px;

            color: #f59e0b;
            font-size: .95rem;
        }

        .product-rating span {
            color: #858b95;
            font-size: .78rem;
        }

        .product-price-box {
            padding: 22px;

            margin-bottom: 22px;

            background: #f8f9fa;

            border: 1px solid #e8eaed;
            border-radius: 16px;
        }

        .product-price-label {
            margin-bottom: 5px;

            color: #7a8089;

            font-size: .78rem;
        }

        .product-price {
            color: #111;

            font-size: 1.8rem;
            font-weight: 950;
        }

        .product-price-unit {
            margin-right: 5px;

            color: #747a84;

            font-size: .8rem;
            font-weight: 600;
        }

        .product-old-price {
            margin-right: 10px;

            color: #a0a5ad;

            font-size: .9rem;

            text-decoration: line-through;
        }

        .product-stock {
            display: flex;
            align-items: center;
            gap: 8px;

            margin-top: 10px;

            font-size: .78rem;
            font-weight: 700;
        }

        .stock-available {
            color: #16a34a;
        }

        .stock-unavailable {
            color: #dc2626;
        }

        .product-buy-box {
            padding: 22px;

            background: #fff;

            border: 1px solid #e4e7eb;
            border-radius: 16px;

            box-shadow:
                0 8px 30px rgba(0, 0, 0, .05);
        }

        .quantity-label {
            display: block;

            margin-bottom: 9px;

            color: #4c525c;

            font-size: .82rem;
            font-weight: 800;
        }

        .quantity-control {
            display: flex;
            align-items: center;

            width: 145px;

            margin-bottom: 15px;

            border: 1px solid #dfe2e6;
            border-radius: 10px;

            overflow: hidden;
        }

        .quantity-control button {
            width: 40px;
            height: 42px;

            border: 0;

            background: #f5f6f8;
            color: #333;

            font-size: 1.1rem;

            cursor: pointer;
        }

        .quantity-control button:hover {
            background: #eceef1;
        }

        .quantity-control input {
            width: 65px;
            height: 42px;

            border: 0;

            outline: 0;

            text-align: center;

            font-family: inherit;
            font-weight: 700;
        }

        .product-buy-button {
            width: 100%;

            min-height: 50px;

            font-size: .98rem;
        }

        .product-buy-note {
            margin-top: 12px;

            color: #888e97;

            font-size: .72rem;
            text-align: center;
        }

        .product-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;

            gap: 10px;

            margin-top: 20px;
        }

        .product-meta-item {
            padding: 13px;

            background: #fff;

            border: 1px solid #e7e9ed;
            border-radius: 11px;
        }

        .product-meta-item span {
            display: block;

            color: #8a9099;

            font-size: .7rem;
        }

        .product-meta-item strong {
            display: block;

            margin-top: 2px;

            color: #272a30;

            font-size: .8rem;
        }

        .product-description-section {
            margin-top: 60px;

            padding: 30px;

            background: #fff;

            border: 1px solid #e6e8eb;
            border-radius: 18px;
        }

        .product-description-section h2 {
            margin: 0 0 18px;

            font-size: 1.2rem;
            font-weight: 900;
        }

        .product-description {
            color: #606671;

            font-size: .9rem;
            line-height: 2.1;

            white-space: pre-line;
        }

        .product-benefits {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 12px;

            margin-top: 20px;
        }

        .product-benefit {
            padding: 18px;

            background: #f8f9fa;

            border-radius: 12px;

            text-align: center;
        }

        .product-benefit-icon {
            margin-bottom: 5px;

            font-size: 1.5rem;
        }

        .product-benefit strong {
            display: block;

            font-size: .78rem;
        }

        .product-benefit span {
            color: #858b95;

            font-size: .68rem;
        }

        @media (max-width: 992px) {

            .product-layout {
                grid-template-columns: 1fr;
            }

            .product-gallery {
                position: static;
            }

            .product-main-image {
                min-height: 400px;
            }

            .product-main-image img {
                height: 400px;
            }

            .product-benefits {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }

        @media (max-width: 576px) {

            .product-page {
                padding-block: 25px 45px;
            }

            .product-main-image {
                min-height: 310px;
            }

            .product-main-image img {
                height: 310px;
                padding: 20px;
            }

            .product-image-placeholder {
                font-size: 5rem;
            }

            .product-title {
                font-size: 1.55rem;
            }

            .product-meta {
                grid-template-columns: 1fr;
            }

            .product-description-section {
                padding: 20px;
            }

            .product-benefits {
                grid-template-columns: 1fr 1fr;
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
                class="aa-brand"
            >

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
                    aria-label="محصولات"
                >
                    🔍
                </a>

                <a
                    href="<?= $baseUrl ?>/cart"
                    class="aa-icon-btn"
                    aria-label="سبد خرید"
                >
                    🛒
                </a>

            </div>

        </div>

    </div>

</nav>


<!-- =========================================================
     Product
========================================================= -->

<main class="product-page">

    <div class="aa-container">


        <!-- Breadcrumb -->

        <div class="product-breadcrumb">

            <a href="<?= $baseUrl ?>">
                خانه
            </a>

            <span>›</span>

            <a href="<?= $baseUrl ?>/products">
                محصولات
            </a>

            <?php if (
                $categoryName !== ''
            ): ?>

                <span>›</span>

                <span>
                    <?= htmlspecialchars(
                        $categoryName,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </span>

            <?php endif; ?>

            <span>›</span>

            <span>
                <?= htmlspecialchars(
                    $productName,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </span>

        </div>


        <div class="product-layout">


            <!-- =================================================
                 Gallery
            ================================================== -->

            <?php if ($galleryImages !== []): ?>

                <section class="product-gallery">

                    <div
                        class="product-main-image"
                        id="product-main-image"
                    >

                        <?php if (
                            $hasDiscount
                        ): ?>

                            <span
                                class="product-discount-badge"
                            >
                                <?= $discountPercent ?>٪ تخفیف
                            </span>

                        <?php endif; ?>

                        <img
                            id="main-product-image"
                            src="<?= htmlspecialchars(
                                $galleryImages[0]['url'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            alt="<?= htmlspecialchars(
                                $galleryImages[0]['alt'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                    </div>


                    <?php if (count($galleryImages) > 1): ?>

                        <div
                            class="product-thumbnails"
                        >

                            <?php foreach (
                                $galleryImages as $index => $galleryImage
                            ): ?>

                                <img
                                    src="<?= htmlspecialchars(
                                        $galleryImage['url'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    alt="<?= htmlspecialchars(
                                        $galleryImage['alt'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    class="product-thumbnail <?= $index === 0 ? 'active' : '' ?>"
                                    data-full-image="<?= htmlspecialchars(
                                        $galleryImage['url'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    data-alt="<?= htmlspecialchars(
                                        $galleryImage['alt'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                </section>

            <?php endif; ?>


            <!-- =================================================
                 Product Info
            ================================================== -->

            <section class="product-info">


                <?php if (
                    $brandName !== ''
                ): ?>

                    <div class="product-brand">

                        <?= htmlspecialchars(
                            $brandName,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </div>

                <?php endif; ?>


                <h1 class="product-title">

                    <?= htmlspecialchars(
                        $productName,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </h1>


                <?php if (
                    $categoryName !== ''
                ): ?>

                    <div class="product-category">

                        دسته‌بندی:

                        <strong>
                            <?= htmlspecialchars(
                                $categoryName,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </strong>

                    </div>

                <?php endif; ?>


                <div class="product-rating">

                    ★★★★★

                    <span>
                        محصول تخصصی تجهیزات صوتی
                    </span>

                </div>


                <!-- Price -->

                <div class="product-price-box">

                    <div class="product-price-label">
                        قیمت محصول
                    </div>

                    <div>

                        <span class="product-price">

                            <?= number_format(
                                $finalPrice,
                                0,
                                '.',
                                ','
                            ) ?>

                        </span>

                        <span class="product-price-unit">
                            تومان
                        </span>


                        <?php if (
                            $hasDiscount
                        ): ?>

                            <span
                                class="product-old-price"
                            >
                                <?= number_format(
                                    $price,
                                    0,
                                    '.',
                                    ','
                                ) ?>
                            </span>

                        <?php endif; ?>

                    </div>


                    <?php if (
                        $stock > 0
                    ): ?>

                        <div
                            class="
                                product-stock
                                stock-available
                            "
                        >
                            ✓ موجود در انبار

                            <?php if (
                                $stock <= 5
                            ): ?>

                                — فقط
                                <?= $stock ?>
                                عدد باقی مانده

                            <?php endif; ?>

                        </div>

                    <?php else: ?>

                        <div
                            class="
                                product-stock
                                stock-unavailable
                            "
                        >
                            ✕ این محصول در حال حاضر
                            ناموجود است
                        </div>

                    <?php endif; ?>

                </div>


                <!-- Buy -->

                <div class="product-buy-box">

                    <?php if (
                        $stock > 0
                    ): ?>

                        <form
                            method="POST"
                            action="<?= $baseUrl ?>/cart/add"
                        >

                            <?php
                            /*
                             * If your project already uses a
                             * CSRF field helper, keep this field.
                             */
                            ?>

                            <?php if (
                                class_exists(
                                    'App\Core\Csrf'
                                )
                            ): ?>

                                <?= App\Core\Csrf::field() ?>

                            <?php endif; ?>


                            <input
                                type="hidden"
                                name="product_id"
                                value="<?= (int) (
                                    $product['id']
                                    ?? 0
                                ) ?>"
                            >


                            <label
                                class="quantity-label"
                                for="product-quantity"
                            >
                                تعداد
                            </label>


                            <div
                                class="quantity-control"
                            >

                                <button
                                    type="button"
                                    id="quantity-minus"
                                >
                                    −
                                </button>

                                <input
                                    id="product-quantity"
                                    type="number"
                                    name="quantity"
                                    value="1"
                                    min="1"
                                    max="<?= $stock ?>"
                                >

                                <button
                                    type="button"
                                    id="quantity-plus"
                                >
                                    +
                                </button>

                            </div>


                            <button
                                type="submit"
                                class="
                                    aa-btn
                                    aa-btn-primary
                                    product-buy-button
                                "
                            >
                                🛒 افزودن به سبد خرید
                            </button>


                            <div class="product-buy-note">

                                امکان پرداخت امن و پیگیری سفارش
                                از طریق حساب کاربری

                            </div>

                        </form>

                    <?php else: ?>

                        <button
                            type="button"
                            class="
                                aa-btn
                                product-buy-button
                            "
                            disabled
                            style="
                                background:#e5e7eb;
                                color:#777;
                                cursor:not-allowed;
                            "
                        >
                            محصول ناموجود است
                        </button>

                    <?php endif; ?>

                </div>


                <!-- Meta -->

                <div class="product-meta">

                    <div
                        class="product-meta-item"
                    >

                        <span>
                            وضعیت
                        </span>

                        <strong>
                            <?= $stock > 0
                                ? 'موجود'
                                : 'ناموجود' ?>
                        </strong>

                    </div>


                    <div
                        class="product-meta-item"
                    >

                        <span>
                            برند
                        </span>

                        <strong>
                            <?= $brandName !== ''
                                ? htmlspecialchars(
                                    $brandName,
                                    ENT_QUOTES,
                                    'UTF-8'
                                )
                                : '---' ?>
                        </strong>

                    </div>


                    <?php if (
                        $sku !== ''
                    ): ?>

                        <div
                            class="product-meta-item"
                        >

                            <span>
                                کد محصول
                            </span>

                            <strong>
                                <?= htmlspecialchars(
                                    $sku,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </strong>

                        </div>

                    <?php endif; ?>


                    <div
                        class="product-meta-item"
                    >

                        <span>
                            ارسال
                        </span>

                        <strong>
                            ارسال به سراسر کشور
                        </strong>

                    </div>

                </div>

            </section>

        </div>


        <!-- =====================================================
             Description
        ====================================================== -->

        <section
            class="product-description-section"
        >

            <h2>
                درباره این محصول
            </h2>


            <?php if (
                $description !== ''
            ): ?>

                <div
                    class="product-description"
                >
                    <?= nl2br(
                        htmlspecialchars(
                            $description,
                            ENT_QUOTES,
                            'UTF-8'
                        )
                    ) ?>
                </div>

            <?php else: ?>

                <div
                    class="product-description"
                >
                    توضیحاتی برای این محصول ثبت نشده است.
                </div>

            <?php endif; ?>


            <div class="product-benefits">

                <div class="product-benefit">

                    <div
                        class="product-benefit-icon"
                    >
                        🚚
                    </div>

                    <strong>
                        ارسال سریع
                    </strong>

                    <span>
                        ارسال سفارش در سریع‌ترین زمان
                    </span>

                </div>


                <div class="product-benefit">

                    <div
                        class="product-benefit-icon"
                    >
                        🔒
                    </div>

                    <strong>
                        خرید امن
                    </strong>

                    <span>
                        مدیریت امن اطلاعات سفارش
                    </span>

                </div>


                <div class="product-benefit">

                    <div
                        class="product-benefit-icon"
                    >
                        🎧
                    </div>

                    <strong>
                        تخصصی
                    </strong>

                    <span>
                        تمرکز روی تجهیزات صوتی
                    </span>

                </div>


                <div class="product-benefit">

                    <div
                        class="product-benefit-icon"
                    >
                        🤖
                    </div>

                    <strong>
                        Telegram
                    </strong>

                    <span>
                        امکان خرید از ربات تلگرام
                    </span>

                </div>

            </div>

        </section>

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


<!-- =========================================================
     Product JavaScript
========================================================= -->

<script>

(function () {

    const quantityInput =
        document.getElementById(
            'product-quantity'
        );

    const minusButton =
        document.getElementById(
            'quantity-minus'
        );

    const plusButton =
        document.getElementById(
            'quantity-plus'
        );

    if (
        quantityInput
        && minusButton
        && plusButton
    ) {

        const maxQuantity =
            parseInt(
                quantityInput.max || '1',
                10
            );

        minusButton.addEventListener(
            'click',
            function () {

                let value =
                    parseInt(
                        quantityInput.value || '1',
                        10
                    );

                value = Math.max(
                    1,
                    value - 1
                );

                quantityInput.value = value;

            }
        );


        plusButton.addEventListener(
            'click',
            function () {

                let value =
                    parseInt(
                        quantityInput.value || '1',
                        10
                    );

                value = Math.min(
                    maxQuantity,
                    value + 1
                );

                quantityInput.value = value;

            }
        );


        quantityInput.addEventListener(
            'change',
            function () {

                let value =
                    parseInt(
                        quantityInput.value || '1',
                        10
                    );

                if (
                    Number.isNaN(value)
                ) {
                    value = 1;
                }

                value = Math.max(
                    1,
                    Math.min(
                        maxQuantity,
                        value
                    )
                );

                quantityInput.value = value;

            }
        );

    }


    /*
     * Product gallery
     */

    const mainImage =
        document.getElementById(
            'main-product-image'
        );

    const thumbnails =
        document.querySelectorAll(
            '.product-thumbnail'
        );

    if (
        mainImage
        && thumbnails.length
    ) {

        thumbnails.forEach(
            function (thumbnail) {

                thumbnail.addEventListener(
                    'click',
                    function () {

                        const image =
                            thumbnail.dataset.fullImage;

                        const alt =
                            thumbnail.dataset.alt;

                        if (
                            image
                        ) {
                            mainImage.src =
                                image;
                        }

                        if (
                            alt
                        ) {
                            mainImage.alt =
                                alt;
                        }

                        /*
                         * Remove active class from all thumbnails
                         */
                        thumbnails.forEach(
                            function (t) {
                                t.classList.remove(
                                    'active'
                                );
                            }
                        );

                        thumbnail.classList.add(
                            'active'
                        );

                    }
                );

            }
        );

    }

})();

</script>


</body>

</html>
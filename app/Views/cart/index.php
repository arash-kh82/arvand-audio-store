<?php

declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Session;

$success = Session::flash('success');
$error = Session::flash('error');

$items = is_array($items ?? null)
    ? $items
    : [];

$totalQuantity = (int) (
    $totalQuantity ?? 0
);

$itemCount = (int) (
    $itemCount ?? 0
);

$total = (float) (
    $total ?? 0
);

$baseUrl = rtrim(
    (string) app_config('base_url', ''),
    '/'
);

$csrfField = Csrf::field();


/*
 * =========================================================
 * Helpers
 * =========================================================
 */

function cartItemImage(
    array $item,
    string $baseUrl
): ?string {

    $image = trim(
        (string) (
            $item['image']
            ?? $item['image_url']
            ?? $item['thumbnail']
            ?? ''
        )
    );

    if ($image === '') {
        return null;
    }

    if (
        strpos($image, 'http://') === 0
        || strpos($image, 'https://') === 0
    ) {
        return $image;
    }

    return $baseUrl
        . '/'
        . ltrim($image, '/');
}


function cartItemFinalPrice(
    array $item
): float {

    $price = (float) (
        $item['price'] ?? 0
    );

    $discountPrice = $item['discount_price']
        ?? null;

    if (
        $discountPrice !== null
        && $discountPrice !== ''
        && (float) $discountPrice > 0
        && (float) $discountPrice < $price
    ) {
        return (float) $discountPrice;
    }

    return $price;
}


function cartItemHasDiscount(
    array $item
): bool {

    $price = (float) (
        $item['price'] ?? 0
    );

    $discountPrice = $item['discount_price']
        ?? null;

    return (
        $price > 0
        && $discountPrice !== null
        && $discountPrice !== ''
        && (float) $discountPrice > 0
        && (float) $discountPrice < $price
    );
}


function cartItemDiscountPercent(
    array $item
): int {

    if (!cartItemHasDiscount($item)) {
        return 0;
    }

    $price = (float) (
        $item['price'] ?? 0
    );

    $discountPrice = (float) (
        $item['discount_price'] ?? 0
    );

    if ($price <= 0) {
        return 0;
    }

    return (int) round(
        (
            1
            - (
                $discountPrice / $price
            )
        ) * 100
    );
}

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
        سبد خرید | آروند Audio
    </title>

    <meta
        name="description"
        content="سبد خرید فروشگاه آروند Audio"
    >

    <link
        rel="stylesheet"
        href="<?= $baseUrl ?>/assets/css/app.css"
    >

    <style>

        /* =====================================================
           Cart Page
        ===================================================== */

        .cart-page {
            padding-block: 38px 70px;
        }


        .cart-heading {
            display: flex;
            align-items: end;
            justify-content: space-between;

            gap: 20px;

            margin-bottom: 25px;
        }


        .cart-heading h1 {
            margin: 0;

            color: #15171b;

            font-size: 1.9rem;
            font-weight: 950;
        }


        .cart-heading p {
            margin: 5px 0 0;

            color: #7b818b;

            font-size: .84rem;
        }


        /* =====================================================
           Messages
        ===================================================== */

        .cart-message {
            margin-bottom: 18px;

            padding: 14px 17px;

            border-radius: 12px;

            font-size: .82rem;
            font-weight: 700;
        }


        .cart-message-success {
            background: #ecfdf3;

            border: 1px solid #bbf7d0;

            color: #166534;
        }


        .cart-message-error {
            background: #fef2f2;

            border: 1px solid #fecaca;

            color: #991b1b;
        }


        /* =====================================================
           Layout
        ===================================================== */

        .cart-layout {
            display: grid;

            grid-template-columns:
                minmax(0, 1fr)
                330px;

            gap: 25px;

            align-items: start;
        }


        .cart-items {
            min-width: 0;
        }


        /* =====================================================
           Cart Item
        ===================================================== */

        .cart-item {
            display: grid;

            grid-template-columns:
                120px
                minmax(0, 1fr)
                auto;

            gap: 18px;

            align-items: center;

            padding: 17px;

            margin-bottom: 15px;

            background: #fff;

            border: 1px solid #e6e8eb;

            border-radius: 16px;

            transition:
                transform .2s ease,
                box-shadow .2s ease;
        }


        .cart-item:hover {
            transform: translateY(-2px);

            box-shadow:
                0 8px 25px rgba(0, 0, 0, .05);
        }


        /* =====================================================
           Image
        ===================================================== */

        .cart-item-image {
            width: 120px;
            height: 120px;

            display: flex;

            align-items: center;
            justify-content: center;

            overflow: hidden;

            background: #f7f8fa;

            border-radius: 13px;
        }


        .cart-item-image img {
            width: 100%;
            height: 100%;

            padding: 8px;

            object-fit: contain;

            display: block;
        }


        .cart-item-placeholder {
            font-size: 3rem;

            color: #d1d5db;
        }


        /* =====================================================
           Information
        ===================================================== */

        .cart-item-info {
            min-width: 0;
        }


        .cart-item-brand {
            margin-bottom: 4px;

            color: #a16207;

            font-size: .72rem;
            font-weight: 800;
        }


        .cart-item-title {
            display: block;

            margin: 0 0 7px;

            color: #17191d;

            font-size: 1rem;
            font-weight: 900;

            line-height: 1.45;

            text-decoration: none;
        }


        .cart-item-title:hover {
            color: #a16207;
        }


        .cart-item-category {
            margin-bottom: 10px;

            color: #858b95;

            font-size: .72rem;
        }


        .cart-item-price-row {
            display: flex;

            align-items: center;

            flex-wrap: wrap;

            gap: 7px;
        }


        .cart-item-price {
            color: #202329;

            font-size: .95rem;
            font-weight: 900;
        }


        .cart-item-price-unit {
            margin-right: 3px;

            color: #777e87;

            font-size: .68rem;
            font-weight: 600;
        }


        .cart-item-old-price {
            color: #a0a5ad;

            font-size: .7rem;

            text-decoration: line-through;
        }


        .cart-discount {
            display: inline-block;

            padding: 3px 7px;

            border-radius: 999px;

            background: #fef2f2;

            color: #dc2626;

            font-size: .62rem;
            font-weight: 800;
        }


        .cart-stock {
            margin-top: 9px;

            font-size: .7rem;
            font-weight: 700;
        }


        .cart-stock.available {
            color: #16a34a;
        }


        .cart-stock.unavailable {
            color: #dc2626;
        }


        /* =====================================================
           Quantity
        ===================================================== */

        .cart-item-actions {
            display: flex;

            flex-direction: column;

            align-items: stretch;

            gap: 9px;

            min-width: 145px;
        }


        .cart-quantity-form {
            display: flex;

            align-items: center;

            gap: 6px;
        }


        .cart-quantity-input {
            width: 65px;
            height: 40px;

            padding: 5px;

            border: 1px solid #dfe2e6;

            border-radius: 9px;

            background: #fff;

            color: #222;

            text-align: center;

            font-family: inherit;

            font-size: .8rem;
            font-weight: 800;

            outline: none;
        }


        .cart-quantity-input:focus {
            border-color: #f59e0b;

            box-shadow:
                0 0 0 3px rgba(
                    245,
                    158,
                    11,
                    .12
                );
        }


        .cart-update-button {
            flex: 1;

            min-height: 40px;

            padding: 7px 10px;

            border: 0;

            border-radius: 9px;

            background: #f3f4f6;

            color: #333;

            font-family: inherit;

            font-size: .72rem;
            font-weight: 800;

            cursor: pointer;
        }


        .cart-update-button:hover {
            background: #e5e7eb;
        }


        .cart-remove-form {
            margin: 0;
        }


        .cart-remove-button {
            width: 100%;

            min-height: 38px;

            padding: 7px 10px;

            border: 0;

            border-radius: 9px;

            background: #fef2f2;

            color: #dc2626;

            font-family: inherit;

            font-size: .72rem;
            font-weight: 800;

            cursor: pointer;
        }


        .cart-remove-button:hover {
            background: #fee2e2;
        }


        .cart-item-subtotal {
            margin-top: 4px;

            color: #111;

            font-size: .76rem;
            font-weight: 800;

            text-align: center;
        }


        /* =====================================================
           Summary
        ===================================================== */

        .cart-summary {
            position: sticky;

            top: 100px;

            padding: 20px;

            background: #fff;

            border: 1px solid #e5e7eb;

            border-radius: 16px;

            box-shadow:
                0 8px 30px rgba(
                    0,
                    0,
                    0,
                    .04
                );
        }


        .cart-summary-title {
            margin: 0 0 18px;

            color: #17191d;

            font-size: 1.05rem;
            font-weight: 950;
        }


        .cart-summary-row {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 12px;

            margin-bottom: 12px;

            color: #717780;

            font-size: .78rem;
        }


        .cart-summary-row strong {
            color: #25282e;

            font-size: .8rem;
        }


        .cart-summary-total {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 12px;

            padding-top: 17px;

            margin-top: 17px;

            border-top: 1px solid #eceef1;

            color: #222;

            font-size: .84rem;
            font-weight: 900;
        }


        .cart-summary-total strong {
            color: #111;

            font-size: 1.15rem;
        }


        .cart-summary-note {
            margin-top: 12px;

            color: #8a9099;

            font-size: .67rem;

            line-height: 1.8;

            text-align: center;
        }


        .cart-checkout-button {
            width: 100%;

            min-height: 49px;

            margin-top: 17px;

            font-size: .9rem;
        }


        .cart-clear-button {
            width: 100%;

            min-height: 42px;

            margin-top: 9px;

            border: 1px solid #e5e7eb;

            border-radius: 10px;

            background: #fff;

            color: #dc2626;

            font-family: inherit;

            font-size: .76rem;
            font-weight: 800;

            cursor: pointer;
        }


        .cart-clear-button:hover {
            background: #fef2f2;
        }


        /* =====================================================
           Continue Shopping
        ===================================================== */

        .cart-continue {
            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 6px;

            min-height: 42px;

            margin-top: 10px;

            padding-inline: 15px;

            border: 1px solid #e1e4e8;

            border-radius: 10px;

            background: #fff;

            color: #444;

            font-size: .76rem;
            font-weight: 800;

            text-decoration: none;
        }


        .cart-continue:hover {
            background: #f8f9fa;

            color: #111;
        }


        /* =====================================================
           Empty
        ===================================================== */

        .cart-empty {
            padding: 75px 25px;

            background: #fff;

            border: 1px dashed #d9dce1;

            border-radius: 18px;

            text-align: center;
        }


        .cart-empty-icon {
            margin-bottom: 12px;

            font-size: 4rem;
        }


        .cart-empty h2 {
            margin: 0 0 7px;

            color: #202329;

            font-size: 1.2rem;
            font-weight: 900;
        }


        .cart-empty p {
            margin: 0 0 20px;

            color: #7c828c;

            font-size: .82rem;
        }


        /* =====================================================
           Responsive
        ===================================================== */

        @media (max-width: 992px) {

            .cart-layout {
                grid-template-columns: 1fr;
            }


            .cart-summary {
                position: static;
            }

        }


        @media (max-width: 700px) {

            .cart-heading {
                align-items: start;

                flex-direction: column;
            }


            .cart-item {
                grid-template-columns:
                    90px
                    minmax(0, 1fr);

                gap: 14px;
            }


            .cart-item-image {
                width: 90px;
                height: 90px;
            }


            .cart-item-actions {
                grid-column: 1 / -1;

                display: grid;

                grid-template-columns:
                    1fr 1fr;

                min-width: 0;
            }


            .cart-item-subtotal {
                grid-column: 1 / -1;

                text-align: right;
            }

        }


        @media (max-width: 480px) {

            .cart-page {
                padding-block: 25px 45px;
            }


            .cart-heading h1 {
                font-size: 1.55rem;
            }


            .cart-item {
                grid-template-columns:
                    75px
                    minmax(0, 1fr);

                padding: 13px;
            }


            .cart-item-image {
                width: 75px;
                height: 75px;
            }


            .cart-item-title {
                font-size: .88rem;
            }


            .cart-item-actions {
                grid-template-columns: 1fr;
            }


            .cart-empty {
                padding: 55px 18px;
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
     Cart
========================================================= -->

<main class="cart-page">

    <div class="aa-container">


        <!-- Heading -->

        <div class="cart-heading">

            <div>

                <h1>
                    🛒 سبد خرید
                </h1>

                <p>
                    محصولات انتخاب‌شده خود را بررسی و سفارش را تکمیل کنید.
                </p>

            </div>


            <a
                href="<?= $baseUrl ?>/products"
                class="cart-continue"
            >
                ← ادامه خرید
            </a>

        </div>


        <!-- =================================================
             Messages
        ================================================== -->

        <?php if (
            $success !== null
            && trim((string) $success) !== ''
        ): ?>

            <div
                class="
                    cart-message
                    cart-message-success
                "
            >

                ✓

                <?= htmlspecialchars(
                    (string) $success,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>


        <?php if (
            $error !== null
            && trim((string) $error) !== ''
        ): ?>

            <div
                class="
                    cart-message
                    cart-message-error
                "
            >

                ⚠

                <?= htmlspecialchars(
                    (string) $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             Empty Cart
        ================================================== -->

        <?php if ($items === []): ?>

            <section class="cart-empty">

                <div class="cart-empty-icon">
                    🛒
                </div>


                <h2>
                    سبد خرید شما خالی است
                </h2>


                <p>
                    هنوز محصولی به سبد خرید اضافه نکرده‌اید.
                </p>


                <a
                    href="<?= $baseUrl ?>/products"
                    class="
                        aa-btn
                        aa-btn-primary
                    "
                >
                    مشاهده محصولات
                </a>

            </section>


        <?php else: ?>


            <!-- =================================================
                 Cart Layout
            ================================================== -->

            <div class="cart-layout">


                <!-- =============================================
                     Items
                ============================================== -->

                <section class="cart-items">


                    <?php foreach (
                        $items as $item
                    ): ?>

                        <?php

                        $productId = (int) (
                            $item['product_id']
                            ?? 0
                        );

                        $productName = (string) (
                            $item['name']
                            ?? $item['product_name']
                            ?? 'محصول'
                        );

                        $productSlug = (string) (
                            $item['slug']
                            ?? ''
                        );

                        $brandName = (string) (
                            $item['brand_name']
                            ?? ''
                        );

                        $categoryName = (string) (
                            $item['category_name']
                            ?? ''
                        );

                        $quantity = max(
                            1,
                            (int) (
                                $item['quantity']
                                ?? 1
                            )
                        );

                        $stock = max(
                            0,
                            (int) (
                                $item['stock']
                                ?? 0
                            )
                        );

                        $finalPrice =
                            cartItemFinalPrice(
                                $item
                            );

                        $originalPrice =
                            (float) (
                                $item['price']
                                ?? 0
                            );

                        $hasDiscount =
                            cartItemHasDiscount(
                                $item
                            );

                        $discountPercent =
                            cartItemDiscountPercent(
                                $item
                            );

                        $itemSubtotal =
                            $finalPrice
                            * $quantity;

                        $image =
                            cartItemImage(
                                $item,
                                $baseUrl
                            );

                        ?>


                        <article
                            class="cart-item"
                        >


                            <!-- =================================
                                 Image
                            ================================== -->

                            <div
                                class="cart-item-image"
                            >

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
                                        loading="lazy"
                                        onerror="
                                            this.style.display='none';
                                            this.nextElementSibling.style.display='flex';
                                        "
                                    >

                                    <div
                                        class="
                                            cart-item-placeholder
                                        "
                                        style="display:none;"
                                    >
                                        🎧
                                    </div>

                                <?php else: ?>

                                    <div
                                        class="
                                            cart-item-placeholder
                                        "
                                    >
                                        🎧
                                    </div>

                                <?php endif; ?>

                            </div>


                            <!-- =================================
                                 Information
                            ================================== -->

                            <div class="cart-item-info">


                                <?php if (
                                    $brandName !== ''
                                ): ?>

                                    <div
                                        class="
                                            cart-item-brand
                                        "
                                    >
                                        <?= htmlspecialchars(
                                            $brandName,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </div>

                                <?php endif; ?>


                                <?php if (
                                    $productSlug !== ''
                                ): ?>

                                    <a
                                        href="<?= $baseUrl ?>/products/<?= urlencode(
                                            $productSlug
                                        ) ?>"
                                        class="
                                            cart-item-title
                                        "
                                    >
                                        <?= htmlspecialchars(
                                            $productName,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </a>

                                <?php else: ?>

                                    <div
                                        class="
                                            cart-item-title
                                        "
                                    >
                                        <?= htmlspecialchars(
                                            $productName,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </div>

                                <?php endif; ?>


                                <?php if (
                                    $categoryName !== ''
                                ): ?>

                                    <div
                                        class="
                                            cart-item-category
                                        "
                                    >

                                        دسته‌بندی:

                                        <?= htmlspecialchars(
                                            $categoryName,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </div>

                                <?php endif; ?>


                                <div
                                    class="
                                        cart-item-price-row
                                    "
                                >

                                    <span
                                        class="
                                            cart-item-price
                                        "
                                    >
                                        <?= number_format(
                                            $finalPrice,
                                            0,
                                            '.',
                                            ','
                                        ) ?>

                                        <span
                                            class="
                                                cart-item-price-unit
                                            "
                                        >
                                            تومان
                                        </span>
                                    </span>


                                    <?php if (
                                        $hasDiscount
                                    ): ?>

                                        <span
                                            class="
                                                cart-item-old-price
                                            "
                                        >
                                            <?= number_format(
                                                $originalPrice,
                                                0,
                                                '.',
                                                ','
                                            ) ?>

                                            تومان
                                        </span>


                                        <span
                                            class="
                                                cart-discount
                                            "
                                        >
                                            <?= $discountPercent ?>٪ تخفیف
                                        </span>

                                    <?php endif; ?>

                                </div>


                                <?php if (
                                    $stock > 0
                                ): ?>

                                    <div
                                        class="
                                            cart-stock
                                            available
                                        "
                                    >
                                        ✓ موجودی:

                                        <?= $stock ?>

                                        عدد

                                    </div>

                                <?php else: ?>

                                    <div
                                        class="
                                            cart-stock
                                            unavailable
                                        "
                                    >
                                        ✕ این محصول ناموجود شده است
                                    </div>

                                <?php endif; ?>

                            </div>


                            <!-- =================================
                                 Actions
                            ================================== -->

                            <div
                                class="
                                    cart-item-actions
                                "
                            >


                                <?php if (
                                    $stock > 0
                                ): ?>

                                    <form
                                        method="POST"
                                        action="<?= $baseUrl ?>/cart/update"
                                        class="
                                            cart-quantity-form
                                        "
                                    >

                                        <?= $csrfField ?>


                                        <input
                                            type="hidden"
                                            name="product_id"
                                            value="<?= $productId ?>"
                                        >


                                        <input
                                            type="number"
                                            name="quantity"
                                            class="
                                                cart-quantity-input
                                            "
                                            value="<?= min(
                                                $quantity,
                                                $stock
                                            ) ?>"
                                            min="1"
                                            max="<?= $stock ?>"
                                            required
                                            aria-label="تعداد محصول"
                                        >


                                        <button
                                            type="submit"
                                            class="
                                                cart-update-button
                                            "
                                        >
                                            بروزرسانی
                                        </button>

                                    </form>

                                <?php else: ?>

                                    <div
                                        style="
                                            color:#dc2626;
                                            font-size:.7rem;
                                            font-weight:800;
                                            text-align:center;
                                            padding:8px;
                                            background:#fef2f2;
                                            border-radius:9px;
                                        "
                                    >
                                        ناموجود
                                    </div>

                                <?php endif; ?>


                                <form
                                    method="POST"
                                    action="<?= $baseUrl ?>/cart/remove"
                                    class="cart-remove-form"
                                    onsubmit="
                                        return confirm(
                                            'آیا از حذف این محصول از سبد خرید مطمئن هستید؟'
                                        );
                                    "
                                >

                                    <?= $csrfField ?>


                                    <input
                                        type="hidden"
                                        name="product_id"
                                        value="<?= $productId ?>"
                                    >


                                    <button
                                        type="submit"
                                        class="
                                            cart-remove-button
                                        "
                                    >
                                        🗑 حذف از سبد
                                    </button>

                                </form>


                                <div
                                    class="
                                        cart-item-subtotal
                                    "
                                >

                                    مبلغ:

                                    <?= number_format(
                                        $itemSubtotal,
                                        0,
                                        '.',
                                        ','
                                    ) ?>

                                    تومان

                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </section>


                <!-- =============================================
                     Summary
                ============================================== -->

                <aside
                    class="
                        cart-summary
                    "
                >

                    <h2
                        class="
                            cart-summary-title
                        "
                    >
                        خلاصه سبد خرید
                    </h2>


                    <div
                        class="
                            cart-summary-row
                        "
                    >

                        <span>
                            تعداد آیتم‌ها
                        </span>

                        <strong>
                            <?= $itemCount ?>
                        </strong>

                    </div>


                    <div
                        class="
                            cart-summary-row
                        "
                    >

                        <span>
                            تعداد کل کالاها
                        </span>

                        <strong>
                            <?= $totalQuantity ?>
                        </strong>

                    </div>


                    <div
                        class="
                            cart-summary-row
                        "
                    >

                        <span>
                            هزینه ارسال
                        </span>

                        <strong>
                            محاسبه در مرحله بعد
                        </strong>

                    </div>


                    <div
                        class="
                            cart-summary-total
                        "
                    >

                        <span>
                            مبلغ کل
                        </span>

                        <strong>

                            <?= number_format(
                                $total,
                                0,
                                '.',
                                ','
                            ) ?>

                            <small
                                style="
                                    font-size:.65rem;
                                    font-weight:600;
                                    color:#777;
                                "
                            >
                                تومان
                            </small>

                        </strong>

                    </div>


                    <a
                        href="<?= $baseUrl ?>/checkout"
                        class="
                            aa-btn
                            aa-btn-primary
                            cart-checkout-button
                        "
                    >
                        ادامه و ثبت سفارش →
                    </a>


                    <div
                        class="
                            cart-summary-note
                        "
                    >
                        در مرحله بعد می‌توانید آدرس ارسال
                        را انتخاب کرده و سفارش را ثبت کنید.
                    </div>


                    <form
                        method="POST"
                        action="<?= $baseUrl ?>/cart/clear"
                        onsubmit="
                            return confirm(
                                'آیا مطمئن هستید که می‌خواهید کل سبد خرید را خالی کنید؟'
                            );
                        "
                    >

                        <?= $csrfField ?>


                        <button
                            type="submit"
                            class="
                                cart-clear-button
                            "
                        >
                            🗑 خالی کردن کل سبد
                        </button>

                    </form>


                    <a
                        href="<?= $baseUrl ?>/products"
                        class="
                            cart-continue
                        "
                        style="width:100%;"
                    >
                        ← بازگشت به محصولات
                    </a>

                </aside>

            </div>

        <?php endif; ?>

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
<?php

declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Session;

/*
 * =========================================================
 * Data
 * =========================================================
 */

$items = is_array($items ?? null)
    ? $items
    : [];

$addresses = is_array($addresses ?? null)
    ? $addresses
    : [];

$totalQuantity = (int) ($totalQuantity ?? 0);
$itemCount = (int) ($itemCount ?? 0);
$total = (float) ($total ?? 0);

$success = Session::flash('success');
$error = Session::flash('error');

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

function checkoutProductImage(
    array $item,
    string $baseUrl
): ?string {

    $image = trim(
        (string) (
            $item['image']
            ?? $item['product_image']
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

    return $baseUrl . '/' . ltrim($image, '/');
}

function checkoutProductPrice(array $item): float
{
    $discountPrice =
        $item['discount_price']
        ?? null;

    if (
        $discountPrice !== null
        && $discountPrice !== ''
        && (float) $discountPrice > 0
    ) {
        return (float) $discountPrice;
    }

    return (float) (
        $item['price']
        ?? 0
    );
}

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
        <?= htmlspecialchars(
            (string) (
                $title
                ?? 'تکمیل سفارش'
            ),
            ENT_QUOTES,
            'UTF-8'
        ) ?>
        | آروند Audio
    </title>

    <link
        rel="stylesheet"
        href="<?= $baseUrl ?>/assets/css/app.css">

    <style>
        .checkout-page {
            padding-block: 40px 70px;
        }

        .checkout-header {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 20px;

            margin-bottom: 25px;
        }

        .checkout-header h1 {
            margin: 0;

            color: #15171b;

            font-size: 1.8rem;
            font-weight: 950;
        }

        .checkout-header p {
            margin: 7px 0 0;

            color: #7b818b;

            font-size: .84rem;
        }

        .checkout-layout {
            display: grid;

            grid-template-columns:
                minmax(0, 1fr) 340px;

            gap: 25px;

            align-items: start;
        }

        .checkout-card {
            background: #fff;

            border: 1px solid #e5e8ec;
            border-radius: 16px;

            overflow: hidden;
        }

        .checkout-card+.checkout-card {
            margin-top: 20px;
        }

        .checkout-card-header {
            padding: 18px 22px;

            border-bottom: 1px solid #eceef1;

            background: #fafbfc;
        }

        .checkout-card-header h2 {
            margin: 0;

            color: #202329;

            font-size: 1rem;
            font-weight: 900;
        }

        .checkout-card-body {
            padding: 22px;
        }

        /*
     * =====================================================
     * Messages
     * =====================================================
     */

        .checkout-message {
            margin-bottom: 20px;

            padding: 13px 16px;

            border-radius: 11px;

            font-size: .82rem;
            line-height: 1.8;
        }

        .checkout-message-success {
            background: #ecfdf3;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .checkout-message-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /*
     * =====================================================
     * Products
     * =====================================================
     */

        .checkout-product {
            display: grid;

            grid-template-columns: 78px minmax(0, 1fr) auto;

            gap: 15px;

            align-items: center;

            padding: 15px 0;

            border-bottom: 1px solid #eceef1;
        }

        .checkout-product:first-child {
            padding-top: 0;
        }

        .checkout-product:last-child {
            padding-bottom: 0;
            border-bottom: 0;
        }

        .checkout-product-image {
            width: 78px;
            height: 78px;

            display: flex;
            align-items: center;
            justify-content: center;

            overflow: hidden;

            background: #f5f6f8;

            border: 1px solid #e5e7eb;
            border-radius: 11px;
        }

        .checkout-product-image img {
            width: 100%;
            height: 100%;

            object-fit: contain;
        }

        .checkout-product-placeholder {
            font-size: 2rem;
            color: #c7cbd1;
        }

        .checkout-product-name {
            margin-bottom: 6px;

            color: #202329;

            font-size: .9rem;
            font-weight: 800;
            line-height: 1.6;
        }

        .checkout-product-name a {
            color: inherit;
            text-decoration: none;
        }

        .checkout-product-name a:hover {
            color: #b45309;
        }

        .checkout-product-meta {
            color: #858b95;

            font-size: .74rem;
            line-height: 1.8;
        }

        .checkout-product-total {
            color: #202329;

            font-size: .9rem;
            font-weight: 900;

            white-space: nowrap;
        }

        /*
     * =====================================================
     * Addresses
     * =====================================================
     */

        .address-option {
            position: relative;

            margin-bottom: 12px;

            border: 1px solid #e1e4e8;
            border-radius: 13px;

            transition:
                border-color .2s,
                box-shadow .2s,
                background .2s;
        }

        .address-option:last-child {
            margin-bottom: 0;
        }

        .address-option:has(input:checked) {
            border-color: #f59e0b;

            background: #fffdf8;

            box-shadow:
                0 0 0 3px rgba(245, 158, 11, .08);
        }

        .address-option label {
            display: block;

            padding: 17px 18px;

            cursor: pointer;
        }

        .address-radio {
            display: flex;

            align-items: flex-start;

            gap: 11px;
        }

        .address-radio input {
            margin-top: 4px;

            accent-color: #f59e0b;
        }

        .address-content {
            min-width: 0;
            flex: 1;
        }

        .address-title {
            margin-bottom: 7px;

            color: #202329;

            font-size: .88rem;
            font-weight: 900;
        }

        .address-line {
            margin-bottom: 4px;

            color: #606671;

            font-size: .76rem;
            line-height: 1.8;
        }

        .address-line:last-child {
            margin-bottom: 0;
        }

        .address-label {
            color: #8a9099;
        }

        .address-empty {
            padding: 25px;

            text-align: center;

            background: #fffbeb;

            border: 1px solid #fde68a;
            border-radius: 12px;
        }

        .address-empty-icon {
            margin-bottom: 8px;

            font-size: 2.5rem;
        }

        .address-empty strong {
            display: block;

            margin-bottom: 5px;

            color: #78350f;

            font-size: .9rem;
        }

        .address-empty p {
            margin: 0 0 15px;

            color: #92400e;

            font-size: .76rem;
        }

        /*
     * =====================================================
     * Summary
     * =====================================================
     */

        .checkout-summary {
            position: sticky;
            top: 100px;
        }

        .summary-row {
            display: flex;

            justify-content: space-between;
            align-items: center;

            gap: 15px;

            margin-bottom: 13px;

            color: #656b75;

            font-size: .8rem;
        }

        .summary-row strong {
            color: #25282e;
        }

        .summary-total {
            display: flex;

            justify-content: space-between;
            align-items: center;

            gap: 15px;

            padding-top: 18px;
            margin-top: 18px;

            border-top: 1px solid #e5e7eb;
        }

        .summary-total span {
            color: #33373e;

            font-size: .86rem;
            font-weight: 800;
        }

        .summary-total strong {
            color: #111;

            font-size: 1.15rem;
            font-weight: 950;

            text-align: left;
        }

        .summary-unit {
            color: #777e88;

            font-size: .68rem;
            font-weight: 500;
        }

        .checkout-submit {
            width: 100%;

            min-height: 50px;

            margin-top: 20px;

            font-size: .92rem;
        }

        .checkout-security {
            margin-top: 12px;

            color: #858b95;

            font-size: .7rem;
            line-height: 1.9;

            text-align: center;
        }

        .checkout-back {
            display: block;

            margin-top: 10px;

            text-align: center;
        }

        /*
     * =====================================================
     * Empty Cart
     * =====================================================
     */

        .checkout-empty {
            padding: 65px 20px;

            background: #fff;

            border: 1px dashed #d9dce1;
            border-radius: 16px;

            text-align: center;
        }

        .checkout-empty-icon {
            margin-bottom: 12px;

            font-size: 3.5rem;
        }

        .checkout-empty h2 {
            margin: 0 0 7px;

            font-size: 1.15rem;
        }

        .checkout-empty p {
            margin: 0 0 20px;

            color: #7c828c;

            font-size: .82rem;
        }

        /*
     * =====================================================
     * Responsive
     * =====================================================
     */

        @media (max-width: 992px) {

            .checkout-layout {
                grid-template-columns: 1fr;
            }

            .checkout-summary {
                position: static;
            }

        }

        @media (max-width: 576px) {

            .checkout-page {
                padding-block: 25px 45px;
            }

            .checkout-header {
                align-items: start;
                flex-direction: column;
            }

            .checkout-header h1 {
                font-size: 1.5rem;
            }

            .checkout-card-body {
                padding: 17px;
            }

            .checkout-product {
                grid-template-columns: 62px minmax(0, 1fr);
            }

            .checkout-product-image {
                width: 62px;
                height: 62px;
            }

            .checkout-product-total {
                grid-column: 2;
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
     Checkout
========================================================= -->

    <main class="checkout-page">

        <div class="aa-container">


            <div class="checkout-header">

                <div>

                    <h1>
                        تکمیل سفارش
                    </h1>

                    <p>
                        محصولات و آدرس ارسال خود را بررسی کنید
                        و سپس سفارش را ثبت کنید.
                    </p>

                </div>

                <a
                    href="<?= $baseUrl ?>/cart"
                    class="aa-btn aa-btn-outline">
                    ← بازگشت به سبد خرید
                </a>

            </div>


            <?php if ($success !== null): ?>

                <div
                    class="
                checkout-message
                checkout-message-success
            ">
                    <?= htmlspecialchars(
                        (string) $success,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </div>

            <?php endif; ?>


            <?php if ($error !== null): ?>

                <div
                    class="
                checkout-message
                checkout-message-error
            ">
                    <?= htmlspecialchars(
                        (string) $error,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </div>

            <?php endif; ?>


            <?php if ($items === []): ?>

                <div class="checkout-empty">

                    <div class="checkout-empty-icon">
                        🛒
                    </div>

                    <h2>
                        سبد خرید شما خالی است.
                    </h2>

                    <p>
                        برای ثبت سفارش ابتدا حداقل یک محصول
                        به سبد خرید اضافه کنید.
                    </p>

                    <a
                        href="<?= $baseUrl ?>/products"
                        class="aa-btn aa-btn-primary">
                        مشاهده محصولات
                    </a>

                </div>

            <?php else: ?>


                <div class="checkout-layout">


                    <!-- =================================================
                 Main
            ================================================== -->

                    <div>


                        <!-- Products -->

                        <section class="checkout-card">

                            <div class="checkout-card-header">

                                <h2>
                                    🛒 محصولات سفارش
                                </h2>

                            </div>


                            <div class="checkout-card-body">

                                <?php foreach (
                                    $items as $item
                                ): ?>

                                    <?php

                                    $productName = (string) (
                                        $item['product_name']
                                        ?? 'محصول'
                                    );

                                    $productId = (int) (
                                        $item['product_id']
                                        ?? 0
                                    );

                                    $quantity = max(
                                        1,
                                        (int) (
                                            $item['quantity']
                                            ?? 1
                                        )
                                    );

                                    $price = checkoutProductPrice(
                                        $item
                                    );

                                    $subtotal =
                                        $price * $quantity;

                                    $image =
                                        checkoutProductImage(
                                            $item,
                                            $baseUrl
                                        );

                                    ?>

                                    <div
                                        class="checkout-product">

                                        <div
                                            class="
                                        checkout-product-image
                                    ">

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
                                            ">

                                                <div
                                                    class="
                                                checkout-product-placeholder
                                            "
                                                    style="display:none;">
                                                    🎧
                                                </div>

                                            <?php else: ?>

                                                <div
                                                    class="
                                                checkout-product-placeholder
                                            ">
                                                    🎧
                                                </div>

                                            <?php endif; ?>

                                        </div>


                                        <div>

                                            <div
                                                class="
                                            checkout-product-name
                                        ">

                                                <?php if (
                                                    $productId > 0
                                                ): ?>

                                                    <a
                                                        href="<?= $baseUrl ?>/products/<?= urlencode(
                                                                                            (string) (
                                                                                                $item['product_slug']
                                                                                                ?? ''
                                                                                            )
                                                                                        ) ?>">
                                                        <?= htmlspecialchars(
                                                            $productName,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>
                                                    </a>

                                                <?php else: ?>

                                                    <?= htmlspecialchars(
                                                        $productName,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>

                                                <?php endif; ?>

                                            </div>


                                            <div
                                                class="
                                            checkout-product-meta
                                        ">

                                                قیمت واحد:

                                                <?= number_format(
                                                    $price,
                                                    0,
                                                    '.',
                                                    ','
                                                ) ?>

                                                تومان

                                                <br>

                                                تعداد:

                                                <?= $quantity ?>

                                            </div>

                                        </div>


                                        <div
                                            class="
                                        checkout-product-total
                                    ">

                                            <?= number_format(
                                                $subtotal,
                                                0,
                                                '.',
                                                ','
                                            ) ?>

                                            تومان

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            </div>

                        </section>


                        <!-- Addresses -->

                        <section
                            class="
                        checkout-card
                    ">

                            <div
                                class="
                            checkout-card-header
                        ">

                                <h2>
                                    📍 آدرس ارسال
                                </h2>

                            </div>


                            <div
                                class="
                            checkout-card-body
                        ">

                                <?php if (
                                    $addresses === []
                                ): ?>

                                    <div
                                        class="
                                    address-empty
                                ">

                                        <div
                                            class="
                                        address-empty-icon
                                    ">
                                            📍
                                        </div>

                                        <strong>
                                            هنوز آدرسی ثبت نکرده‌اید.
                                        </strong>

                                        <p>
                                            ابتدا یک آدرس برای
                                            ارسال سفارش ثبت کنید.
                                        </p>

                                        <a
                                            href="<?= $baseUrl ?>/addresses"
                                            class="
                                        aa-btn
                                        aa-btn-primary
                                    ">
                                            مدیریت آدرس‌ها
                                        </a>

                                    </div>

                                <?php else: ?>


                                    <?php foreach (
                                        $addresses
                                        as $index => $address
                                    ): ?>

                                        <?php

                                        $addressId = (int) (
                                            $address['id']
                                            ?? 0
                                        );

                                        $addressTitle =
                                            (string) (
                                                $address['title']
                                                ?? 'آدرس'
                                            );

                                        $receiverName =
                                            (string) (
                                                $address['receiver_name']
                                                ?? ''
                                            );

                                        $phone =
                                            (string) (
                                                $address['phone']
                                                ?? ''
                                            );

                                        $province =
                                            (string) (
                                                $address['province']
                                                ?? ''
                                            );

                                        $city =
                                            (string) (
                                                $address['city']
                                                ?? ''
                                            );

                                        $addressText =
                                            (string) (
                                                $address['address']
                                                ?? ''
                                            );

                                        $postalCode =
                                            (string) (
                                                $address['postal_code']
                                                ?? ''
                                            );

                                        ?>

                                        <div
                                            class="
                                        address-option
                                    ">

                                            <label
                                                for="address-<?= $addressId ?>">

                                                <div
                                                    class="
                                                address-radio
                                            ">

                                                    <input
                                                        id="address-<?= $addressId ?>"
                                                        type="radio"
                                                        name="address_id"
                                                        value="<?= $addressId ?>"
                                                        form="checkout-form"
                                                        <?= $index === 0
                                                            ? 'checked'
                                                            : '' ?>>


                                                    <div
                                                        class="
                                                    address-content
                                                ">

                                                        <div
                                                            class="
                                                        address-title
                                                    ">
                                                            <?= htmlspecialchars(
                                                                $addressTitle,
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ) ?>
                                                        </div>


                                                        <?php if (
                                                            $receiverName !== ''
                                                        ): ?>

                                                            <div
                                                                class="
                                                            address-line
                                                        ">

                                                                <span
                                                                    class="
                                                                address-label
                                                            ">
                                                                    گیرنده:
                                                                </span>

                                                                <?= htmlspecialchars(
                                                                    $receiverName,
                                                                    ENT_QUOTES,
                                                                    'UTF-8'
                                                                ) ?>

                                                            </div>

                                                        <?php endif; ?>


                                                        <?php if (
                                                            $phone !== ''
                                                        ): ?>

                                                            <div
                                                                class="
                                                            address-line
                                                        ">

                                                                <span
                                                                    class="
                                                                address-label
                                                            ">
                                                                    تلفن:
                                                                </span>

                                                                <?= htmlspecialchars(
                                                                    $phone,
                                                                    ENT_QUOTES,
                                                                    'UTF-8'
                                                                ) ?>

                                                            </div>

                                                        <?php endif; ?>


                                                        <?php if (
                                                            $province !== ''
                                                            || $city !== ''
                                                        ): ?>

                                                            <div
                                                                class="
                                                            address-line
                                                        ">

                                                                <?= htmlspecialchars(
                                                                    $province,
                                                                    ENT_QUOTES,
                                                                    'UTF-8'
                                                                ) ?>

                                                                <?php if (
                                                                    $province !== ''
                                                                    && $city !== ''
                                                                ): ?>

                                                                    -

                                                                <?php endif; ?>

                                                                <?= htmlspecialchars(
                                                                    $city,
                                                                    ENT_QUOTES,
                                                                    'UTF-8'
                                                                ) ?>

                                                            </div>

                                                        <?php endif; ?>


                                                        <?php if (
                                                            $addressText !== ''
                                                        ): ?>

                                                            <div
                                                                class="
                                                            address-line
                                                        ">

                                                                <span
                                                                    class="
                                                                address-label
                                                            ">
                                                                    آدرس:
                                                                </span>

                                                                <?= htmlspecialchars(
                                                                    $addressText,
                                                                    ENT_QUOTES,
                                                                    'UTF-8'
                                                                ) ?>

                                                            </div>

                                                        <?php endif; ?>


                                                        <?php if (
                                                            $postalCode !== ''
                                                        ): ?>

                                                            <div
                                                                class="
                                                            address-line
                                                        ">

                                                                <span
                                                                    class="
                                                                address-label
                                                            ">
                                                                    کد پستی:
                                                                </span>

                                                                <?= htmlspecialchars(
                                                                    $postalCode,
                                                                    ENT_QUOTES,
                                                                    'UTF-8'
                                                                ) ?>

                                                            </div>

                                                        <?php endif; ?>

                                                    </div>

                                                </div>

                                            </label>

                                        </div>

                                    <?php endforeach; ?>


                                    <a
                                        href="<?= $baseUrl ?>/addresses"
                                        class="
                                    aa-btn
                                    aa-btn-outline
                                "
                                        style="margin-top:15px;">
                                        مدیریت آدرس‌ها
                                    </a>

                                <?php endif; ?>

                            </div>

                        </section>

                    </div>


                    <!-- =================================================
                 Summary
            ================================================== -->

                    <aside
                        class="
                    checkout-card
                    checkout-summary
                ">

                        <div
                            class="
                        checkout-card-header
                    ">

                            <h2>
                                📋 خلاصه سفارش
                            </h2>

                        </div>


                        <div
                            class="
                        checkout-card-body
                    ">

                            <div class="summary-row">

                                <span>
                                    تعداد آیتم‌ها
                                </span>

                                <strong>
                                    <?= $itemCount ?>
                                </strong>

                            </div>


                            <div class="summary-row">

                                <span>
                                    تعداد کل کالاها
                                </span>

                                <strong>
                                    <?= $totalQuantity ?>
                                </strong>

                            </div>


                            <div class="summary-row">

                                <span>
                                    هزینه ارسال
                                </span>

                                <strong>
                                    محاسبه هنگام ارسال
                                </strong>

                            </div>


                            <div class="summary-total">

                                <span>
                                    مبلغ نهایی
                                </span>

                                <strong>

                                    <?= number_format(
                                        $total,
                                        0,
                                        '.',
                                        ','
                                    ) ?>

                                    <span
                                        class="summary-unit">
                                        تومان
                                    </span>

                                </strong>

                            </div>


                            <form
                                id="checkout-form"
                                method="POST"
                                action="<?= $baseUrl ?>/checkout">

                                <?= $csrfField ?>


                                <?php if (
                                    $addresses !== []
                                ): ?>

                                    <button
                                        type="submit"
                                        class="
                                    aa-btn
                                    aa-btn-primary
                                    checkout-submit
                                ">
                                        ✓ تأیید و ثبت سفارش
                                    </button>

                                <?php else: ?>

                                    <button
                                        type="button"
                                        class="
                                    aa-btn
                                    checkout-submit
                                "
                                        disabled
                                        style="
                                    background:#e5e7eb;
                                    color:#777;
                                    cursor:not-allowed;
                                ">
                                        ابتدا آدرس ثبت کنید
                                    </button>

                                <?php endif; ?>

                            </form>


                            


                            <a
                                href="<?= $baseUrl ?>/cart"
                                class="
                            aa-btn
                            aa-btn-outline
                            checkout-back
                        ">
                                ← بازگشت به سبد خرید
                            </a>

                        </div>

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
<?php

declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Session;

$success = Session::flash('success');
$error = Session::flash('error');

$items = is_array($items ?? null)
    ? $items
    : [];

$totalQuantity = (int) ($totalQuantity ?? 0);
$itemCount = (int) ($itemCount ?? 0);
$total = (float) ($total ?? 0);

$csrfField = Csrf::field();

$baseUrl = rtrim(
    (string) app_config('base_url', ''),
    '/'
);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars(
            (string) ($title ?? 'سبد خرید'),
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Tahoma, Arial, sans-serif;
            margin: 0;
            padding: 30px 15px;
            background: #f5f5f5;
            color: #222;
        }

        .container {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
        }

        h1 {
            margin: 0 0 25px;
            font-size: 30px;
        }

        /* پیام‌ها */

        .message {
            padding: 14px 18px;
            margin-bottom: 18px;
            border-radius: 8px;
            font-size: 15px;
        }

        .success {
            background: #dff5e3;
            color: #176b2c;
            border: 1px solid #b7e4c0;
        }

        .error {
            background: #fde2e2;
            color: #9b1c1c;
            border: 1px solid #f3b5b5;
        }

        /* سبد خالی */

        .empty {
            background: #fff;
            padding: 60px 25px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        }

        .empty-icon {
            font-size: 55px;
            margin-bottom: 15px;
        }

        .empty h2 {
            margin: 0 0 10px;
        }

        .empty p {
            color: #666;
            margin-bottom: 25px;
        }

        /* آیتم */

        .cart-item {
            background: #fff;
            border-radius: 12px;
            padding: 22px;
            margin-bottom: 15px;
            display: grid;
            grid-template-columns: 1fr auto auto;
            align-items: center;
            gap: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        }

        .product-name {
            font-size: 19px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .product-meta {
            color: #666;
            font-size: 14px;
            margin-top: 6px;
        }

        .price {
            font-weight: bold;
            color: #333;
        }

        .subtotal {
            font-weight: bold;
            color: #111;
        }

        /* تعداد */

        .quantity-form {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .quantity-form input[type="number"] {
            width: 70px;
            height: 42px;
            padding: 8px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        .quantity-form input[type="number"]:focus {
            outline: none;
            border-color: #222;
        }

        /* دکمه‌ها */

        button,
        .btn {
            border: 0;
            padding: 10px 16px;
            border-radius: 7px;
            cursor: pointer;
            font-family: inherit;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }

        button:hover,
        .btn:hover {
            opacity: .9;
        }

        .update {
            background: #222;
            color: #fff;
        }

        .remove {
            background: #c62828;
            color: #fff;
        }

        .continue {
            background: #eee;
            color: #222;
        }

        .checkout {
            background: #176b2c;
            color: #fff;
        }

        /* خلاصه */

        .summary {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            margin-top: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        }

        .summary-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            color: #555;
        }

        .summary-row strong {
            color: #222;
        }

        .total {
            border-top: 1px solid #ddd;
            padding-top: 18px;
            margin-top: 18px;
            font-size: 21px;
            font-weight: bold;
            color: #111;
        }

        /* پایین صفحه */

        .cart-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-top: 20px;
        }

        .right-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .clear-form {
            margin: 0;
        }

        /* موبایل */

        @media (max-width: 750px) {

            body {
                padding: 20px 10px;
            }

            h1 {
                font-size: 25px;
            }

            .cart-item {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .quantity-form {
                flex-wrap: wrap;
            }

            .cart-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .right-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .right-actions .btn {
                text-align: center;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <h1>🛒 سبد خرید</h1>

    <?php if ($success !== null): ?>

        <div class="message success">

            <?= htmlspecialchars(
                (string) $success,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </div>

    <?php endif; ?>

    <?php if ($error !== null): ?>

        <div class="message error">

            <?= htmlspecialchars(
                (string) $error,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </div>

    <?php endif; ?>


    <?php if ($items === []): ?>

        <div class="empty">

            <div class="empty-icon">
                🛒
            </div>

            <h2>
                سبد خرید شما خالی است.
            </h2>

            <p>
                هنوز محصولی به سبد خرید اضافه نکرده‌اید.
            </p>

            <a
                class="btn checkout"
                href="<?= htmlspecialchars(
                    $baseUrl . '/products',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >
                مشاهده محصولات
            </a>

        </div>

    <?php else: ?>


        <?php foreach ($items as $item): ?>

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
                (int) ($item['quantity'] ?? 1)
            );

            $price = (float) (
                $item['price']
                ?? 0
            );

            $stock = max(
                0,
                (int) ($item['stock'] ?? 0)
            );

            $subtotal = $price * $quantity;

            ?>

            <div class="cart-item">

                <div>

                    <div class="product-name">

                        <?= htmlspecialchars(
                            $productName,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </div>

                    <div class="product-meta">

                        قیمت واحد:

                        <span class="price">
                            <?= number_format($price) ?>
                            تومان
                        </span>

                    </div>

                    <div class="product-meta">

                        موجودی:

                        <?= $stock ?>

                        عدد

                    </div>

                    <div class="product-meta">

                        مبلغ این محصول:

                        <span class="subtotal">
                            <?= number_format($subtotal) ?>
                            تومان
                        </span>

                    </div>

                </div>


                <form
                    method="POST"
                    action="<?= htmlspecialchars(
                        $baseUrl . '/cart/update',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    class="quantity-form"
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
                        value="<?= $quantity ?>"
                        min="1"
                        max="<?= max(1, $stock) ?>"
                        required
                    >

                    <button
                        type="submit"
                        class="update"
                    >
                        بروزرسانی
                    </button>

                </form>


                <form
                    method="POST"
                    action="<?= htmlspecialchars(
                        $baseUrl . '/cart/remove',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
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
                        class="remove"
                    >
                        حذف
                    </button>

                </form>

            </div>

        <?php endforeach; ?>


        <div class="summary">

            <div class="summary-title">
                خلاصه سفارش
            </div>

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

            <div class="summary-row total">

                <span>
                    مبلغ کل
                </span>

                <strong>
                    <?= number_format($total) ?>
                    تومان
                </strong>

            </div>

        </div>


        <div class="cart-actions">

            <a
                class="btn continue"
                href="<?= htmlspecialchars(
                    $baseUrl . '/products',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >
                ← ادامه خرید
            </a>


            <div class="right-actions">

                <form
                    method="POST"
                    action="<?= htmlspecialchars(
                        $baseUrl . '/cart/clear',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    class="clear-form"
                    onsubmit="
                        return confirm(
                            'آیا مطمئن هستید که می‌خواهید کل سبد خرید را خالی کنید؟'
                        );
                    "
                >

                    <?= $csrfField ?>

                    <button
                        type="submit"
                        class="remove"
                    >
                        خالی کردن سبد
                    </button>

                </form>


                <a
                    class="btn checkout"
                    href="<?= htmlspecialchars(
                        $baseUrl . '/checkout',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >
                    ادامه و ثبت سفارش →
                </a>

            </div>

        </div>

    <?php endif; ?>

</div>

</body>
</html>
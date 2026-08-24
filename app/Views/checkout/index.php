<?php

declare(strict_types=1);

$items = is_array($items ?? null)
    ? $items
    : [];

$totalQuantity = (int) ($totalQuantity ?? 0);
$itemCount = (int) ($itemCount ?? 0);
$total = (float) ($total ?? 0);

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
            (string) ($title ?? 'تکمیل سفارش'),
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </title>

    <style>

        body {
            font-family: Tahoma, Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 30px;
            color: #222;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .box {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        h1 {
            margin-top: 0;
        }

        .item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .item:last-child {
            border-bottom: 0;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 8px;
        }

        .meta {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }

        .summary {
            margin-top: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .total {
            border-top: 1px solid #ddd;
            padding-top: 15px;
            margin-top: 15px;
            font-size: 20px;
            font-weight: bold;
        }

        .actions {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 25px;
        }

        button {
            border: 0;
            background: #222;
            color: #fff;
            padding: 12px 22px;
            border-radius: 6px;
            cursor: pointer;
            font-family: inherit;
        }

        .back {
            text-decoration: none;
            color: #222;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="box">

        <h1>
            تکمیل سفارش
        </h1>

        <p>
            لطفاً اطلاعات سفارش خود را بررسی کنید
            و در صورت صحت، سفارش را ثبت کنید.
        </p>

    </div>

    <div class="box">

        <h2>
            محصولات سفارش
        </h2>

        <?php foreach ($items as $item): ?>

            <?php
            $productName = (string) (
                $item['product_name']
                ?? 'محصول'
            );

            $quantity = (int) (
                $item['quantity']
                ?? 1
            );

            $price = (float) (
                $item['price']
                ?? 0
            );

            $subtotal = $price * $quantity;
            ?>

            <div class="item">

                <div class="item-name">
                    <?= htmlspecialchars(
                        $productName,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </div>

                <div class="meta">
                    قیمت واحد:
                    <?= number_format($price) ?>
                    تومان
                </div>

                <div class="meta">
                    تعداد:
                    <?= $quantity ?>
                    عدد
                </div>

                <div class="meta">
                    مجموع:
                    <?= number_format($subtotal) ?>
                    تومان
                </div>

            </div>

        <?php endforeach; ?>

        <div class="summary">

            <div class="summary-row">

                <span>
                    تعداد آیتم‌ها:
                </span>

                <strong>
                    <?= $itemCount ?>
                </strong>

            </div>

            <div class="summary-row">

                <span>
                    تعداد کل کالاها:
                </span>

                <strong>
                    <?= $totalQuantity ?>
                </strong>

            </div>

            <div class="summary-row total">

                <span>
                    مبلغ نهایی:
                </span>

                <strong>
                    <?= number_format($total) ?>
                    تومان
                </strong>

            </div>

        </div>

        <div class="actions">

            <form
                method="POST"
                action="<?= htmlspecialchars(
                    app_config('base_url', '')
                    . '/checkout',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >

                <?= $csrfField ?>

                <button type="submit">
                    تأیید و ثبت سفارش
                </button>

            </form>

            <a
                class="back"
                href="<?= htmlspecialchars(
                    app_config('base_url', '')
                    . '/cart',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >
                بازگشت به سبد خرید
            </a>

        </div>

    </div>

</div>

</body>

</html>
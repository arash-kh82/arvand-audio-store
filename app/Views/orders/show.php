<?php

declare(strict_types=1);

$order = is_array($order ?? null)
    ? $order
    : [];

$items = is_array($items ?? null)
    ? $items
    : [];

$address = is_array($address ?? null)
    ? $address
    : null;
    
$orderNumber = (string) (
    $order['order_number'] ?? ''
);

$status = (string) (
    $order['status'] ?? 'pending'
);

$paymentStatus = (string) (
    $order['payment_status'] ?? 'pending'
);

$subtotal = (float) (
    $order['subtotal'] ?? 0
);

$discount = (float) (
    $order['discount'] ?? 0
);

$shippingCost = (float) (
    $order['shipping_cost'] ?? 0
);

$total = (float) (
    $order['total'] ?? 0
);

$statusLabels = [
    'pending' => 'در انتظار پرداخت',
    'paid' => 'پرداخت شده',
    'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده',
    'delivered' => 'تحویل داده شده',
    'cancelled' => 'لغو شده',
];

$paymentStatusLabels = [
    'pending' => 'در انتظار پرداخت',
    'success' => 'پرداخت موفق',
    'failed' => 'پرداخت ناموفق',
];

$statusText = $statusLabels[$status]
    ?? $status;

$paymentStatusText =
    $paymentStatusLabels[$paymentStatus]
    ?? $paymentStatus;
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
            (string) (
                $title
                ?? 'جزئیات سفارش'
            ),
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

        .order-number {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .status {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 6px;
            background: #eee;
            margin-left: 8px;
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

        .success {
            background: #dff5e3;
            color: #176b2c;
        }

        .pending {
            background: #fff4cc;
            color: #7a5c00;
        }

        .failed {
            background: #fde2e2;
            color: #9b1c1c;
        }

        .actions {
            margin-top: 25px;
        }

        .actions a {
            text-decoration: none;
            color: #222;
            margin-left: 20px;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="box">

        <h1>
            جزئیات سفارش
        </h1>

        <div class="order-number">
            شماره سفارش:
            <?= htmlspecialchars(
                $orderNumber,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </div>

        <div>

            وضعیت سفارش:

            <span class="status">
                <?= htmlspecialchars(
                    $statusText,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </span>

        </div>

        <br>

        <div>

            وضعیت پرداخت:

            <span
                class="status
                <?= $paymentStatus === 'success'
                    ? 'success'
                    : (
                        $paymentStatus === 'failed'
                            ? 'failed'
                            : 'pending'
                    ) ?>"
            >
                <?= htmlspecialchars(
                    $paymentStatusText,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </span>

        </div>

    </div>

    <!-- ====== ADDRESS SECTION INSERTED HERE ====== -->
    <?php if ($address !== null): ?>

        <div class="box">

            <h2>
                آدرس ارسال
            </h2>

            <?php if (!empty($address['title'])): ?>

                <div class="order-number">
                    <?= htmlspecialchars(
                        (string) $address['title'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </div>

            <?php endif; ?>

            <div class="meta">
                گیرنده:
                <?= htmlspecialchars(
                    (string) (
                        $address['receiver_name'] ?? ''
                    ),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>

            <div class="meta">
                تلفن:
                <?= htmlspecialchars(
                    (string) (
                        $address['phone'] ?? ''
                    ),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>

            <div class="meta">
                استان:
                <?= htmlspecialchars(
                    (string) (
                        $address['province'] ?? ''
                    ),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>

            <div class="meta">
                شهر:
                <?= htmlspecialchars(
                    (string) (
                        $address['city'] ?? ''
                    ),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>

            <div class="meta">
                آدرس:
                <?= htmlspecialchars(
                    (string) (
                        $address['address'] ?? ''
                    ),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>

            <?php if (!empty($address['postal_code'])): ?>

                <div class="meta">
                    کد پستی:
                    <?= htmlspecialchars(
                        (string) $address['postal_code'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </div>

            <?php endif; ?>

        </div>

    <?php endif; ?>
    <!-- ====== END OF ADDRESS SECTION ====== -->

    <div class="box">

        <h2>
            محصولات سفارش
        </h2>

        <?php if ($items === []): ?>

            <p>
                آیتمی برای این سفارش پیدا نشد.
            </p>

        <?php else: ?>

            <?php foreach ($items as $item): ?>

                <?php
                $productName = (string) (
                    $item['product_name']
                    ?? 'محصول'
                );

                $price = (float) (
                    $item['price']
                    ?? 0
                );

                $quantity = (int) (
                    $item['quantity']
                    ?? 0
                );

                $itemTotal = (float) (
                    $item['total']
                    ?? ($price * $quantity)
                );
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

                        <?= number_format(
                            $price
                        ) ?>

                        تومان

                    </div>

                    <div class="meta">

                        تعداد:

                        <?= $quantity ?>

                        عدد

                    </div>

                    <div class="meta">

                        مجموع:

                        <?= number_format(
                            $itemTotal
                        ) ?>

                        تومان

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

        <div class="summary">

            <div class="summary-row">

                <span>
                    مبلغ کالاها:
                </span>

                <strong>
                    <?= number_format(
                        $subtotal
                    ) ?>

                    تومان
                </strong>

            </div>

            <div class="summary-row">

                <span>
                    تخفیف:
                </span>

                <strong>
                    <?= number_format(
                        $discount
                    ) ?>

                    تومان
                </strong>

            </div>

            <div class="summary-row">

                <span>
                    هزینه ارسال:
                </span>

                <strong>
                    <?= number_format(
                        $shippingCost
                    ) ?>

                    تومان
                </strong>

            </div>

            <div class="summary-row total">

                <span>
                    مبلغ نهایی:
                </span>

                <strong>
                    <?= number_format(
                        $total
                    ) ?>

                    تومان
                </strong>

            </div>

        </div>

        <div class="actions">

            <a
                href="<?= htmlspecialchars(
                    app_config('base_url', '')
                    . '/products',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >
                ← ادامه خرید
            </a>

            <a
                href="<?= htmlspecialchars(
                    app_config('base_url', '')
                    . '/account',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >
                حساب کاربری
            </a>

        </div>

    </div>

</div>

</body>

</html>
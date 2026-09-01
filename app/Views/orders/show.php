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

$csrfField = (string) ($csrfField ?? '');

$e = static fn($value): string =>
htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$baseUrl = rtrim(
    (string) app_config('base_url', ''),
    '/'
);

$orderId = (int) (
    $order['id'] ?? 0
);

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

$statusText = $statusLabels[$status] ?? $status;
$paymentStatusText =
    $paymentStatusLabels[$paymentStatus]
    ?? $paymentStatus;

$statusClass = match ($status) {
    'delivered' => 'status-success',
    'shipped',
    'processing',
    'paid' => 'status-info',
    'cancelled' => 'status-danger',
    default => 'status-pending',
};

$paymentClass = match ($paymentStatus) {
    'success' => 'status-success',
    'failed' => 'status-danger',
    default => 'status-pending',
}; // <- اینجا باید } باشد نه )

$canPay =
    $paymentStatus === 'pending'
    || $paymentStatus === 'failed';

$canCancel =
    $status === 'pending'
    && $paymentStatus !== 'success';

?>

<!doctype html>

<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>
        <?= $e($title ?? 'جزئیات سفارش') ?>
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f5f7fb;
            color: #1f2937;
            font-family: Tahoma, Arial, sans-serif;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .aa-navbar {
            background: #111827;
            color: #fff;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .08);
        }

        .aa-navbar-inner {
            max-width: 1180px;
            margin: 0 auto;
            padding: 15px 20px;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .aa-brand {
            font-size: 20px;
            font-weight: 800;
            white-space: nowrap;
        }

        .aa-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .aa-nav a {
            color: #d1d5db;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 14px;
            transition: .2s;
        }

        .aa-nav a:hover {
            background: #1f2937;
            color: #fff;
        }

        .aa-container {
            max-width: 1050px;
            margin: 0 auto;
            padding: 30px 20px 55px;
        }

        .breadcrumb {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .breadcrumb a:hover {
            color: #111827;
            text-decoration: underline;
        }

        .page-header {
            margin-bottom: 22px;
        }

        .page-header h1 {
            margin: 0;
            font-size: 27px;
        }

        .page-header p {
            margin: 8px 0 0;
            color: #6b7280;
            font-size: 13px;
        }

        .grid {
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(290px, .75fr);
            gap: 22px;
            align-items: start;
        }

        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(15, 23, 42, .05);
            margin-bottom: 22px;
        }

        .card:last-child {
            margin-bottom: 0;
        }

        .card-header {
            padding: 18px 20px;
            border-bottom: 1px solid #eef0f3;
        }

        .card-header h2 {
            margin: 0;
            font-size: 17px;
        }

        .card-body {
            padding: 20px;
        }

        .order-number-box {
            background: #f9fafb;
            border: 1px solid #eef0f3;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 18px;
        }

        .label {
            display: block;
            color: #6b7280;
            font-size: 11px;
            margin-bottom: 6px;
        }

        .order-number {
            font-size: 18px;
            font-weight: 900;
            color: #111827;
        }

        .status-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;

            padding: 12px 0;
            border-bottom: 1px solid #f0f1f3;

            font-size: 13px;
        }

        .status-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .status-row:first-child {
            padding-top: 0;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;

            padding: 6px 10px;
            border-radius: 999px;

            font-size: 11px;
            font-weight: 800;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-success {
            background: #d1fae5;
            color: #065f46;
        }

        .status-info {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;

            padding: 17px 0;
            border-bottom: 1px solid #eef0f3;
        }

        .item:last-child {
            border-bottom: 0;
        }

        .item-main {
            min-width: 0;
        }

        .item-name {
            font-size: 15px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 7px;
        }

        .item-meta {
            color: #6b7280;
            font-size: 12px;
            line-height: 1.8;
        }

        .item-total {
            white-space: nowrap;
            font-size: 14px;
            font-weight: 900;
            color: #111827;
        }

        .empty-items {
            text-align: center;
            padding: 25px 10px;
            color: #6b7280;
            font-size: 13px;
        }

        .summary-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;

            padding: 10px 0;

            font-size: 13px;
        }

        .summary-row span {
            color: #6b7280;
        }

        .summary-row strong {
            color: #111827;
        }

        .summary-total {
            border-top: 1px solid #e5e7eb;
            margin-top: 8px;
            padding-top: 17px;

            font-size: 16px;
        }

        .summary-total span,
        .summary-total strong {
            color: #111827;
            font-weight: 900;
        }

        .address-box {
            background: #f9fafb;
            border: 1px solid #eef0f3;
            border-radius: 12px;
            padding: 16px;
        }

        .address-title {
            font-size: 15px;
            font-weight: 900;
            margin-bottom: 13px;
        }

        .address-line {
            color: #4b5563;
            font-size: 12px;
            line-height: 2;
        }

        .address-line strong {
            color: #111827;
        }

        .payment-notice {
            padding: 14px;
            border-radius: 11px;
            margin-bottom: 15px;

            font-size: 12px;
            line-height: 2;
        }

        .notice-warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
        }

        .notice-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .notice-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn {
            width: 100%;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding: 12px 15px;

            border-radius: 10px;
            border: 0;

            font-family: inherit;
            font-size: 13px;
            font-weight: 800;

            cursor: pointer;
            transition: .2s;
        }

        .btn-primary {
            background: #111827;
            color: #fff;
        }

        .btn-primary:hover {
            background: #1f2937;
        }

        .btn-success {
            background: #059669;
            color: #fff;
        }

        .btn-success:hover {
            background: #047857;
        }

        .btn-danger {
            background: #fff;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .btn-danger:hover {
            background: #fef2f2;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .bottom-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 20px;
        }

        .payment-id {
            color: #047857;
            font-size: 12px;
            margin-top: 7px;
        }

        @media (max-width: 800px) {

            .grid {
                grid-template-columns: 1fr;
            }

        }

        @media (max-width: 600px) {

            .aa-navbar-inner {
                align-items: flex-start;
                flex-direction: column;
            }

            .aa-nav {
                width: 100%;
            }

            .aa-container {
                padding: 22px 14px 40px;
            }

            .page-header h1 {
                font-size: 23px;
            }

            .item {
                align-items: flex-start;
                flex-direction: column;
                gap: 8px;
            }

            .item-total {
                width: 100%;
            }

            .bottom-actions {
                grid-template-columns: 1fr;
            }

        }
    </style>

</head>

<body>

    <header class="aa-navbar">

        <div class="aa-navbar-inner">

            <a
                class="aa-brand"
                href="<?= $e($baseUrl . '/') ?>">
                آروند Audio
            </a>

            <nav class="aa-nav">

                <a href="<?= $e($baseUrl . '/') ?>">
                    خانه
                </a>

                <a href="<?= $e($baseUrl . '/products') ?>">
                    محصولات
                </a>

                <a href="<?= $e($baseUrl . '/cart') ?>">
                    🛒 سبد خرید
                </a>

                <a href="<?= $e($baseUrl . '/account') ?>">
                    حساب کاربری
                </a>

            </nav>

        </div>

    </header>

    <main class="aa-container">

        <div class="breadcrumb">

            <a href="<?= $e($baseUrl . '/') ?>">
                خانه
            </a>

            <span> › </span>

            <a href="<?= $e($baseUrl . '/account') ?>">
                حساب کاربری
            </a>

            <span> › </span>

            <span>
                جزئیات سفارش
            </span>

        </div>


        <div class="page-header">

            <h1>
                جزئیات سفارش
            </h1>

            <p>
                اطلاعات کامل سفارش، محصولات و وضعیت پرداخت
            </p>

        </div>


        <div class="grid">


            <!-- Main column -->

            <div>


                <!-- Order status -->

                <section class="card">

                    <div class="card-header">

                        <h2>
                            وضعیت سفارش
                        </h2>

                    </div>

                    <div class="card-body">


                        <div class="order-number-box">

                            <span class="label">
                                شماره سفارش
                            </span>

                            <div class="order-number">

                                <?= $e(
                                    $orderNumber !== ''
                                        ? $orderNumber
                                        : '---'
                                ) ?>

                            </div>

                        </div>


                        <div class="status-row">

                            <span>
                                وضعیت سفارش
                            </span>

                            <span class="status <?= $e($statusClass) ?>">

                                <?= $e($statusText) ?>

                            </span>

                        </div>


                        <div class="status-row">

                            <span>
                                وضعیت پرداخت
                            </span>

                            <span class="status <?= $e($paymentClass) ?>">

                                <?= $e($paymentStatusText) ?>

                            </span>

                        </div>

                    </div>

                </section>


                <!-- Address -->

                <?php if ($address !== null): ?>

                    <section class="card">

                        <div class="card-header">

                            <h2>
                                آدرس ارسال
                            </h2>

                        </div>

                        <div class="card-body">

                            <div class="address-box">

                                <?php if (!empty($address['title'])): ?>

                                    <div class="address-title">

                                        <?= $e(
                                            $address['title']
                                        ) ?>

                                    </div>

                                <?php endif; ?>


                                <div class="address-line">

                                    <strong>
                                        گیرنده:
                                    </strong>

                                    <?= $e(
                                        $address['receiver_name']
                                            ?? '-'
                                    ) ?>

                                </div>


                                <div class="address-line">

                                    <strong>
                                        تلفن:
                                    </strong>

                                    <?= $e(
                                        $address['phone']
                                            ?? '-'
                                    ) ?>

                                </div>


                                <div class="address-line">

                                    <strong>
                                        استان:
                                    </strong>

                                    <?= $e(
                                        $address['province']
                                            ?? '-'
                                    ) ?>

                                </div>


                                <div class="address-line">

                                    <strong>
                                        شهر:
                                    </strong>

                                    <?= $e(
                                        $address['city']
                                            ?? '-'
                                    ) ?>

                                </div>


                                <div class="address-line">

                                    <strong>
                                        آدرس:
                                    </strong>

                                    <?= nl2br(
                                        $e(
                                            $address['address']
                                                ?? '-'
                                        )
                                    ) ?>

                                </div>


                                <?php if (
                                    !empty($address['postal_code'])
                                ): ?>

                                    <div class="address-line">

                                        <strong>
                                            کد پستی:
                                        </strong>

                                        <?= $e(
                                            $address['postal_code']
                                        ) ?>

                                    </div>

                                <?php endif; ?>

                            </div>

                        </div>

                    </section>

                <?php endif; ?>


                <!-- Products -->

                <section class="card">

                    <div class="card-header">

                        <h2>
                            محصولات سفارش
                        </h2>

                    </div>

                    <div class="card-body">


                        <?php if ($items === []): ?>

                            <div class="empty-items">

                                آیتمی برای این سفارش پیدا نشد.

                            </div>

                        <?php else: ?>


                            <?php foreach ($items as $item): ?>

                                <?php

                                $productName = (string) (
                                    $item['product_name']
                                    ?? $item['name']
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

                                $itemTotal = array_key_exists(
                                    'total',
                                    $item
                                )
                                    ? (float) $item['total']
                                    : ($price * $quantity);

                                ?>

                                <div class="item">


                                    <div class="item-main">

                                        <div class="item-name">

                                            <?= $e(
                                                $productName
                                            ) ?>

                                        </div>


                                        <div class="item-meta">

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
                                            عدد

                                        </div>

                                    </div>


                                    <div class="item-total">

                                        <?= number_format(
                                            $itemTotal,
                                            0,
                                            '.',
                                            ','
                                        ) ?>

                                        تومان

                                    </div>


                                </div>

                            <?php endforeach; ?>


                        <?php endif; ?>


                    </div>

                </section>


            </div>


            <!-- Sidebar -->

            <aside>


                <!-- Summary -->

                <section class="card">

                    <div class="card-header">

                        <h2>
                            خلاصه مالی سفارش
                        </h2>

                    </div>


                    <div class="card-body">


                        <div class="summary-row">

                            <span>
                                مبلغ کالاها
                            </span>

                            <strong>
                                <?= number_format(
                                    $subtotal,
                                    0,
                                    '.',
                                    ','
                                ) ?>
                                تومان
                            </strong>

                        </div>


                        <div class="summary-row">

                            <span>
                                تخفیف
                            </span>

                            <strong>
                                <?= number_format(
                                    $discount,
                                    0,
                                    '.',
                                    ','
                                ) ?>
                                تومان
                            </strong>

                        </div>


                        <div class="summary-row">

                            <span>
                                هزینه ارسال
                            </span>

                            <strong>
                                <?= number_format(
                                    $shippingCost,
                                    0,
                                    '.',
                                    ','
                                ) ?>
                                تومان
                            </strong>

                        </div>


                        <div class="summary-row summary-total">

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
                                تومان
                            </strong>

                        </div>

                    </div>

                </section>


                <!-- Payment -->

                <section class="card">

                    <div class="card-header">

                        <h2>
                            پرداخت
                        </h2>

                    </div>


                    <div class="card-body">


                        <?php if ($paymentStatus === 'success'): ?>

                            <div class="payment-notice notice-success">

                                <strong>
                                    ✓ پرداخت موفق
                                </strong>

                                <br>

                                پرداخت این سفارش با موفقیت انجام شده
                                و سفارش در حال پردازش است.

                            </div>


                        <?php elseif ($paymentStatus === 'failed'): ?>

                            <div class="payment-notice notice-danger">

                                <strong>
                                    ✕ پرداخت ناموفق
                                </strong>

                                <br>

                                پرداخت قبلی موفق نبوده است.
                                می‌توانید دوباره برای پرداخت اقدام کنید.

                            </div>


                        <?php else: ?>

                            <div class="payment-notice notice-warning">

                                <strong>
                                    در انتظار پرداخت
                                </strong>

                                <br>

                                این سفارش هنوز پرداخت نشده است.

                            </div>

                        <?php endif; ?>


                        <?php if ($canPay): ?>

                            <div class="actions">

                                <a
                                    class="btn btn-success"
                                    href="<?= $e(
                                                $baseUrl
                                                    . '/payment/'
                                                    . $orderId
                                            ) ?>">

                                    <?= $paymentStatus === 'failed'
                                        ? 'تلاش مجدد پرداخت'
                                        : 'پرداخت سفارش'
                                    ?>

                                </a>

                            </div>

                        <?php endif; ?>


                        <?php if ($canCancel): ?>

                            <form
                                method="POST"
                                action="<?= $e(
                                            $baseUrl
                                                . '/orders/'
                                                . $orderId
                                                . '/cancel'
                                        ) ?>"
                                onsubmit="return confirm('آیا از لغو و حذف کامل این سفارش مطمئن هستید؟ موجودی کالاهای سفارش نیز برگردانده خواهد شد.');"
                                style="margin-top: 10px;">

                                <?= $csrfField ?>

                                <button
                                    type="submit"
                                    class="btn btn-danger">
                                    لغو و حذف سفارش
                                </button>

                            </form>

                        <?php endif; ?>


                    </div>

                </section>


            </aside>

        </div>


    </main>

</body>

</html>
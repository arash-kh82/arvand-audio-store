<?php

declare(strict_types=1);

use App\Core\Session;

$e = static fn($value): string =>
htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$order = is_array($order ?? null)
    ? $order
    : [];

$payment = is_array($payment ?? null)
    ? $payment
    : [];

$orderNumber = (string) (
    $order['order_number'] ?? ''
);

$total = (float) (
    $order['total'] ?? 0
);

$paymentId = (int) (
    $payment['id'] ?? 0
);

$paymentStatus = (string) (
    $payment['status'] ?? 'pending'
);

$baseUrl = rtrim(
    (string) app_config('base_url', ''),
    '/'
);

$orderId = (int) (
    $order['id'] ?? 0
);

$success = Session::flash('success');
$error = Session::flash('error');

?>

<!doctype html>

<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>
        <?= $e($title ?? 'پرداخت سفارش') ?>
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
            max-width: 900px;
            margin: 0 auto;
            padding: 35px 20px 55px;
        }

        .aa-breadcrumb {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .aa-breadcrumb a:hover {
            color: #111827;
            text-decoration: underline;
        }

        .alert {
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 18px;
            font-size: 14px;
            line-height: 1.9;
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .payment-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 12px 35px rgba(15, 23, 42, .07);
        }

        .payment-header {
            padding: 25px;
            border-bottom: 1px solid #eef0f3;
            text-align: center;
        }

        .payment-icon {
            width: 68px;
            height: 68px;
            margin: 0 auto 15px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 50%;
            background: #f3f4f6;

            font-size: 30px;
        }

        .payment-header h1 {
            margin: 0;
            font-size: 25px;
        }

        .payment-header p {
            margin: 8px 0 0;
            color: #6b7280;
            font-size: 13px;
        }

        .payment-body {
            padding: 25px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 25px;
        }

        .info-box {
            background: #f9fafb;
            border: 1px solid #eef0f3;
            border-radius: 12px;
            padding: 15px;
        }

        .info-label {
            color: #6b7280;
            font-size: 12px;
            margin-bottom: 7px;
        }

        .info-value {
            color: #111827;
            font-size: 15px;
            font-weight: 800;
        }

        .total-box {
            background: #111827;
            color: #fff;
            border-radius: 14px;
            padding: 20px;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;

            margin-bottom: 25px;
        }

        .total-label {
            color: #d1d5db;
            font-size: 13px;
        }

        .total-value {
            font-size: 23px;
            font-weight: 900;
            white-space: nowrap;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 7px;

            padding: 7px 12px;
            border-radius: 999px;

            font-size: 12px;
            font-weight: 700;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-success {
            background: #d1fae5;
            color: #065f46;
        }

        .status-failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .gateway-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;

            border-radius: 12px;
            padding: 16px;

            margin-bottom: 20px;

            font-size: 13px;
            line-height: 2;
        }

        .gateway-title {
            font-weight: 800;
            margin-bottom: 4px;
        }

        .payment-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn {
            width: 100%;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            border: 0;
            border-radius: 11px;

            padding: 13px 16px;

            font-family: inherit;
            font-size: 14px;
            font-weight: 800;

            cursor: pointer;
            transition: .2s;
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

        .btn-primary {
            background: #111827;
            color: #fff;
        }

        .btn-primary:hover {
            background: #1f2937;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .bottom-links {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;

            margin-top: 22px;
        }

        .transaction-box {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 12px;

            padding: 16px;
            margin-bottom: 20px;

            color: #065f46;
            font-size: 13px;
            line-height: 2;
        }

        .transaction-box strong {
            display: block;
            margin-bottom: 3px;
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

            .payment-header,
            .payment-body {
                padding: 20px;
            }

            .info-grid,
            .bottom-links {
                grid-template-columns: 1fr;
            }

            .total-box {
                align-items: flex-start;
                flex-direction: column;
            }

            .total-value {
                font-size: 21px;
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

        <div class="aa-breadcrumb">

            <a href="<?= $e($baseUrl . '/') ?>">
                خانه
            </a>

            <span> › </span>

            <a href="<?= $e($baseUrl . '/account') ?>">
                حساب کاربری
            </a>

            <span> › </span>

            <span>
                پرداخت سفارش
            </span>

        </div>


        <?php if ($success): ?>

            <div class="alert alert-success">
                <?= $e($success) ?>
            </div>

        <?php endif; ?>


        <?php if ($error): ?>

            <div class="alert alert-danger">
                <?= $e($error) ?>
            </div>

        <?php endif; ?>


        <section class="payment-card">


            <div class="payment-header">

                <div class="payment-icon">

                    <?php if ($paymentStatus === 'success'): ?>

                        ✓

                    <?php elseif ($paymentStatus === 'failed'): ?>

                        !

                    <?php else: ?>

                        💳

                    <?php endif; ?>

                </div>


                <h1>
                    پرداخت سفارش
                </h1>


                <p>
                    تکمیل فرآیند خرید در فروشگاه آروند Audio
                </p>

            </div>


            <div class="payment-body">


                <div class="info-grid">


                    <div class="info-box">

                        <div class="info-label">
                            شماره سفارش
                        </div>

                        <div class="info-value">
                            <?= $e($orderNumber !== '' ? $orderNumber : '---') ?>
                        </div>

                    </div>


                    <div class="info-box">

                        <div class="info-label">
                            وضعیت پرداخت
                        </div>

                        <div>

                            <?php if ($paymentStatus === 'pending'): ?>

                                <span class="status status-pending">
                                    ● در انتظار پرداخت
                                </span>

                            <?php elseif ($paymentStatus === 'success'): ?>

                                <span class="status status-success">
                                    ✓ پرداخت موفق
                                </span>

                            <?php else: ?>

                                <span class="status status-failed">
                                    ✕ پرداخت ناموفق
                                </span>

                            <?php endif; ?>

                        </div>

                    </div>


                </div>


                <div class="total-box">

                    <div class="total-label">
                        مبلغ قابل پرداخت
                    </div>

                    <div class="total-value">
                        <?= number_format($total, 0, '.', ',') ?>
                        تومان
                    </div>

                </div>


                <?php if ($paymentStatus === 'pending'): ?>


                    <div class="gateway-box">

                        <div class="gateway-title">
                            درگاه پرداخت آزمایشی
                        </div>

                        این بخش برای شبیه‌سازی فرآیند پرداخت
                        در پروژه دانشگاهی طراحی شده است.

                        <br>

                        با انتخاب یکی از گزینه‌های زیر می‌توانید
                        نتیجه پرداخت را شبیه‌سازی کنید.

                    </div>


                    <div class="payment-actions">


                        <form
                            method="POST"
                            action="<?= $e(
                                        $baseUrl
                                            . '/payment/'
                                            . $orderId
                                            . '/success'
                                    ) ?>">

                            <?= $csrfField ?? '' ?>

                            <button
                                type="submit"
                                class="btn btn-success">
                                ✓ شبیه‌سازی پرداخت موفق
                            </button>

                        </form>


                        <form
                            method="POST"
                            action="<?= $e(
                                        $baseUrl
                                            . '/payment/'
                                            . $orderId
                                            . '/failed'
                                    ) ?>">

                            <?= $csrfField ?? '' ?>

                            <button
                                type="submit"
                                class="btn btn-danger">
                                ✕ شبیه‌سازی پرداخت ناموفق
                            </button>

                        </form>


                    </div>


                <?php elseif ($paymentStatus === 'success'): ?>


                    <div class="transaction-box">

                        <strong>
                            ✓ پرداخت با موفقیت انجام شد
                        </strong>

                        سفارش شما با موفقیت ثبت و پرداخت شده است.

                        <?php if ($paymentId > 0): ?>

                            <br>

                            شناسه پرداخت:
                            <strong>
                                #<?= $paymentId ?>
                            </strong>

                        <?php endif; ?>

                    </div>


                    <a
                        href="<?= $e(
                                    $baseUrl
                                        . '/orders/'
                                        . $orderId
                                ) ?>"
                        class="btn btn-success">
                        مشاهده جزئیات سفارش
                    </a>


                <?php else: ?>


                    <div class="alert alert-danger">

                        پرداخت قبلی این سفارش ناموفق بوده است.

                        <br>

                        می‌توانید برای این سفارش دوباره
                        فرآیند پرداخت را امتحان کنید.

                    </div>


                    <a
                        href="<?= $e(
                                    $baseUrl
                                        . '/payment/'
                                        . $orderId
                                ) ?>"
                        class="btn btn-primary">
                        تلاش مجدد پرداخت
                    </a>


                <?php endif; ?>


                <div class="bottom-links">


                    <a
                        href="<?= $e(
                                    $baseUrl
                                        . '/orders/'
                                        . $orderId
                                ) ?>"
                        class="btn btn-secondary">
                        جزئیات سفارش
                    </a>


                    <a
                        href="<?= $e(
                                    $baseUrl
                                        . '/account'
                                ) ?>"
                        class="btn btn-secondary">
                        حساب کاربری
                    </a>


                </div>


            </div>

        </section>

    </main>

</body>

</html>
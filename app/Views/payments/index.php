<?php

declare(strict_types=1);

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

$baseUrl = (string) app_config(
    'base_url',
    ''
);

$orderId = (int) (
    $order['id'] ?? 0
);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        <?= htmlspecialchars(
            (string) (
                $title ?? 'پرداخت سفارش'
            ),
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-12 col-md-8 col-lg-6">

            <div class="card shadow-sm">

                <div class="card-body p-4">

                    <h1 class="h4 mb-4">
                        پرداخت سفارش
                    </h1>

                    <div class="mb-3">

                        <div class="text-muted mb-1">
                            شماره سفارش
                        </div>

                        <strong>
                            <?= htmlspecialchars(
                                $orderNumber,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </strong>

                    </div>

                    <div class="mb-3">

                        <div class="text-muted mb-1">
                            مبلغ قابل پرداخت
                        </div>

                        <strong class="fs-4">
                            <?= number_format($total) ?>
                            تومان
                        </strong>

                    </div>

                    <div class="mb-4">

                        <div class="text-muted mb-1">
                            وضعیت پرداخت
                        </div>

                        <?php if (
                            $paymentStatus === 'pending'
                        ): ?>

                            <span class="badge text-bg-warning">
                                در انتظار پرداخت
                            </span>

                        <?php elseif (
                            $paymentStatus === 'success'
                        ): ?>

                            <span class="badge text-bg-success">
                                پرداخت شده
                            </span>

                        <?php else: ?>

                            <span class="badge text-bg-danger">
                                ناموفق
                            </span>

                        <?php endif; ?>

                    </div>

                    <?php if (
                        $paymentStatus === 'pending'
                    ): ?>

                        <div class="alert alert-info">

                            <strong>
                                درگاه پرداخت آزمایشی
                            </strong>

                            <br>

                            این صفحه برای شبیه‌سازی
                            فرآیند پرداخت در پروژه فروشگاهی
                            استفاده می‌شود.

                        </div>

                        <!-- پرداخت موفق -->

                        <form
                            method="POST"
                            action="<?= htmlspecialchars(
                                $baseUrl
                                . '/payment/'
                                . $orderId
                                . '/success',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            class="mb-3"
                        >

                            <?= $csrfField ?? '' ?>

                            <button
                                type="submit"
                                class="btn btn-success btn-lg w-100"
                            >
                                پرداخت موفق
                            </button>

                        </form>

                        <!-- پرداخت ناموفق -->

                        <form
                            method="POST"
                            action="<?= htmlspecialchars(
                                $baseUrl
                                . '/payment/'
                                . $orderId
                                . '/failed',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                            <?= $csrfField ?? '' ?>

                            <button
                                type="submit"
                                class="btn btn-outline-danger w-100"
                            >
                                شبیه‌سازی پرداخت ناموفق
                            </button>

                        </form>

                    <?php elseif (
                        $paymentStatus === 'success'
                    ): ?>

                        <div class="alert alert-success">

                            پرداخت این سفارش با موفقیت انجام شده
                            و دیگر نیازی به پرداخت مجدد نیست.

                        </div>

                    <?php else: ?>

                        <div class="alert alert-warning">

                            پرداخت قبلی ناموفق بوده است.

                            <br>

                            برای تلاش مجدد، روی
                            <strong>تلاش مجدد پرداخت</strong>
                            کلیک کنید.

                        </div>

                        <a
                            href="<?= htmlspecialchars(
                                $baseUrl
                                . '/payment/'
                                . $orderId,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            class="btn btn-primary btn-lg w-100"
                        >
                            تلاش مجدد پرداخت
                        </a>

                    <?php endif; ?>

                    <div class="mt-4 d-flex gap-2">

                        <a
                            href="<?= htmlspecialchars(
                                $baseUrl
                                . '/orders/'
                                . $orderId,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            class="btn btn-outline-dark flex-fill"
                        >
                            جزئیات سفارش
                        </a>

                        <a
                            href="<?= htmlspecialchars(
                                $baseUrl
                                . '/account',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            class="btn btn-outline-secondary flex-fill"
                        >
                            حساب کاربری
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>
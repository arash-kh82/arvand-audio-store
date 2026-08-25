<?php

declare(strict_types=1);

$e = static fn($value): string =>
    htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );

$orders = is_array($orders ?? null)
    ? $orders
    : [];

$flashSuccess = \App\Core\Session::flash('success');
$flashError = \App\Core\Session::flash('error');
?>

<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        <?= $e($title ?? 'مدیریت سفارش‌ها') ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">
                مدیریت سفارش‌ها
            </h1>

            <p class="text-muted mb-0">
                مشاهده و مدیریت سفارش‌های مشتریان
            </p>
        </div>

        <a
            href="/arvand-audio-store/public/admin"
            class="btn btn-outline-secondary"
        >
            بازگشت به پنل
        </a>

    </div>

    <?php if ($flashSuccess): ?>

        <div class="alert alert-success">
            <?= $e($flashSuccess) ?>
        </div>

    <?php endif; ?>

    <?php if ($flashError): ?>

        <div class="alert alert-danger">
            <?= $e($flashError) ?>
        </div>

    <?php endif; ?>

    <div class="card shadow-sm">

        <div class="card-body">

            <?php if ($orders === []): ?>

                <div class="alert alert-info mb-0">
                    هنوز سفارشی ثبت نشده است.
                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table
                        class="table table-bordered table-hover align-middle mb-0"
                    >

                        <thead class="table-dark">

                        <tr>
                            <th>#</th>
                            <th>شماره سفارش</th>
                            <th>مشتری</th>
                            <th>ایمیل</th>
                            <th>مبلغ کل</th>
                            <th>وضعیت سفارش</th>
                            <th>وضعیت پرداخت</th>
                            <th>تاریخ</th>
                            <th>عملیات</th>
                        </tr>

                        </thead>

                        <tbody>

                        <?php foreach ($orders as $order): ?>

                            <?php
                            $status = (string) (
                                $order['status'] ?? ''
                            );

                            $paymentStatus = (string) (
                                $order['payment_status'] ?? ''
                            );
                            ?>

                            <tr>

                                <td>
                                    <?= (int) $order['id'] ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= $e($order['order_number']) ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= $e($order['user_name'] ?? '-') ?>
                                </td>

                                <td>
                                    <?= $e($order['user_email'] ?? '-') ?>
                                </td>

                                <td>
                                    <?= number_format(
                                        (float) $order['total']
                                    ) ?>
                                    تومان
                                </td>

                                <td>

                                    <?php
                                    $statusLabels = [
                                        'pending' => 'در انتظار',
                                        'paid' => 'پرداخت شده',
                                        'processing' => 'در حال پردازش',
                                        'shipped' => 'ارسال شده',
                                        'delivered' => 'تحویل شده',
                                        'cancelled' => 'لغو شده',
                                    ];

                                    $statusClasses = [
                                        'pending' => 'bg-warning text-dark',
                                        'paid' => 'bg-info text-dark',
                                        'processing' => 'bg-primary',
                                        'shipped' => 'bg-secondary',
                                        'delivered' => 'bg-success',
                                        'cancelled' => 'bg-danger',
                                    ];
                                    ?>

                                    <span
                                        class="badge <?= $e(
                                            $statusClasses[$status]
                                                ?? 'bg-dark'
                                        ) ?>"
                                    >
                                        <?= $e(
                                            $statusLabels[$status]
                                                ?? $status
                                        ) ?>
                                    </span>

                                </td>

                                <td>

                                    <?php
                                    $paymentLabels = [
                                        'pending' => 'در انتظار',
                                        'success' => 'موفق',
                                        'failed' => 'ناموفق',
                                    ];

                                    $paymentClasses = [
                                        'pending' => 'bg-warning text-dark',
                                        'success' => 'bg-success',
                                        'failed' => 'bg-danger',
                                    ];
                                    ?>

                                    <span
                                        class="badge <?= $e(
                                            $paymentClasses[$paymentStatus]
                                                ?? 'bg-dark'
                                        ) ?>"
                                    >
                                        <?= $e(
                                            $paymentLabels[$paymentStatus]
                                                ?? $paymentStatus
                                        ) ?>
                                    </span>

                                </td>

                                <td>
                                    <?= $e(
                                        $order['created_at'] ?? '-'
                                    ) ?>
                                </td>

                                <td>

                                    <a
                                        href="/arvand-audio-store/public/admin/orders\<?= (int) $order['id'] ?>"
                                        class="btn btn-sm btn-primary"
                                    >
                                        مشاهده
                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

</body>

</html>
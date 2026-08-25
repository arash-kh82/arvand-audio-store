<?php

declare(strict_types=1);

$e = static fn($value): string =>
    htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );

$order = is_array($order ?? null)
    ? $order
    : [];

$items = is_array($items ?? null)
    ? $items
    : [];

$statusLabels = [
    'pending' => 'در انتظار',
    'paid' => 'پرداخت شده',
    'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده',
    'delivered' => 'تحویل شده',
    'cancelled' => 'لغو شده',
];

$paymentLabels = [
    'pending' => 'در انتظار',
    'success' => 'موفق',
    'failed' => 'ناموفق',
];

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
        <?= $e($title ?? 'جزئیات سفارش') ?>
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
                جزئیات سفارش
            </h1>

            <p class="text-muted mb-0">
                <?= $e($order['order_number'] ?? '-') ?>
            </p>

        </div>

        <a
            href="/admin/orders"
            class="btn btn-outline-secondary"
        >
            بازگشت به سفارش‌ها
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


    <!-- اطلاعات سفارش -->

    <div class="card shadow-sm mb-4">

        <div class="card-header">
            <strong>اطلاعات سفارش</strong>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4 mb-3">

                    <strong>شماره سفارش:</strong>

                    <div>
                        <?= $e($order['order_number'] ?? '-') ?>
                    </div>

                </div>

                <div class="col-md-4 mb-3">

                    <strong>تاریخ ثبت:</strong>

                    <div>
                        <?= $e($order['created_at'] ?? '-') ?>
                    </div>

                </div>

                <div class="col-md-4 mb-3">

                    <strong>آخرین بروزرسانی:</strong>

                    <div>
                        <?= $e($order['updated_at'] ?? '-') ?>
                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="row">


        <!-- مشتری -->

        <div class="col-lg-6 mb-4">

            <div class="card shadow-sm h-100">

                <div class="card-header">
                    <strong>اطلاعات مشتری</strong>
                </div>

                <div class="card-body">

                    <p>
                        <strong>نام:</strong>
                        <?= $e($order['user_name'] ?? '-') ?>
                    </p>

                    <p>
                        <strong>ایمیل:</strong>
                        <?= $e($order['user_email'] ?? '-') ?>
                    </p>

                    <p class="mb-0">
                        <strong>تلفن:</strong>
                        <?= $e($order['user_phone'] ?? '-') ?>
                    </p>

                </div>

            </div>

        </div>


        <!-- وضعیت -->

        <div class="col-lg-6 mb-4">

            <div class="card shadow-sm h-100">

                <div class="card-header">
                    <strong>وضعیت سفارش</strong>
                </div>

                <div class="card-body">

                    <form
                        method="POST"
                        action="/admin/orders/<?= (int) $order['id'] ?>/status"
                        class="mb-4"
                    >

                        <?= $csrfField ?>

                        <label class="form-label">
                            وضعیت سفارش
                        </label>

                        <div class="input-group">

                            <select
                                name="status"
                                class="form-select"
                            >

                                <?php foreach ($statusLabels as $value => $label): ?>

                                    <option
                                        value="<?= $e($value) ?>"
                                        <?= ($order['status'] ?? '') === $value
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        <?= $e($label) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                بروزرسانی
                            </button>

                        </div>

                    </form>


                    <form
                        method="POST"
                        action="/admin/orders/<?= (int) $order['id'] ?>/payment-status"
                    >

                        <?= $csrfField ?>

                        <label class="form-label">
                            وضعیت پرداخت
                        </label>

                        <div class="input-group">

                            <select
                                name="payment_status"
                                class="form-select"
                            >

                                <?php foreach ($paymentLabels as $value => $label): ?>

                                    <option
                                        value="<?= $e($value) ?>"
                                        <?= ($order['payment_status'] ?? '') === $value
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        <?= $e($label) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                            <button
                                type="submit"
                                class="btn btn-success"
                            >
                                بروزرسانی
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>


    <!-- آدرس -->

    <div class="card shadow-sm mb-4">

        <div class="card-header">
            <strong>آدرس ارسال</strong>
        </div>

        <div class="card-body">

            <?php if (
                empty($order['address'])
                && empty($order['city'])
            ): ?>

                <div class="alert alert-warning mb-0">
                    برای این سفارش آدرسی ثبت نشده است.
                </div>

            <?php else: ?>

                <div class="row">

                    <div class="col-md-4 mb-3">

                        <strong>گیرنده:</strong>

                        <div>
                            <?= $e(
                                $order['receiver_name'] ?? '-'
                            ) ?>
                        </div>

                    </div>

                    <div class="col-md-4 mb-3">

                        <strong>تلفن:</strong>

                        <div>
                            <?= $e(
                                $order['address_phone'] ?? '-'
                            ) ?>
                        </div>

                    </div>

                    <div class="col-md-4 mb-3">

                        <strong>استان:</strong>

                        <div>
                            <?= $e(
                                $order['province'] ?? '-'
                            ) ?>
                        </div>

                    </div>

                    <div class="col-md-4 mb-3">

                        <strong>شهر:</strong>

                        <div>
                            <?= $e(
                                $order['city'] ?? '-'
                            ) ?>
                        </div>

                    </div>

                    <div class="col-md-8 mb-3">

                        <strong>کد پستی:</strong>

                        <div>
                            <?= $e(
                                $order['postal_code'] ?? '-'
                            ) ?>
                        </div>

                    </div>

                    <div class="col-12">

                        <strong>آدرس:</strong>

                        <div>
                            <?= nl2br(
                                $e($order['address'] ?? '-')
                            ) ?>
                        </div>

                    </div>

                </div>

            <?php endif; ?>

        </div>

    </div>


    <!-- محصولات -->

    <div class="card shadow-sm mb-4">

        <div class="card-header">
            <strong>محصولات سفارش</strong>
        </div>

        <div class="card-body">

            <?php if ($items === []): ?>

                <div class="alert alert-info mb-0">
                    آیتمی برای این سفارش ثبت نشده است.
                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table
                        class="table table-bordered table-hover align-middle mb-0"
                    >

                        <thead class="table-dark">

                        <tr>

                            <th>#</th>
                            <th>محصول</th>
                            <th>قیمت واحد</th>
                            <th>تعداد</th>
                            <th>مبلغ</th>

                        </tr>

                        </thead>

                        <tbody>

                        <?php foreach ($items as $item): ?>

                            <tr>

                                <td>
                                    <?= (int) $item['id'] ?>
                                </td>

                                <td>
                                    <?= $e(
                                        $item['product_name']
                                            ?? '-'
                                    ) ?>
                                </td>

                                <td>
                                    <?= number_format(
                                        (float) $item['price']
                                    ) ?>
                                    تومان
                                </td>

                                <td>
                                    <?= (int) $item['quantity'] ?>
                                </td>

                                <td>
                                    <?= number_format(
                                        (float) $item['total']
                                    ) ?>
                                    تومان
                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </div>


    <!-- مبالغ -->

    <div class="card shadow-sm">

        <div class="card-header">
            <strong>خلاصه مالی سفارش</strong>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-3 mb-3">

                    <div class="text-muted">
                        مبلغ کالاها
                    </div>

                    <strong>
                        <?= number_format(
                            (float) ($order['subtotal'] ?? 0)
                        ) ?>
                        تومان
                    </strong>

                </div>

                <div class="col-md-3 mb-3">

                    <div class="text-muted">
                        تخفیف
                    </div>

                    <strong>
                        <?= number_format(
                            (float) ($order['discount'] ?? 0)
                        ) ?>
                        تومان
                    </strong>

                </div>

                <div class="col-md-3 mb-3">

                    <div class="text-muted">
                        هزینه ارسال
                    </div>

                    <strong>
                        <?= number_format(
                            (float) ($order['shipping_cost'] ?? 0)
                        ) ?>
                        تومان
                    </strong>

                </div>

                <div class="col-md-3 mb-3">

                    <div class="text-muted">
                        مبلغ نهایی
                    </div>

                    <strong class="text-success fs-5">
                        <?= number_format(
                            (float) ($order['total'] ?? 0)
                        ) ?>
                        تومان
                    </strong>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>
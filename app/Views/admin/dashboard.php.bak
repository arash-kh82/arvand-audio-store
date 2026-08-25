<?php

declare(strict_types=1);

use App\Core\Session;

$e = static fn($value): string =>
    htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );

$success = Session::flash('success');
$error = Session::flash('error');

$statistics = is_array($statistics ?? null)
    ? $statistics
    : [];

$latestOrders = is_array($latestOrders ?? null)
    ? $latestOrders
    : [];

$lowStockProducts = is_array($lowStockProducts ?? null)
    ? $lowStockProducts
    : [];

$baseUrl = function_exists('app_config')
    ? rtrim((string) app_config('base_url', ''), '/')
    : '';

$url = static function (string $path) use ($baseUrl): string {
    return $baseUrl . '/' . ltrim($path, '/');
};

$formatMoney = static function ($value): string {
    return number_format(
        (float) $value,
        0,
        '.',
        ','
    ) . ' تومان';
};

$orderStatusLabels = [
    'pending' => 'در انتظار',
    'paid' => 'پرداخت شده',
    'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده',
    'delivered' => 'تحویل شده',
    'cancelled' => 'لغو شده',
];

$paymentStatusLabels = [
    'pending' => 'در انتظار',
    'success' => 'موفق',
    'failed' => 'ناموفق',
];

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
        <?= $e($title ?? 'داشبورد مدیریت') ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">

    <div class="container">

        <a
            class="navbar-brand fw-bold"
            href="<?= $e($url('/admin')) ?>"
        >
            فروشگاه آروند
        </a>

        <div class="d-flex gap-2">

            <a
                href="<?= $e($url('/admin/products')) ?>"
                class="btn btn-outline-light btn-sm"
            >
                محصولات
            </a>

            <a
                href="<?= $e($url('/admin/orders')) ?>"
                class="btn btn-outline-light btn-sm"
            >
                سفارش‌ها
            </a>

            <a
                href="<?= $e($url('/admin/users')) ?>"
                class="btn btn-outline-light btn-sm"
            >
                کاربران
            </a>

            <a
                href="<?= $e($url('/')) ?>"
                class="btn btn-outline-light btn-sm"
            >
                فروشگاه
            </a>

        </div>

    </div>

</nav>


<div class="container py-4">

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


    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">
                داشبورد مدیریت
            </h1>

            <p class="text-muted mb-0">
                نمای کلی وضعیت فروشگاه
            </p>

        </div>

    </div>


    <!-- Statistics -->

    <div class="row g-3 mb-4">

        <div class="col-12 col-md-6 col-xl-3">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <div class="text-muted mb-2">
                        کاربران
                    </div>

                    <div class="fs-3 fw-bold">
                        <?= $e($statistics['users'] ?? 0) ?>
                    </div>

                </div>

            </div>

        </div>


        <div class="col-12 col-md-6 col-xl-3">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <div class="text-muted mb-2">
                        محصولات
                    </div>

                    <div class="fs-3 fw-bold">
                        <?= $e($statistics['products'] ?? 0) ?>
                    </div>

                    <small class="text-success">
                        فعال:
                        <?= $e($statistics['active_products'] ?? 0) ?>
                    </small>

                </div>

            </div>

        </div>


        <div class="col-12 col-md-6 col-xl-3">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <div class="text-muted mb-2">
                        سفارش‌ها
                    </div>

                    <div class="fs-3 fw-bold">
                        <?= $e($statistics['orders'] ?? 0) ?>
                    </div>

                    <small class="text-warning">
                        در انتظار:
                        <?= $e($statistics['pending_orders'] ?? 0) ?>
                    </small>

                </div>

            </div>

        </div>


        <div class="col-12 col-md-6 col-xl-3">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <div class="text-muted mb-2">
                        فروش موفق
                    </div>

                    <div class="fs-5 fw-bold">
                        <?= $formatMoney($statistics['successful_sales'] ?? 0) ?>
                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- Order Status -->

    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <h2 class="h5 mb-3">
                وضعیت سفارش‌ها
            </h2>

            <div class="row g-3">

                <div class="col-6 col-md-3">

                    <div class="border rounded p-3">

                        <div class="text-muted">
                            در انتظار
                        </div>

                        <strong class="fs-4">
                            <?= $e($statistics['pending_orders'] ?? 0) ?>
                        </strong>

                    </div>

                </div>


                <div class="col-6 col-md-3">

                    <div class="border rounded p-3">

                        <div class="text-muted">
                            در حال پردازش
                        </div>

                        <strong class="fs-4">
                            <?= $e($statistics['processing_orders'] ?? 0) ?>
                        </strong>

                    </div>

                </div>


                <div class="col-6 col-md-3">

                    <div class="border rounded p-3">

                        <div class="text-muted">
                            ارسال شده
                        </div>

                        <strong class="fs-4">
                            <?= $e($statistics['shipped_orders'] ?? 0) ?>
                        </strong>

                    </div>

                </div>


                <div class="col-6 col-md-3">

                    <div class="border rounded p-3">

                        <div class="text-muted">
                            تحویل شده
                        </div>

                        <strong class="fs-4">
                            <?= $e($statistics['delivered_orders'] ?? 0) ?>
                        </strong>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="row g-4">


        <!-- Latest Orders -->

        <div class="col-12 col-lg-8">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <h2 class="h5 mb-0">
                            آخرین سفارش‌ها
                        </h2>

                        <a
                            href="<?= $e($url('/admin/orders')) ?>"
                            class="btn btn-sm btn-outline-primary"
                        >
                            مشاهده همه
                        </a>

                    </div>


                    <?php if ($latestOrders === []): ?>

                        <div class="text-center text-muted py-4">
                            هنوز سفارشی ثبت نشده است.
                        </div>

                    <?php else: ?>

                        <div class="table-responsive">

                            <table class="table table-hover align-middle">

                                <thead>

                                <tr>

                                    <th>
                                        سفارش
                                    </th>

                                    <th>
                                        مشتری
                                    </th>

                                    <th>
                                        مبلغ
                                    </th>

                                    <th>
                                        وضعیت
                                    </th>

                                    <th>
                                        عملیات
                                    </th>

                                </tr>

                                </thead>

                                <tbody>

                                <?php foreach ($latestOrders as $order): ?>

                                    <tr>

                                        <td>

                                            <strong>
                                                <?= $e($order['order_number'] ?? '-') ?>
                                            </strong>

                                            <div class="small text-muted">
                                                <?= $e($order['created_at'] ?? '-') ?>
                                            </div>

                                        </td>


                                        <td>

                                            <?= $e($order['user_name'] ?? '-') ?>

                                            <div class="small text-muted">
                                                <?= $e($order['user_email'] ?? '-') ?>
                                            </div>

                                        </td>


                                        <td>
                                            <?= $formatMoney($order['total'] ?? 0) ?>
                                        </td>


                                        <td>

                                            <div>
                                                <?= $e(
                                                    $orderStatusLabels[
                                                        $order['status'] ?? ''
                                                    ] ?? 'نامشخص'
                                                ) ?>
                                            </div>

                                            <small class="text-muted">

                                                پرداخت:
                                                <?= $e(
                                                    $paymentStatusLabels[
                                                        $order['payment_status'] ?? ''
                                                    ] ?? 'نامشخص'
                                                ) ?>

                                            </small>

                                        </td>


                                        <td>

                                            <a
                                                href="<?= $e(
                                                    $url(
                                                        '/admin/orders/' .
                                                        (int) ($order['id'] ?? 0)
                                                    )
                                                ) ?>"
                                                class="btn btn-sm btn-outline-secondary"
                                            >
                                                جزئیات
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


        <!-- Low Stock -->

        <div class="col-12 col-lg-4">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h2 class="h5 mb-3">
                        موجودی کم
                    </h2>


                    <?php if ($lowStockProducts === []): ?>

                        <div class="text-center text-muted py-4">
                            محصولی با موجودی کم وجود ندارد.
                        </div>

                    <?php else: ?>

                        <div class="list-group">

                            <?php foreach ($lowStockProducts as $product): ?>

                                <div class="list-group-item">

                                    <div class="d-flex justify-content-between align-items-start">

                                        <div>

                                            <div class="fw-bold">
                                                <?= $e($product['name'] ?? '-') ?>
                                            </div>

                                            <small class="text-muted">
                                                SKU:
                                                <?= $e($product['sku'] ?? '-') ?>
                                            </small>

                                        </div>


                                        <span class="badge text-bg-warning">
                                            <?= $e($product['stock'] ?? 0) ?>
                                        </span>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>


    <!-- Quick Access -->

    <div class="card shadow-sm mt-4">

        <div class="card-body">

            <h2 class="h5 mb-3">
                دسترسی سریع
            </h2>

            <div class="d-flex flex-wrap gap-2">

                <a
                    href="<?= $e($url('/admin/products')) ?>"
                    class="btn btn-primary"
                >
                    مدیریت محصولات
                </a>

                <a
                    href="<?= $e($url('/admin/products/create')) ?>"
                    class="btn btn-outline-primary"
                >
                    افزودن محصول
                </a>

                <a
                    href="<?= $e($url('/admin/orders')) ?>"
                    class="btn btn-outline-dark"
                >
                    مدیریت سفارش‌ها
                </a>

                <a
                    href="<?= $e($url('/admin/users')) ?>"
                    class="btn btn-outline-primary"
                >
                    مدیریت کاربران
                </a>

            </div>

        </div>

    </div>

</div>


</body>

</html>
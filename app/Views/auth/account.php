<?php

declare(strict_types=1);

use App\Core\Session;

$e = static fn($value): string =>
htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$user = is_array($user ?? null)
    ? $user
    : [];

$orders = is_array($orders ?? null)
    ? $orders
    : [];

$csrfField = (string) ($csrfField ?? '');

$success = Session::flash('success');
$error = Session::flash('error');

$baseUrl = rtrim(
    (string) app_config('base_url', ''),
    '/'
);

$statusLabels = [
    'pending' => 'در انتظار پرداخت',
    'paid' => 'پرداخت شده',
    'processing' => 'در حال پردازش',
    'shipped' => 'ارسال شده',
    'delivered' => 'تحویل داده شده',
    'cancelled' => 'لغو شده',
];

$statusClasses = [
    'pending' => 'status-pending',
    'paid' => 'status-success',
    'processing' => 'status-info',
    'shipped' => 'status-info',
    'delivered' => 'status-success',
    'cancelled' => 'status-danger',
];

?>

<!doctype html>

<html lang="fa" dir="rtl">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>
        <?= $e($title ?? 'حساب کاربری') ?>
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
            max-width: 1100px;
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

        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 18px;

            font-size: 13px;
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

        .alert-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }

        .top-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(280px, .7fr);
            gap: 22px;
            margin-bottom: 22px;
        }

        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(15, 23, 42, .05);
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

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .avatar {
            width: 58px;
            height: 58px;

            display: flex;
            align-items: center;
            justify-content: center;

            flex-shrink: 0;

            border-radius: 50%;
            background: #111827;
            color: #fff;

            font-size: 23px;
            font-weight: 900;
        }

        .user-name {
            font-size: 18px;
            font-weight: 900;
            color: #111827;
        }

        .user-email {
            margin-top: 5px;
            color: #6b7280;
            font-size: 12px;
            direction: ltr;
            text-align: right;
        }

        .user-info {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .info-box {
            background: #f9fafb;
            border: 1px solid #eef0f3;
            border-radius: 11px;
            padding: 13px;
        }

        .info-label {
            color: #6b7280;
            font-size: 11px;
            margin-bottom: 6px;
        }

        .info-value {
            color: #111827;
            font-size: 13px;
            font-weight: 800;
        }

        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;

            border-radius: 10px;
            border: 0;

            padding: 11px 14px;

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

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .btn-danger {
            background: #fff;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .btn-danger:hover {
            background: #fef2f2;
        }

        .btn-success {
            background: #059669;
            color: #fff;
        }

        .btn-success:hover {
            background: #047857;
        }

        .logout-form {
            margin-top: 5px;
        }

        .logout-form button {
            width: 100%;
        }

        .orders-card {
            margin-bottom: 22px;
        }

        .orders-table-wrapper {
            overflow-x: auto;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        .orders-table th,
        .orders-table td {
            padding: 14px 16px;
            text-align: right;
            border-bottom: 1px solid #eef0f3;
            font-size: 12px;
        }

        .orders-table th {
            background: #f9fafb;
            color: #6b7280;
            font-weight: 800;
            white-space: nowrap;
        }

        .orders-table td {
            color: #374151;
        }

        .orders-table tbody tr:hover {
            background: #fafafa;
        }

        .order-number {
            color: #111827;
            font-weight: 900;
        }

        .price {
            color: #111827;
            font-weight: 800;
            white-space: nowrap;
        }

        .date {
            color: #6b7280;
            direction: ltr;
            text-align: right;
            white-space: nowrap;
        }

        .status {
            display: inline-flex;
            align-items: center;

            padding: 6px 10px;
            border-radius: 999px;

            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
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

        .view-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            background: #111827;
            color: #fff;

            padding: 7px 12px;
            border-radius: 8px;

            font-size: 11px;
            font-weight: 800;

            transition: .2s;
        }

        .view-btn:hover {
            background: #1f2937;
        }

        .empty-orders {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-icon {
            font-size: 40px;
            margin-bottom: 12px;
        }

        .empty-orders h3 {
            margin: 0 0 8px;
            font-size: 17px;
        }

        .empty-orders p {
            margin: 0 0 18px;
            color: #6b7280;
            font-size: 13px;
        }

        .footer-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        @media (max-width: 800px) {

            .top-grid {
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

            .user-info {
                grid-template-columns: 1fr;
            }

            .footer-actions {
                flex-direction: column;
            }

            .footer-actions .btn {
                width: 100%;
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

                <a href="<?= $e($baseUrl . '/addresses') ?>">
                    📍 آدرس‌ها
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

            <span>
                حساب کاربری
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


        <div class="page-header">

            <h1>
                حساب کاربری
            </h1>

            <p>
                مدیریت اطلاعات حساب و مشاهده سفارش‌های شما
            </p>

        </div>


        <div class="top-grid">


            <!-- User information -->

            <section class="card">

                <div class="card-header">

                    <h2>
                        اطلاعات حساب
                    </h2>

                </div>


                <div class="card-body">


                    <div class="user-profile">

                        <div class="avatar">
                            <?= $e(
                                mb_substr(
                                    (string) (
                                        $user['name'] ?? 'ک'
                                    ),
                                    0,
                                    1
                                )
                            ) ?>
                        </div>


                        <div>

                            <div class="user-name">

                                <?= $e(
                                    $user['name']
                                        ?? 'کاربر'
                                ) ?>

                            </div>

                            <div class="user-email">

                                <?= $e(
                                    $user['email']
                                        ?? ''
                                ) ?>

                            </div>

                        </div>

                    </div>


                    <div class="user-info">


                        <div class="info-box">

                            <div class="info-label">
                                شناسه کاربر
                            </div>

                            <div class="info-value">
                                #<?= (int) (
                                        $user['id'] ?? 0
                                    ) ?>
                            </div>

                        </div>


                        <div class="info-box">

                            <div class="info-label">
                                نقش
                            </div>

                            <div class="info-value">

                                <?= $e(
                                    match ((string) (
                                        $user['role']
                                        ?? 'customer'
                                    )) {
                                        'admin' => 'مدیر',
                                        default => 'مشتری',
                                    }
                                ) ?>

                            </div>

                        </div>


                        <div class="info-box">

                            <div class="info-label">
                                وضعیت حساب
                            </div>

                            <div class="info-value">

                                <?= $e(
                                    match ((string) (
                                        $user['status']
                                        ?? 'active'
                                    )) {
                                        'active' => 'فعال',
                                        'inactive' => 'غیرفعال',
                                        default => (
                                            $user['status']
                                            ?? '---'
                                        ),
                                    }
                                ) ?>

                            </div>

                        </div>


                        <div class="info-box">

                            <div class="info-label">
                                ایمیل
                            </div>

                            <div class="info-value">
                                <?= $e(
                                    $user['email'] ?? '---'
                                ) ?>
                            </div>

                        </div>


                    </div>

                </div>

            </section>


            <!-- Quick actions -->

            <aside class="card">

                <div class="card-header">

                    <h2>
                        دسترسی سریع
                    </h2>

                </div>


                <div class="card-body">

                    <div class="quick-actions">


                        <a
                            class="btn btn-primary"
                            href="<?= $e(
                                        $baseUrl . '/products'
                                    ) ?>">
                            🎧 مشاهده محصولات
                        </a>


                        <a
                            class="btn btn-secondary"
                            href="<?= $e(
                                        $baseUrl . '/cart'
                                    ) ?>">
                            🛒 سبد خرید
                        </a>


                        <a
                            class="btn btn-secondary"
                            href="<?= $e(
                                        $baseUrl . '/addresses'
                                    ) ?>">
                            📍 مدیریت آدرس‌ها
                        </a>


                        <form
                            method="POST"
                            action="<?= $e(
                                        $baseUrl . '/logout'
                                    ) ?>"
                            class="logout-form">

                            <?= $csrfField ?>

                            <button
                                type="submit"
                                class="btn btn-danger">
                                خروج از حساب
                            </button>

                        </form>


                    </div>

                </div>

            </aside>


        </div>


        <!-- Orders -->

        <section class="card orders-card">

            <div class="card-header">

                <h2>
                    سفارش‌های من
                </h2>

            </div>


            <?php if ($orders === []): ?>

                <div class="empty-orders">

                    <div class="empty-icon">
                        📦
                    </div>

                    <h3>
                        هنوز سفارشی ثبت نکرده‌اید
                    </h3>

                    <p>
                        بعد از ثبت اولین سفارش، اطلاعات آن در این بخش نمایش داده می‌شود.
                    </p>

                    <a
                        href="<?= $e(
                                    $baseUrl . '/products'
                                ) ?>"
                        class="btn btn-primary">
                        مشاهده محصولات
                    </a>

                </div>

            <?php else: ?>


                <div class="orders-table-wrapper">

                    <table class="orders-table">

                        <thead>

                            <tr>

                                <th>
                                    شماره سفارش
                                </th>

                                <th>
                                    وضعیت
                                </th>

                                <th>
                                    مبلغ
                                </th>

                                <th>
                                    تاریخ ثبت
                                </th>

                                <th>
                                    عملیات
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php foreach ($orders as $order): ?>

                                <?php

                                $orderId = (int) (
                                    $order['id'] ?? 0
                                );

                                $orderNumber = (string) (
                                    $order['order_number'] ?? ''
                                );

                                $status = (string) (
                                    $order['status'] ?? 'pending'
                                );

                                $total = (float) (
                                    $order['total'] ?? 0
                                );

                                $createdAt = (string) (
                                    $order['created_at'] ?? ''
                                );

                                $statusText =
                                    $statusLabels[$status]
                                    ?? $status;

                                $statusClass =
                                    $statusClasses[$status]
                                    ?? 'status-pending';

                                ?>


                                <tr>


                                    <td>

                                        <span class="order-number">

                                            <?= $e(
                                                $orderNumber !== ''
                                                    ? $orderNumber
                                                    : '#'
                                                    . $orderId
                                            ) ?>

                                        </span>

                                    </td>


                                    <td>

                                        <span
                                            class="status <?= $e(
                                                                $statusClass
                                                            ) ?>">

                                            <?= $e(
                                                $statusText
                                            ) ?>

                                        </span>

                                    </td>


                                    <td>

                                        <span class="price">

                                            <?= number_format(
                                                $total,
                                                0,
                                                '.',
                                                ','
                                            ) ?>

                                            تومان

                                        </span>

                                    </td>


                                    <td>

                                        <span class="date">

                                            <?= $e(
                                                $createdAt !== ''
                                                    ? $createdAt
                                                    : '---'
                                            ) ?>

                                        </span>

                                    </td>


                                    <td>

                                        <?php if ($orderId > 0): ?>

                                            <a
                                                href="<?= $e(
                                                            $baseUrl
                                                                . '/orders/'
                                                                . $orderId
                                                        ) ?>"
                                                class="view-btn">
                                                مشاهده سفارش
                                            </a>

                                        <?php else: ?>

                                            <span>
                                                ---
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                </tr>


                            <?php endforeach; ?>


                        </tbody>

                    </table>

                </div>


            <?php endif; ?>

        </section>


        <div class="footer-actions">


            <a
                href="<?= $e(
                            $baseUrl . '/'
                        ) ?>"
                class="btn btn-secondary">
                ← بازگشت به خانه
            </a>



        </div>

    </main>

</body>

</html>
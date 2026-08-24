<?php

use App\Core\Session;

$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$user = $user ?? [];
$orders = is_array($orders ?? null) ? $orders : [];

$success = Session::flash('success');
$error = Session::flash('error');
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($title ?? 'حساب کاربری') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">

            <!-- کارت اصلی -->
            <div class="bg-white border rounded-3 p-4 shadow-sm">

                <!-- هدر -->
                <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                    <h1 class="h4 mb-0"><?= $e($title ?? 'حساب کاربری') ?></h1>
                    <form method="post" action="logout" class="m-0">
                        <?= $csrfField ?? '' ?>
                        <button type="submit" class="btn btn-outline-danger btn-sm">خروج</button>
                    </form>
                </div>

                <!-- پیام‌های فلش -->
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $e($success) ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $e($error) ?></div>
                <?php endif; ?>

                <!-- اطلاعات کاربری -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle mb-0">
                        <tbody>
                            <tr>
                                <th class="w-25">شناسه</th>
                                <td><?= $e($user['id'] ?? '') ?></td>
                            </tr>
                            <tr>
                                <th>نام</th>
                                <td><?= $e($user['name'] ?? '') ?></td>
                            </tr>
                            <tr>
                                <th>ایمیل</th>
                                <td><?= $e($user['email'] ?? '') ?></td>
                            </tr>
                            <tr>
                                <th>نقش</th>
                                <td><?= $e($user['role'] ?? 'customer') ?></td>
                            </tr>
                            <tr>
                                <th>وضعیت</th>
                                <td><?= $e($user['status'] ?? 'active') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- سفارش‌های من -->
                <div class="mt-4">
                    <h2 class="h5 mb-3">سفارش‌های من</h2>

                    <?php if ($orders === []): ?>
                        <div class="alert alert-info">
                            هنوز سفارشی ثبت نکرده‌اید.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>شماره سفارش</th>
                                        <th>مبلغ</th>
                                        <th>وضعیت</th>
                                        <th>تاریخ</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><?= $e($order['order_number'] ?? '') ?></td>
                                            <td><?= number_format((float) ($order['total'] ?? 0)) ?> تومان</td>
                                            <td><?= $e($order['status'] ?? '') ?></td>
                                            <td><?= $e($order['created_at'] ?? '') ?></td>
                                            <td>
                                                <a class="btn btn-sm btn-outline-primary" href="/orders/<?= (int) $order['id'] ?>">
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
                <!-- پایان سفارش‌های من -->

            </div>
            <!-- پایان کارت اصلی -->

        </div>
    </div>
</div>

</body>
</html>
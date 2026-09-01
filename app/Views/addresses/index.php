<?php

use App\Core\Session;

$e = static fn($value): string =>
htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$addresses = is_array($addresses ?? null)
    ? $addresses
    : [];

$baseUrl = rtrim(
    (string) app_config('base_url', ''),
    '/'
);

$success = Session::flash('success');
$error = Session::flash('error');

?>

<!doctype html>

<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>
        <?= $e($title ?? 'آدرس‌های من') ?>
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
            max-width: 1180px;
            margin: 0 auto;
            padding: 30px 20px 50px;
        }

        .aa-breadcrumb {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .aa-breadcrumb a {
            color: #374151;
        }

        .aa-breadcrumb a:hover {
            text-decoration: underline;
        }

        .aa-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 25px;
        }

        .aa-header h1 {
            margin: 0;
            font-size: 28px;
        }

        .aa-header p {
            margin: 7px 0 0;
            color: #6b7280;
            font-size: 14px;
        }

        .aa-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(300px, .8fr);
            gap: 25px;
            align-items: start;
        }

        .aa-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(15, 23, 42, .05);
        }

        .aa-card-header {
            padding: 20px 22px;
            border-bottom: 1px solid #eef0f3;
        }

        .aa-card-header h2 {
            margin: 0;
            font-size: 18px;
        }

        .aa-card-body {
            padding: 22px;
        }

        .alert {
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 18px;
            font-size: 14px;
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

        .address-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .address-card {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 18px;
            background: #fff;
            transition: .2s;
        }

        .address-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 5px 18px rgba(15, 23, 42, .05);
        }

        .address-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 15px;
        }

        .address-title {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
        }

        .address-badge {
            display: inline-block;
            margin-top: 6px;
            padding: 4px 9px;
            border-radius: 999px;
            background: #f3f4f6;
            color: #4b5563;
            font-size: 11px;
        }

        .address-delete {
            border: 0;
            background: #fef2f2;
            color: #dc2626;
            padding: 7px 11px;
            border-radius: 8px;
            cursor: pointer;
            font-family: inherit;
            font-size: 12px;
            transition: .2s;
        }

        .address-delete:hover {
            background: #fee2e2;
        }

        .address-details {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px 15px;
            margin-bottom: 15px;
        }

        .detail {
            color: #4b5563;
            font-size: 13px;
            line-height: 1.8;
        }

        .detail strong {
            color: #111827;
        }

        .address-text {
            padding: 13px 15px;
            background: #f9fafb;
            border-radius: 10px;
            color: #374151;
            font-size: 13px;
            line-height: 2;
            margin-top: 5px;
        }

        .empty-state {
            text-align: center;
            padding: 35px 20px;
        }

        .empty-icon {
            font-size: 42px;
            margin-bottom: 12px;
        }

        .empty-state h3 {
            margin: 0 0 8px;
            font-size: 18px;
        }

        .empty-state p {
            margin: 0 0 18px;
            color: #6b7280;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            margin-bottom: 7px;
            color: #374151;
            font-size: 13px;
            font-weight: 700;
        }

        .form-control {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 11px 13px;
            background: #fff;
            color: #111827;
            font-family: inherit;
            font-size: 13px;
            outline: none;
            transition: .2s;
        }

        .form-control:focus {
            border-color: #6b7280;
            box-shadow: 0 0 0 3px rgba(107, 114, 128, .1);
        }

        textarea.form-control {
            min-height: 105px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;

            border: 0;
            border-radius: 10px;
            padding: 11px 16px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: .2s;
        }

        .btn-primary {
            width: 100%;
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

        .aa-footer-links {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        @media (max-width: 800px) {
            .aa-grid {
                grid-template-columns: 1fr;
            }

            .aa-header {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 550px) {
            .aa-navbar-inner {
                align-items: flex-start;
                flex-direction: column;
            }

            .aa-nav {
                width: 100%;
            }

            .aa-header h1 {
                font-size: 23px;
            }

            .address-details,
            .form-row {
                grid-template-columns: 1fr;
            }

            .aa-container {
                padding: 22px 14px 40px;
            }

            .aa-card-body,
            .aa-card-header {
                padding: 17px;
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
            <a href="<?= $e($baseUrl . '/') ?>">خانه</a>
            <span> › </span>
            <span>آدرس‌های من</span>
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


        <div class="aa-header">

            <div>
                <h1>آدرس‌های من</h1>

                <p>
                    آدرس‌های ارسال سفارش خود را مدیریت کنید.
                </p>
            </div>

            <a
                class="btn btn-secondary"
                href="<?= $e($baseUrl . '/checkout') ?>">
                بازگشت به پرداخت
            </a>

        </div>


        <div class="aa-grid">


            <!-- Addresses -->

            <section class="aa-card">

                <div class="aa-card-header">

                    <h2>
                        آدرس‌های ثبت‌شده
                    </h2>

                </div>


                <div class="aa-card-body">

                    <?php if ($addresses === []): ?>

                        <div class="empty-state">

                            <div class="empty-icon">
                                📍
                            </div>

                            <h3>
                                هنوز آدرسی ثبت نکرده‌اید
                            </h3>

                            <p>
                                برای ثبت سفارش ابتدا یک آدرس برای ارسال کالا اضافه کنید.
                            </p>

                        </div>

                    <?php else: ?>

                        <div class="address-list">

                            <?php foreach ($addresses as $address): ?>

                                <article class="address-card">

                                    <div class="address-top">

                                        <div>

                                            <h3 class="address-title">
                                                <?= $e($address['title'] ?? 'آدرس') ?>
                                            </h3>

                                            <span class="address-badge">
                                                آدرس ارسال
                                            </span>

                                        </div>


                                        <form
                                            method="POST"
                                            action="<?= $e($baseUrl . '/addresses/delete') ?>"
                                            onsubmit="return confirm('آیا از حذف این آدرس مطمئن هستید؟');">

                                            <?= $csrfField ?>

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) ($address['id'] ?? 0) ?>">

                                            <button
                                                type="submit"
                                                class="address-delete">
                                                حذف آدرس
                                            </button>

                                        </form>

                                    </div>


                                    <div class="address-details">

                                        <div class="detail">
                                            <strong>گیرنده:</strong>
                                            <?= $e($address['receiver_name'] ?? '-') ?>
                                        </div>

                                        <div class="detail">
                                            <strong>تلفن:</strong>
                                            <?= $e($address['phone'] ?? '-') ?>
                                        </div>

                                        <div class="detail">
                                            <strong>استان:</strong>
                                            <?= $e($address['province'] ?? '-') ?>
                                        </div>

                                        <div class="detail">
                                            <strong>شهر:</strong>
                                            <?= $e($address['city'] ?? '-') ?>
                                        </div>

                                        <div class="detail">
                                            <strong>کد پستی:</strong>
                                            <?= $e($address['postal_code'] ?? '-') ?>
                                        </div>

                                    </div>


                                    <div class="address-text">

                                        <strong>آدرس کامل:</strong>

                                        <br>

                                        <?= nl2br($e($address['address'] ?? '-')) ?>

                                    </div>

                                </article>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                </div>

            </section>


            <!-- Add address -->

            <aside class="aa-card">

                <div class="aa-card-header">

                    <h2>
                        افزودن آدرس جدید
                    </h2>

                </div>


                <div class="aa-card-body">

                    <form
                        method="POST"
                        action="<?= $e($baseUrl . '/addresses') ?>">

                        <?= $csrfField ?>


                        <div class="form-group">

                            <label
                                class="form-label"
                                for="title">
                                عنوان آدرس
                            </label>

                            <input
                                id="title"
                                class="form-control"
                                type="text"
                                name="title"
                                placeholder="مثلاً منزل یا محل کار"
                                maxlength="100">

                        </div>


                        <div class="form-group">

                            <label
                                class="form-label"
                                for="receiver_name">
                                نام گیرنده
                            </label>

                            <input
                                id="receiver_name"
                                class="form-control"
                                type="text"
                                name="receiver_name"
                                placeholder="نام و نام خانوادگی"
                                required>

                        </div>


                        <div class="form-group">

                            <label
                                class="form-label"
                                for="phone">
                                شماره تماس
                            </label>

                            <input
                                id="phone"
                                class="form-control"
                                type="tel"
                                name="phone"
                                placeholder="مثلاً 09123456789"
                                required>

                        </div>


                        <div class="form-row">

                            <div class="form-group">

                                <label
                                    class="form-label"
                                    for="province">
                                    استان
                                </label>

                                <input
                                    id="province"
                                    class="form-control"
                                    type="text"
                                    name="province"
                                    placeholder="استان"
                                    required>

                            </div>


                            <div class="form-group">

                                <label
                                    class="form-label"
                                    for="city">
                                    شهر
                                </label>

                                <input
                                    id="city"
                                    class="form-control"
                                    type="text"
                                    name="city"
                                    placeholder="شهر"
                                    required>

                            </div>

                        </div>


                        <div class="form-group">

                            <label
                                class="form-label"
                                for="postal_code">
                                کد پستی
                            </label>

                            <input
                                id="postal_code"
                                class="form-control"
                                type="text"
                                name="postal_code"
                                placeholder="کد پستی ۱۰ رقمی"
                                maxlength="10">

                        </div>


                        <div class="form-group">

                            <label
                                class="form-label"
                                for="address">
                                آدرس کامل
                            </label>

                            <textarea
                                id="address"
                                class="form-control"
                                name="address"
                                placeholder="خیابان، کوچه، پلاک، واحد و..."
                                required></textarea>

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary">
                            ذخیره آدرس
                        </button>

                    </form>

                </div>

            </aside>

        </div>


        <div class="aa-footer-links">

            <a
                class="btn btn-secondary"
                href="<?= $e($baseUrl . '/products') ?>">
                ← ادامه خرید
            </a>

            <a
                class="btn btn-secondary"
                href="<?= $e($baseUrl . '/cart') ?>">
                🛒 مشاهده سبد خرید
            </a>

        </div>

    </main>

</body>

</html>
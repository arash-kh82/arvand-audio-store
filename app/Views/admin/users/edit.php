<?php

declare(strict_types=1);

$e = static fn($value): string =>
    htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );

$baseUrl = function_exists('app_config')
    ? rtrim(
        (string) app_config('base_url', ''),
        '/'
    )
    : '';

$url = static function (string $path) use ($baseUrl): string {
    return $baseUrl . '/' . ltrim($path, '/');
};

$user = is_array($user ?? null)
    ? $user
    : [];

$errors = is_array($errors ?? null)
    ? $errors
    : [];

$userId = (int) ($user['id'] ?? 0);

$userRole = (string) (
    $user['role'] ?? 'customer'
);

$userStatus = (string) (
    $user['status'] ?? 'active'
);

$currentAdminId = (int) (
    $currentAdminId ?? 0
);

$isCurrentAdmin =
    $userId > 0
    && $userId === $currentAdminId;
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
        <?= $e($title ?? 'ویرایش کاربر') ?>
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
            فروشگاه آروَند
        </a>

        <div class="d-flex gap-2">

            <a
                href="<?= $e($url('/admin')) ?>"
                class="btn btn-outline-light btn-sm"
            >
                داشبورد
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

    <div class="row justify-content-center">

        <div class="col-12 col-lg-8">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h1 class="h3 mb-1">
                        ویرایش کاربر
                    </h1>

                    <p class="text-muted mb-0">
                        ویرایش اطلاعات حساب کاربری
                    </p>

                </div>

                <a
                    href="<?= $e($url('/admin/users')) ?>"
                    class="btn btn-outline-secondary"
                >
                    بازگشت
                </a>

            </div>


            <?php if ($errors !== []): ?>

                <div class="alert alert-danger">

                    <div class="fw-bold mb-2">
                        خطاهای زیر را برطرف کنید:
                    </div>

                    <ul class="mb-0">

                        <?php foreach ($errors as $error): ?>

                            <li>
                                <?= $e($error) ?>
                            </li>

                        <?php endforeach; ?>

                    </ul>

                </div>

            <?php endif; ?>


            <div class="card shadow-sm">

                <div class="card-body p-4">

                    <form
                        method="POST"
                        action="<?= $e(
                            $url(
                                '/admin/users/'
                                . $userId
                                . '/update'
                            )
                        ) ?>"
                    >

                        <?= $csrfField ?? '' ?>


                        <div class="mb-3">

                            <label
                                for="name"
                                class="form-label"
                            >
                                نام و نام خانوادگی
                            </label>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control"
                                maxlength="120"
                                required
                                value="<?= $e(
                                    $user['name'] ?? ''
                                ) ?>"
                            >

                        </div>


                        <div class="mb-4">

                            <label
                                for="email"
                                class="form-label"
                            >
                                ایمیل
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                maxlength="190"
                                required
                                value="<?= $e(
                                    $user['email'] ?? ''
                                ) ?>"
                            >

                        </div>


                        <div class="row g-3 mb-4">

                            <div class="col-md-6">

                                <div class="border rounded p-3">

                                    <div class="text-muted small mb-1">
                                        نقش فعلی
                                    </div>

                                    <?php if ($userRole === 'admin'): ?>

                                        <span class="badge text-bg-dark">
                                            مدیر
                                        </span>

                                    <?php else: ?>

                                        <span class="badge text-bg-primary">
                                            مشتری
                                        </span>

                                    <?php endif; ?>

                                </div>

                            </div>


                            <div class="col-md-6">

                                <div class="border rounded p-3">

                                    <div class="text-muted small mb-1">
                                        وضعیت فعلی
                                    </div>

                                    <?php if ($userStatus === 'active'): ?>

                                        <span class="badge text-bg-success">
                                            فعال
                                        </span>

                                    <?php else: ?>

                                        <span class="badge text-bg-danger">
                                            غیرفعال
                                        </span>

                                    <?php endif; ?>

                                </div>

                            </div>

                        </div>


                        <div class="border rounded p-3 mb-4 bg-light">

                            <div class="row g-3">

                                <div class="col-md-6">

                                    <div class="text-muted small">
                                        شناسه کاربر
                                    </div>

                                    <strong>
                                        #<?= $e($userId) ?>
                                    </strong>

                                </div>


                                <div class="col-md-6">

                                    <div class="text-muted small">
                                        تاریخ ثبت‌نام
                                    </div>

                                    <strong>
                                        <?= $e(
                                            $user['created_at'] ?? '-'
                                        ) ?>
                                    </strong>

                                </div>


                                <div class="col-12">

                                    <div class="text-muted small">
                                        آخرین بروزرسانی
                                    </div>

                                    <strong>
                                        <?= $e(
                                            $user['updated_at'] ?? '-'
                                        ) ?>
                                    </strong>

                                </div>

                            </div>

                        </div>


                        <?php if ($isCurrentAdmin): ?>

                            <div class="alert alert-warning">

                                <strong>
                                    توجه:
                                </strong>

                                این حساب، حساب مدیری است که در حال حاضر
                                وارد پنل شده است. تغییر نقش یا غیرفعال کردن
                                این حساب از طریق این بخش مجاز نیست.

                            </div>

                        <?php endif; ?>


                        <div class="d-flex gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary flex-fill"
                            >
                                ذخیره تغییرات
                            </button>

                            <a
                                href="<?= $e($url('/admin/users')) ?>"
                                class="btn btn-outline-secondary flex-fill"
                            >
                                انصراف
                            </a>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>
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
        <?= $e($title ?? 'افزودن برند') ?>
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

        <div class="d-flex gap-2 flex-wrap">

            <a
                href="<?= $e($url('/admin')) ?>"
                class="btn btn-outline-light btn-sm"
            >
                داشبورد
            </a>

            <a
                href="<?= $e($url('/admin/products')) ?>"
                class="btn btn-outline-light btn-sm"
            >
                محصولات
            </a>

            <a
                href="<?= $e($url('/admin/categories')) ?>"
                class="btn btn-outline-light btn-sm"
            >
                دسته‌بندی‌ها
            </a>

            <a
                href="<?= $e($url('/admin/brands')) ?>"
                class="btn btn-outline-light btn-sm"
            >
                برندها
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
                        افزودن برند
                    </h1>

                    <p class="text-muted mb-0">
                        ثبت یک برند جدید در فروشگاه
                    </p>

                </div>

                <a
                    href="<?= $e($url('/admin/brands')) ?>"
                    class="btn btn-outline-secondary"
                >
                    بازگشت
                </a>

            </div>


            <div class="card shadow-sm">

                <div class="card-body">

                    <form
                        method="POST"
                        action="<?= $e($url('/admin/brands')) ?>"
                    >

                        <?= $csrfField ?? '' ?>


                        <div class="mb-3">

                            <label
                                for="name"
                                class="form-label"
                            >
                                نام برند
                            </label>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control"
                                required
                                maxlength="255"
                                value="<?= $e(
                                    $_POST['name'] ?? ''
                                ) ?>"
                            >

                        </div>


                        <div class="mb-3">

                            <label
                                for="slug"
                                class="form-label"
                            >
                                Slug
                            </label>

                            <input
                                type="text"
                                id="slug"
                                name="slug"
                                class="form-control"
                                required
                                maxlength="255"
                                dir="ltr"
                                value="<?= $e(
                                    $_POST['slug'] ?? ''
                                ) ?>"
                            >

                            <div class="form-text">
                                مثال:
                                <code>shure</code>
                            </div>

                        </div>


                        <div class="mb-3">

                            <label
                                for="logo"
                                class="form-label"
                            >
                                لوگو
                            </label>

                            <input
                                type="text"
                                id="logo"
                                name="logo"
                                class="form-control"
                                dir="ltr"
                                maxlength="500"
                                value="<?= $e(
                                    $_POST['logo'] ?? ''
                                ) ?>"
                                placeholder="مسیر یا آدرس لوگو"
                            >

                        </div>


                        <div class="form-check form-switch mb-4">

                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="status"
                                name="status"
                                value="1"
                                checked
                            >

                            <label
                                class="form-check-label"
                                for="status"
                            >
                                برند فعال باشد
                            </label>

                        </div>


                        <div class="d-flex gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                ذخیره برند
                            </button>

                            <a
                                href="<?= $e(
                                    $url('/admin/brands')
                                ) ?>"
                                class="btn btn-outline-secondary"
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
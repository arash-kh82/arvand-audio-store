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

$categories = is_array($categories ?? null)
    ? $categories
    : [];
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
        <?= $e($title ?? 'افزودن دسته‌بندی') ?>
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
                        افزودن دسته‌بندی
                    </h1>

                    <p class="text-muted mb-0">
                        ایجاد یک دسته‌بندی جدید برای محصولات
                    </p>

                </div>

                <a
                    href="<?= $e($url('/admin/categories')) ?>"
                    class="btn btn-outline-secondary"
                >
                    بازگشت
                </a>

            </div>


            <div class="card shadow-sm">

                <div class="card-body">

                    <form
                        method="POST"
                        action="<?= $e($url('/admin/categories')) ?>"
                    >

                        <?= $csrfField ?? '' ?>


                        <!-- Name -->

                        <div class="mb-3">

                            <label
                                for="name"
                                class="form-label"
                            >
                                نام دسته‌بندی
                            </label>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control"
                                required
                                maxlength="255"
                                placeholder="مثلاً میکروفون استودیویی"
                            >

                        </div>


                        <!-- Slug -->

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
                                placeholder="studio-microphones"
                            >

                            <div class="form-text">
                                برای آدرس صفحات استفاده می‌شود.
                                بهتر است فقط از حروف انگلیسی، عدد و خط تیره استفاده شود.
                            </div>

                        </div>


                        <!-- Parent -->

                        <div class="mb-3">

                            <label
                                for="parent_id"
                                class="form-label"
                            >
                                دسته والد
                            </label>

                            <select
                                id="parent_id"
                                name="parent_id"
                                class="form-select"
                            >

                                <option value="0">
                                    بدون دسته والد
                                </option>

                                <?php foreach ($categories as $category): ?>

                                    <option
                                        value="<?= (int) ($category['id'] ?? 0) ?>"
                                    >
                                        <?= $e($category['name'] ?? '-') ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- Description -->

                        <div class="mb-3">

                            <label
                                for="description"
                                class="form-label"
                            >
                                توضیحات
                            </label>

                            <textarea
                                id="description"
                                name="description"
                                class="form-control"
                                rows="4"
                                placeholder="توضیحات مربوط به دسته‌بندی..."
                            ></textarea>

                        </div>


                        <!-- Image -->

                        <div class="mb-3">

                            <label
                                for="image"
                                class="form-label"
                            >
                                تصویر
                            </label>

                            <input
                                type="text"
                                id="image"
                                name="image"
                                class="form-control"
                                dir="ltr"
                                placeholder="uploads/categories/microphone.jpg"
                            >

                            <div class="form-text">
                                فعلاً مسیر تصویر را وارد می‌کنیم؛ سیستم آپلود را در مرحله مربوط به تصاویر تکمیل می‌کنیم.
                            </div>

                        </div>


                        <!-- Status -->

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
                                دسته‌بندی فعال باشد
                            </label>

                        </div>


                        <!-- Actions -->

                        <div class="d-flex gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                ذخیره دسته‌بندی
                            </button>

                            <a
                                href="<?= $e($url('/admin/categories')) ?>"
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
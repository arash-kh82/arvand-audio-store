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

$category = is_array($category ?? null)
    ? $category
    : [];

$categories = is_array($categories ?? null)
    ? $categories
    : [];

$categoryId = (int) ($category['id'] ?? 0);
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
        <?= $e($title ?? 'ویرایش دسته‌بندی') ?>
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
                        ویرایش دسته‌بندی
                    </h1>

                    <p class="text-muted mb-0">
                        ویرایش اطلاعات دسته‌بندی
                    </p>

                </div>

                <a
                    href="<?= $e($url('/admin/categories')) ?>"
                    class="btn btn-outline-secondary"
                >
                    بازگشت
                </a>

            </div>


            <?php if ($categoryId <= 0): ?>

                <div class="alert alert-danger">
                    شناسه دسته‌بندی معتبر نیست.
                </div>

            <?php else: ?>

                <div class="card shadow-sm">

                    <div class="card-body">

                        <form
                            method="POST"
                            action="<?= $e(
                                $url(
                                    '/admin/categories/'
                                    . $categoryId
                                    . '/update'
                                )
                            ) ?>"
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
                                    value="<?= $e(
                                        $category['name'] ?? ''
                                    ) ?>"
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
                                    value="<?= $e(
                                        $category['slug'] ?? ''
                                    ) ?>"
                                >

                                <div class="form-text">
                                    بهتر است slug یکتا و شامل حروف انگلیسی، عدد و خط تیره باشد.
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

                                    <?php foreach ($categories as $parent): ?>

                                        <?php
                                        $parentId = (int) (
                                            $parent['id'] ?? 0
                                        );

                                        if (
                                            $parentId <= 0
                                            || $parentId === $categoryId
                                        ) {
                                            continue;
                                        }

                                        $selected =
                                            (int) (
                                                $category['parent_id'] ?? 0
                                            ) === $parentId;
                                        ?>

                                        <option
                                            value="<?= $parentId ?>"
                                            <?= $selected
                                                ? 'selected'
                                                : '' ?>
                                        >
                                            <?= $e(
                                                $parent['name'] ?? '-'
                                            ) ?>
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
                                ><?= $e(
                                    $category['description'] ?? ''
                                ) ?></textarea>

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
                                    value="<?= $e(
                                        $category['image'] ?? ''
                                    ) ?>"
                                >

                            </div>


                            <!-- Status -->

                            <?php
                            $isActive =
                                (int) (
                                    $category['status'] ?? 0
                                ) === 1;
                            ?>

                            <div class="form-check form-switch mb-4">

                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    id="status"
                                    name="status"
                                    value="1"
                                    <?= $isActive
                                        ? 'checked'
                                        : '' ?>
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
                                    ذخیره تغییرات
                                </button>

                                <a
                                    href="<?= $e(
                                        $url(
                                            '/admin/categories'
                                        )
                                    ) ?>"
                                    class="btn btn-outline-secondary"
                                >
                                    انصراف
                                </a>

                            </div>

                        </form>

                    </div>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

</body>

</html>
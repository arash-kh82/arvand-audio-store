<?php

declare(strict_types=1);

use App\Core\Session;

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

$success = Session::flash('success');
$error = Session::flash('error');

$categories = is_array($categories ?? null)
    ? $categories
    : [];

$search = (string) ($search ?? '');
$status = (string) ($status ?? '');
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
        <?= $e($title ?? 'مدیریت دسته‌بندی‌ها') ?>
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
                href="<?= $e($url('/admin/brands')) ?>"
                class="btn btn-outline-light btn-sm"
            >
                برندها
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


    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <h1 class="h3 mb-1">
                مدیریت دسته‌بندی‌ها
            </h1>

            <p class="text-muted mb-0">
                مدیریت دسته‌بندی محصولات فروشگاه
            </p>

        </div>

        <a
            href="<?= $e($url('/admin/categories/create')) ?>"
            class="btn btn-primary"
        >
            افزودن دسته‌بندی
        </a>

    </div>


    <!-- Filters -->

    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="<?= $e($url('/admin/categories')) ?>"
            >

                <div class="row g-3 align-items-end">

                    <div class="col-12 col-lg-6">

                        <label
                            for="search"
                            class="form-label"
                        >
                            جستجو
                        </label>

                        <input
                            type="text"
                            id="search"
                            name="search"
                            class="form-control"
                            value="<?= $e($search) ?>"
                            placeholder="نام یا slug دسته‌بندی..."
                        >

                    </div>


                    <div class="col-12 col-md-6 col-lg-3">

                        <label
                            for="status"
                            class="form-label"
                        >
                            وضعیت
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="form-select"
                        >

                            <option value="">
                                همه
                            </option>

                            <option
                                value="active"
                                <?= $status === 'active' ? 'selected' : '' ?>
                            >
                                فعال
                            </option>

                            <option
                                value="inactive"
                                <?= $status === 'inactive' ? 'selected' : '' ?>
                            >
                                غیرفعال
                            </option>

                        </select>

                    </div>


                    <div class="col-12 col-md-6 col-lg-3 d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary flex-fill"
                        >
                            جستجو
                        </button>

                        <a
                            href="<?= $e($url('/admin/categories')) ?>"
                            class="btn btn-outline-secondary"
                        >
                            پاک کردن
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- Categories -->

    <div class="card shadow-sm">

        <div class="card-body">

            <?php if ($categories === []): ?>

                <div class="text-center py-5">

                    <div class="fs-1 mb-3">
                        📂
                    </div>

                    <h2 class="h5">
                        دسته‌بندی‌ای پیدا نشد
                    </h2>

                    <p class="text-muted mb-0">
                        با فیلترهای فعلی هیچ دسته‌بندی‌ای وجود ندارد.
                    </p>

                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                        <tr>

                            <th>#</th>

                            <th>
                                نام دسته‌بندی
                            </th>

                            <th>
                                Slug
                            </th>

                            <th>
                                دسته والد
                            </th>

                            <th>
                                تعداد محصولات
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

                        <?php foreach ($categories as $category): ?>

                            <?php
                            $categoryId = (int) (
                                $category['id'] ?? 0
                            );

                            $categoryStatus = (int) (
                                $category['status'] ?? 0
                            );
                            ?>

                            <tr>

                                <td>
                                    <?= $e($categoryId) ?>
                                </td>


                                <td>

                                    <div class="fw-bold">
                                        <?= $e($category['name'] ?? '-') ?>
                                    </div>

                                    <?php if (
                                        !empty($category['description'])
                                    ): ?>

                                        <div class="small text-muted">
                                            <?= $e(
                                                $category['description']
                                            ) ?>
                                        </div>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <code>
                                        <?= $e($category['slug'] ?? '-') ?>
                                    </code>

                                </td>


                                <td>
                                    <?= $e(
                                        $category['parent_name'] ?? '-'
                                    ) ?>
                                </td>


                                <td>

                                    <span class="badge text-bg-info">
                                        <?= $e(
                                            $category['products_count'] ?? 0
                                        ) ?>
                                    </span>

                                </td>


                                <td>

                                    <?php if ($categoryStatus === 1): ?>

                                        <span class="badge text-bg-success">
                                            فعال
                                        </span>

                                    <?php else: ?>

                                        <span class="badge text-bg-secondary">
                                            غیرفعال
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <div class="d-flex flex-wrap gap-2">

                                        <a
                                            href="<?= $e(
                                                $url(
                                                    '/admin/categories/'
                                                    . $categoryId
                                                    . '/edit'
                                                )
                                            ) ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            ویرایش
                                        </a>


                                        <form
                                            method="POST"
                                            action="<?= $e(
                                                $url(
                                                    '/admin/categories/'
                                                    . $categoryId
                                                    . '/status'
                                                )
                                            ) ?>"
                                        >

                                            <?= $csrfField ?? '' ?>

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-warning"
                                            >
                                                <?= $categoryStatus === 1
                                                    ? 'غیرفعال کردن'
                                                    : 'فعال کردن' ?>
                                            </button>

                                        </form>


                                        <form
                                            method="POST"
                                            action="<?= $e(
                                                $url(
                                                    '/admin/categories/'
                                                    . $categoryId
                                                    . '/delete'
                                                )
                                            ) ?>"
                                            onsubmit="return confirm('آیا از حذف این دسته‌بندی مطمئن هستید؟');"
                                        >

                                            <?= $csrfField ?? '' ?>

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                            >
                                                حذف
                                            </button>

                                        </form>

                                    </div>

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

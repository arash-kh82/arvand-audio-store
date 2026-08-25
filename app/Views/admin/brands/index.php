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

$brands = is_array($brands ?? null)
    ? $brands
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
        <?= $e($title ?? 'مدیریت برندها') ?>
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

        </div>

    </div>

</nav>


<div class="container py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <h1 class="h3 mb-1">
                مدیریت برندها
            </h1>

            <p class="text-muted mb-0">
                مدیریت برندهای محصولات فروشگاه
            </p>

        </div>

        <a
            href="<?= $e($url('/admin/brands/create')) ?>"
            class="btn btn-primary"
        >
            افزودن برند
        </a>

    </div>


    <!-- Filters -->

    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="<?= $e($url('/admin/brands')) ?>"
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
                            placeholder="نام یا Slug برند..."
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
                                value="1"
                                <?= $status === '1' ? 'selected' : '' ?>
                            >
                                فعال
                            </option>

                            <option
                                value="0"
                                <?= $status === '0' ? 'selected' : '' ?>
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
                            href="<?= $e($url('/admin/brands')) ?>"
                            class="btn btn-outline-secondary"
                        >
                            پاک
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- Brands -->

    <div class="card shadow-sm">

        <div class="card-body">

            <?php if ($brands === []): ?>

                <div class="alert alert-info mb-0">
                    هیچ برندی پیدا نشد.
                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-dark">

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                برند
                            </th>

                            <th>
                                Slug
                            </th>

                            <th>
                                لوگو
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

                        <?php foreach ($brands as $brand): ?>

                            <?php
                            $brandId = (int) ($brand['id'] ?? 0);

                            $brandStatus = (int) (
                                $brand['status'] ?? 0
                            );
                            ?>

                            <tr>

                                <td>
                                    <?= $brandId ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= $e($brand['name'] ?? '-') ?>
                                    </strong>
                                </td>

                                <td dir="ltr">
                                    <?= $e($brand['slug'] ?? '-') ?>
                                </td>

                                <td>

                                    <?php if (
                                        !empty($brand['logo'])
                                    ): ?>

                                        <span
                                            class="small text-muted"
                                            dir="ltr"
                                        >
                                            <?= $e($brand['logo']) ?>
                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            -
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <?php if ($brandStatus === 1): ?>

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
                                                    '/admin/brands/'
                                                    . $brandId
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
                                                    '/admin/brands/'
                                                    . $brandId
                                                    . '/status'
                                                )
                                            ) ?>"
                                            class="d-inline"
                                        >

                                            <?= $csrfField ?? '' ?>

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-warning"
                                            >
                                                <?= $brandStatus === 1
                                                    ? 'غیرفعال کردن'
                                                    : 'فعال کردن' ?>
                                            </button>

                                        </form>


                                        <form
                                            method="POST"
                                            action="<?= $e(
                                                $url(
                                                    '/admin/brands/'
                                                    . $brandId
                                                    . '/delete'
                                                )
                                            ) ?>"
                                            class="d-inline"
                                            onsubmit="return confirm('آیا از حذف این برند مطمئن هستید؟');"
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

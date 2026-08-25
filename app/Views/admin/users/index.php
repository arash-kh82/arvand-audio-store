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

$users = is_array($users ?? null)
    ? $users
    : [];

$statistics = is_array($statistics ?? null)
    ? $statistics
    : [];

$search = (string) ($search ?? '');
$role = (string) ($role ?? '');
$status = (string) ($status ?? '');

$page = max(
    1,
    (int) ($page ?? 1)
);

$totalPages = max(
    1,
    (int) ($totalPages ?? 1)
);

$total = (int) ($total ?? 0);
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
        <?= $e($title ?? 'مدیریت کاربران') ?>
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


    <!-- Header -->

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <h1 class="h3 mb-1">
                مدیریت کاربران
            </h1>

            <p class="text-muted mb-0">
                مدیریت حساب‌ها، نقش‌ها و وضعیت کاربران فروشگاه
            </p>

        </div>

    </div>


    <!-- Statistics -->

    <div class="row g-3 mb-4">

        <div class="col-6 col-lg">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <div class="text-muted small mb-2">
                        کل کاربران
                    </div>

                    <div class="fs-3 fw-bold">
                        <?= $e($statistics['total'] ?? 0) ?>
                    </div>

                </div>

            </div>

        </div>


        <div class="col-6 col-lg">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <div class="text-muted small mb-2">
                        مشتریان
                    </div>

                    <div class="fs-3 fw-bold text-primary">
                        <?= $e($statistics['customers'] ?? 0) ?>
                    </div>

                </div>

            </div>

        </div>


        <div class="col-6 col-lg">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <div class="text-muted small mb-2">
                        مدیران
                    </div>

                    <div class="fs-3 fw-bold text-dark">
                        <?= $e($statistics['admins'] ?? 0) ?>
                    </div>

                </div>

            </div>

        </div>


        <div class="col-6 col-lg">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <div class="text-muted small mb-2">
                        فعال
                    </div>

                    <div class="fs-3 fw-bold text-success">
                        <?= $e($statistics['active'] ?? 0) ?>
                    </div>

                </div>

            </div>

        </div>


        <div class="col-6 col-lg">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <div class="text-muted small mb-2">
                        غیرفعال
                    </div>

                    <div class="fs-3 fw-bold text-danger">
                        <?= $e($statistics['inactive'] ?? 0) ?>
                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- Filters -->

    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="<?= $e($url('/admin/users')) ?>"
            >

                <div class="row g-3 align-items-end">

                    <div class="col-12 col-lg-5">

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
                            placeholder="نام یا ایمیل کاربر..."
                        >

                    </div>


                    <div class="col-12 col-md-4 col-lg-2">

                        <label
                            for="role"
                            class="form-label"
                        >
                            نقش
                        </label>

                        <select
                            id="role"
                            name="role"
                            class="form-select"
                        >

                            <option value="">
                                همه
                            </option>

                            <option
                                value="customer"
                                <?= $role === 'customer' ? 'selected' : '' ?>
                            >
                                مشتری
                            </option>

                            <option
                                value="admin"
                                <?= $role === 'admin' ? 'selected' : '' ?>
                            >
                                مدیر
                            </option>

                        </select>

                    </div>


                    <div class="col-12 col-md-4 col-lg-2">

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


                    <div class="col-12 col-md-4 col-lg-3 d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary flex-fill"
                        >
                            جستجو
                        </button>

                        <a
                            href="<?= $e($url('/admin/users')) ?>"
                            class="btn btn-outline-secondary"
                        >
                            پاک کردن
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- Users -->

    <div class="card shadow-sm">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>

                    <h2 class="h5 mb-1">
                        کاربران
                    </h2>

                    <small class="text-muted">
                        تعداد نتایج:
                        <?= $e($total) ?>
                    </small>

                </div>

            </div>


            <?php if ($users === []): ?>

                <div class="text-center py-5">

                    <div class="fs-1 mb-3">
                        👤
                    </div>

                    <h3 class="h5">
                        کاربری پیدا نشد
                    </h3>

                    <p class="text-muted mb-0">
                        با فیلترهای فعلی هیچ کاربری وجود ندارد.
                    </p>

                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                کاربر
                            </th>

                            <th>
                                نقش
                            </th>

                            <th>
                                وضعیت
                            </th>

                            <th>
                                تاریخ ثبت‌نام
                            </th>

                            <th>
                                عملیات
                            </th>

                        </tr>

                        </thead>

                        <tbody>

                        <?php foreach ($users as $user): ?>

                            <?php
                            $userId = (int) ($user['id'] ?? 0);

                            $userRole = (string) (
                                $user['role'] ?? 'customer'
                            );

                            $userStatus = (string) (
                                $user['status'] ?? 'active'
                            );
                            ?>

                            <tr>

                                <td>
                                    <?= $e($userId) ?>
                                </td>


                                <td>

                                    <div class="fw-bold">
                                        <?= $e($user['name'] ?? '-') ?>
                                    </div>

                                    <div class="small text-muted">
                                        <?= $e($user['email'] ?? '-') ?>
                                    </div>

                                </td>


                                <td>

                                    <?php if ($userRole === 'admin'): ?>

                                        <span class="badge text-bg-dark">
                                            مدیر
                                        </span>

                                    <?php else: ?>

                                        <span class="badge text-bg-primary">
                                            مشتری
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php if ($userStatus === 'active'): ?>

                                        <span class="badge text-bg-success">
                                            فعال
                                        </span>

                                    <?php else: ?>

                                        <span class="badge text-bg-danger">
                                            غیرفعال
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <span class="small">
                                        <?= $e($user['created_at'] ?? '-') ?>
                                    </span>

                                </td>


                                <td>

                                    <div class="d-flex flex-wrap gap-2">

                                        <a
                                            href="<?= $e(
                                                $url(
                                                    '/admin/users/'
                                                    . $userId
                                                    . '/edit'
                                                )
                                            ) ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            ویرایش
                                        </a>


                                        <!-- Role -->

                                        <form
                                            method="POST"
                                            action="<?= $e(
                                                $url(
                                                    '/admin/users/'
                                                    . $userId
                                                    . '/role'
                                                )
                                            ) ?>"
                                        >

                                            <?= $csrfField ?? '' ?>

                                            <input
                                                type="hidden"
                                                name="role"
                                                value="<?= $userRole === 'admin'
                                                    ? 'customer'
                                                    : 'admin' ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-dark"
                                            >
                                                <?= $userRole === 'admin'
                                                    ? 'مشتری کردن'
                                                    : 'مدیر کردن' ?>
                                            </button>

                                        </form>


                                        <!-- Status -->

                                        <form
                                            method="POST"
                                            action="<?= $e(
                                                $url(
                                                    '/admin/users/'
                                                    . $userId
                                                    . '/status'
                                                )
                                            ) ?>"
                                        >

                                            <?= $csrfField ?? '' ?>

                                            <input
                                                type="hidden"
                                                name="status"
                                                value="<?= $userStatus === 'active'
                                                    ? 'inactive'
                                                    : 'active' ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-sm <?= $userStatus === 'active'
                                                    ? 'btn-outline-danger'
                                                    : 'btn-outline-success' ?>"
                                            >
                                                <?= $userStatus === 'active'
                                                    ? 'غیرفعال کردن'
                                                    : 'فعال کردن' ?>
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>


                <!-- Pagination -->

                <?php if ($totalPages > 1): ?>

                    <nav class="mt-4">

                        <ul class="pagination justify-content-center">

                            <?php
                            $queryParams = [];

                            if ($search !== '') {
                                $queryParams['search'] = $search;
                            }

                            if ($role !== '') {
                                $queryParams['role'] = $role;
                            }

                            if ($status !== '') {
                                $queryParams['status'] = $status;
                            }
                            ?>


                            <?php if ($page > 1): ?>

                                <?php
                                $queryParams['page'] = $page - 1;

                                $previousUrl =
                                    $url('/admin/users')
                                    . '?'
                                    . http_build_query($queryParams);
                                ?>

                                <li class="page-item">

                                    <a
                                        class="page-link"
                                        href="<?= $e($previousUrl) ?>"
                                    >
                                        قبلی
                                    </a>

                                </li>

                            <?php endif; ?>


                            <?php for (
                                $i = 1;
                                $i <= $totalPages;
                                $i++
                            ): ?>

                                <?php
                                $queryParams['page'] = $i;

                                $pageUrl =
                                    $url('/admin/users')
                                    . '?'
                                    . http_build_query($queryParams);
                                ?>

                                <li
                                    class="page-item <?= $i === $page
                                        ? 'active'
                                        : '' ?>"
                                >

                                    <a
                                        class="page-link"
                                        href="<?= $e($pageUrl) ?>"
                                    >
                                        <?= $e($i) ?>
                                    </a>

                                </li>

                            <?php endfor; ?>


                            <?php if ($page < $totalPages): ?>

                                <?php
                                $queryParams['page'] = $page + 1;

                                $nextUrl =
                                    $url('/admin/users')
                                    . '?'
                                    . http_build_query($queryParams);
                                ?>

                                <li class="page-item">

                                    <a
                                        class="page-link"
                                        href="<?= $e($nextUrl) ?>"
                                    >
                                        بعدی
                                    </a>

                                </li>

                            <?php endif; ?>

                        </ul>

                    </nav>

                <?php endif; ?>

            <?php endif; ?>

        </div>

    </div>

</div>

</body>

</html>
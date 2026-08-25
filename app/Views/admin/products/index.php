<?php

declare(strict_types=1);

$e = static fn($value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$products = is_array($products ?? null)
    ? $products
    : [];

$csrfField = $csrfField ?? '';

$baseUrl = app_config('base_url', '');
?>
<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $e($title ?? 'مدیریت محصولات') ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">
                مدیریت محصولات
            </h1>

            <p class="text-muted mb-0">
                لیست محصولات فروشگاه
            </p>
        </div>

        <div class="d-flex gap-2">

            <a
                href="<?= $e($baseUrl) ?>/admin/products/create"
                class="btn btn-primary"
            >
                افزودن محصول
            </a>

            <a
                href="<?= $e($baseUrl) ?>/admin"
                class="btn btn-outline-secondary"
            >
                بازگشت به پنل
            </a>

        </div>

    </div>

    <?php if ($products === []): ?>

        <div class="card shadow-sm">
            <div class="card-body">

                <div class="alert alert-info mb-0">
                    هنوز محصولی ثبت نشده است.
                </div>

            </div>
        </div>

    <?php else: ?>

        <div class="card shadow-sm">

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle mb-0">

                        <thead class="table-dark">

                        <tr>
                            <th>#</th>
                            <th>نام محصول</th>
                            <th>دسته‌بندی</th>
                            <th>برند</th>
                            <th>قیمت</th>
                            <th>موجودی</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>

                        </thead>

                        <tbody>

                        <?php foreach ($products as $product): ?>

                            <?php
                            $productId = (int) $product['id'];
                            ?>

                            <tr>

                                <td>
                                    <?= $productId ?>
                                </td>

                                <td>
                                    <?= $e($product['name']) ?>
                                </td>

                                <td>
                                    <?= $e($product['category_name'] ?? '-') ?>
                                </td>

                                <td>
                                    <?= $e($product['brand_name'] ?? '-') ?>
                                </td>

                                <td>
                                    <?= number_format(
                                        (float) $product['price']
                                    ) ?>
                                </td>

                                <td>
                                    <?= (int) $product['stock'] ?>
                                </td>

                                <td>

                                    <?php if (
                                        ($product['status'] ?? null)
                                        === 'active'
                                    ): ?>

                                        <span class="badge bg-success">
                                            فعال
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            غیرفعال
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <div class="d-flex gap-2">

                                        <a
                                            href="<?= $e($baseUrl) ?>/admin/products/<?= $productId ?>/edit"
                                            class="btn btn-sm btn-warning"
                                        >
                                            ویرایش
                                        </a>

                                        <form
                                            method="POST"
                                            action="<?= $e(app_config('base_url', '')) ?>/admin/products/<?= (int) $product['id'] ?>/delete"
                                            onsubmit="return confirm('آیا از حذف این محصول مطمئن هستید؟');"
                                            class="d-inline"
                                        >
                                            <?= $csrfField ?>

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-danger"
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

            </div>

        </div>

    <?php endif; ?>

</div>

</body>

</html>
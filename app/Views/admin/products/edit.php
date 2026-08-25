<?php

declare(strict_types=1);

$e = static fn($value): string =>
    htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );

$product = is_array($product ?? null)
    ? $product
    : [];

$categories = is_array($categories ?? null)
    ? $categories
    : [];

$brands = is_array($brands ?? null)
    ? $brands
    : [];

$selectedCategory = (int) (
    $product['category_id'] ?? 0
);

$selectedBrand = $product['brand_id'] !== null
    ? (int) $product['brand_id']
    : 0;

$status = (string) (
    $product['status'] ?? 'active'
);

$featured = (int) (
    $product['featured'] ?? 0
);
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
        <?= $e($title ?? 'ویرایش محصول') ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h1 class="h3 mb-0">
            ویرایش محصول
        </h1>

        <a
            href="/arvand-audio-store/public/admin/products"
            class="btn btn-outline-secondary"
        >
            بازگشت
        </a>

    </div>

    <div class="card shadow-sm">

        <div class="card-body">

            <form
                method="POST"
                action="/arvand-audio-store/public/admin/products/<?= (int) $product['id'] ?>/update"
            >

                <?= $csrfField ?>

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            نام محصول
                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="<?= $e($product['name'] ?? '') ?>"
                            required
                        >

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            SKU
                        </label>

                        <input
                            type="text"
                            name="sku"
                            class="form-control"
                            value="<?= $e($product['sku'] ?? '') ?>"
                            required
                        >

                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Slug
                        </label>

                        <input
                            type="text"
                            name="slug"
                            class="form-control"
                            value="<?= $e($product['slug'] ?? '') ?>"
                            required
                        >

                    </div>

                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            دسته‌بندی
                        </label>

                        <select
                            name="category_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                انتخاب کنید
                            </option>

                            <?php foreach ($categories as $category): ?>

                                <option
                                    value="<?= (int) $category['id'] ?>"
                                    <?= (int) $category['id'] === $selectedCategory ? 'selected' : '' ?>
                                >
                                    <?= $e($category['name']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            برند
                        </label>

                        <select
                            name="brand_id"
                            class="form-select"
                        >

                            <option value="">
                                بدون برند
                            </option>

                            <?php foreach ($brands as $brand): ?>

                                <option
                                    value="<?= (int) $brand['id'] ?>"
                                    <?= (int) $brand['id'] === $selectedBrand ? 'selected' : '' ?>
                                >
                                    <?= $e($brand['name']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        توضیحات
                    </label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="5"
                    ><?= $e($product['description'] ?? '') ?></textarea>

                </div>

                <div class="row">

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            قیمت اصلی
                        </label>

                        <input
                            type="number"
                            name="price"
                            class="form-control"
                            min="0"
                            step="0.01"
                            value="<?= $e($product['price'] ?? '') ?>"
                            required
                        >

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            قیمت تخفیف
                        </label>

                        <input
                            type="number"
                            name="discount_price"
                            class="form-control"
                            min="0"
                            step="0.01"
                            value="<?= $product['discount_price'] !== null ? $e($product['discount_price']) : '' ?>"
                        >

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            موجودی
                        </label>

                        <input
                            type="number"
                            name="stock"
                            class="form-control"
                            min="0"
                            value="<?= (int) ($product['stock'] ?? 0) ?>"
                            required
                        >

                    </div>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        مسیر تصویر
                    </label>

                    <input
                        type="text"
                        name="image"
                        class="form-control"
                        value="<?= $e($product['image'] ?? '') ?>"
                        placeholder="/uploads/products/example.jpg"
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        وضعیت
                    </label>

                    <select
                        name="status"
                        class="form-select"
                    >

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

                <div class="form-check mb-4">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="featured"
                        value="1"
                        id="featured"
                        <?= $featured === 1 ? 'checked' : '' ?>
                    >

                    <label
                        class="form-check-label"
                        for="featured"
                    >
                        محصول ویژه
                    </label>

                </div>

                <div class="d-flex gap-2">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        ذخیره تغییرات
                    </button>

                    <a
                        href="/arvand-audio-store/public/admin/products"
                        class="btn btn-secondary"
                    >
                        انصراف
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

</body>

</html>
<?php

declare(strict_types=1);

$products = is_array($products ?? null)
    ? $products
    : [];

$categories = is_array($categories ?? null)
    ? $categories
    : [];

$brands = is_array($brands ?? null)
    ? $brands
    : [];

$search = (string) ($search ?? '');

$selectedCategory = (string) (
    $selectedCategory ?? ''
);

$selectedBrand = (string) (
    $selectedBrand ?? ''
);

$selectedMinPrice = $selectedMinPrice ?? null;

$selectedMaxPrice = $selectedMaxPrice ?? null;

$selectedSort = (string) (
    $selectedSort ?? 'newest'
);

$inStock = (bool) ($inStock ?? false);


/**
 * تبدیل قیمت به نمایش خوانا
 */
function productPrice(array $product): string
{
    $discountPrice = $product['discount_price'] ?? null;

    $price = (
        $discountPrice !== null
        && $discountPrice !== ''
        && (float) $discountPrice > 0
    )
        ? (float) $discountPrice
        : (float) ($product['price'] ?? 0);

    return number_format(
        $price,
        0,
        '.',
        ','
    );
}


/**
 * نمایش تصویر محصول
 */
function productImage(array $product): string
{
    return trim(
        (string) ($product['image'] ?? '')
    );
}

?>

<!DOCTYPE html>

<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars(
            $title ?? 'محصولات',
            ENT_QUOTES,
            'UTF-8'
        ) ?>
        | Arvand Audio Store
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background: #f8f9fa;
        }

        .product-card {
            transition:
                transform .2s ease,
                box-shadow .2s ease;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow:
                0 .5rem 1rem rgba(0, 0, 0, .12);
        }

        .product-image {
            height: 220px;
            object-fit: contain;
            background: #fff;
        }

        .product-placeholder {
            height: 220px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #f1f3f5;

            color: #6c757d;

            font-size: 3rem;
        }

        .price {
            font-size: 1.1rem;
            font-weight: 700;
        }

        .filter-card {
            position: sticky;
            top: 20px;
        }

    </style>

</head>

<body>

<!-- =========================================================
     Navigation
========================================================= -->

<nav class="navbar navbar-expand-lg bg-dark navbar-dark">

    <div class="container">

        <a
            class="navbar-brand fw-bold"
            href="/arvand-audio-store/public/"
        >
            Arvand Audio Store
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
        >

            <span class="navbar-toggler-icon"></span>

        </button>

        <div
            class="collapse navbar-collapse"
            id="mainNavbar"
        >

            <ul
                class="navbar-nav me-auto mb-2 mb-lg-0"
            >

                <li class="nav-item">

                    <a
                        class="nav-link active"
                        href="/arvand-audio-store/public/products"
                    >
                        محصولات
                    </a>

                </li>

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="/arvand-audio-store/public/"
                    >
                        خانه
                    </a>

                </li>

            </ul>

        </div>

    </div>

</nav>


<!-- =========================================================
     Main
========================================================= -->

<main class="container py-5">

    <div class="row mb-4">

        <div class="col">

            <h1 class="fw-bold">
                محصولات
            </h1>

            <p class="text-muted mb-0">
                محصولات صوتی حرفه‌ای Arvand Audio Store
            </p>

        </div>

    </div>


    <div class="row g-4">


        <!-- =================================================
             Filter Sidebar
        ================================================== -->

        <aside class="col-lg-3">

            <div
                class="card border-0 shadow-sm filter-card"
            >

                <div class="card-body">

                    <h5 class="fw-bold mb-4">
                        فیلتر محصولات
                    </h5>


                    <form
                        method="GET"
                        action="/arvand-audio-store/public/products"
                    >


                        <!-- Search -->

                        <div class="mb-3">

                            <label
                                for="search"
                                class="form-label"
                            >
                                جستجو
                            </label>

                            <input
                                type="search"
                                id="search"
                                name="search"
                                class="form-control"
                                value="<?= htmlspecialchars(
                                    $search,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                placeholder="نام محصول..."
                            >

                        </div>


                        <!-- Category -->

                        <div class="mb-3">

                            <label
                                for="category"
                                class="form-label"
                            >
                                دسته‌بندی
                            </label>

                            <select
                                id="category"
                                name="category"
                                class="form-select"
                            >

                                <option value="">
                                    همه دسته‌بندی‌ها
                                </option>

                                <?php foreach (
                                    $categories
                                    as $category
                                ): ?>

                                    <option
                                        value="<?= htmlspecialchars(
                                            $category['slug'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        <?= $selectedCategory
                                            === $category['slug']
                                            ? 'selected'
                                            : '' ?>
                                    >

                                        <?= htmlspecialchars(
                                            $category['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- Brand -->

                        <div class="mb-3">

                            <label
                                for="brand"
                                class="form-label"
                            >
                                برند
                            </label>

                            <select
                                id="brand"
                                name="brand"
                                class="form-select"
                            >

                                <option value="">
                                    همه برندها
                                </option>

                                <?php foreach (
                                    $brands
                                    as $brand
                                ): ?>

                                    <option
                                        value="<?= htmlspecialchars(
                                            $brand['slug'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        <?= $selectedBrand
                                            === $brand['slug']
                                            ? 'selected'
                                            : '' ?>
                                    >

                                        <?= htmlspecialchars(
                                            $brand['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- Minimum Price -->

                        <div class="mb-3">

                            <label
                                for="min_price"
                                class="form-label"
                            >
                                حداقل قیمت
                            </label>

                            <input
                                type="number"
                                id="min_price"
                                name="min_price"
                                class="form-control"
                                min="0"
                                step="1000"
                                value="<?= $selectedMinPrice !== null
                                    ? htmlspecialchars(
                                        (string) $selectedMinPrice,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    )
                                    : '' ?>"
                                placeholder="تومان"
                            >

                        </div>


                        <!-- Maximum Price -->

                        <div class="mb-3">

                            <label
                                for="max_price"
                                class="form-label"
                            >
                                حداکثر قیمت
                            </label>

                            <input
                                type="number"
                                id="max_price"
                                name="max_price"
                                class="form-control"
                                min="0"
                                step="1000"
                                value="<?= $selectedMaxPrice !== null
                                    ? htmlspecialchars(
                                        (string) $selectedMaxPrice,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    )
                                    : '' ?>"
                                placeholder="تومان"
                            >

                        </div>


                        <!-- Sort -->

                        <div class="mb-3">

                            <label
                                for="sort"
                                class="form-label"
                            >
                                مرتب‌سازی
                            </label>

                            <select
                                id="sort"
                                name="sort"
                                class="form-select"
                            >

                                <option
                                    value="newest"
                                    <?= $selectedSort === 'newest'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    جدیدترین
                                </option>

                                <option
                                    value="price_asc"
                                    <?= $selectedSort === 'price_asc'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    ارزان‌ترین
                                </option>

                                <option
                                    value="price_desc"
                                    <?= $selectedSort === 'price_desc'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    گران‌ترین
                                </option>

                                <option
                                    value="name_asc"
                                    <?= $selectedSort === 'name_asc'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    نام: الف تا ی
                                </option>

                                <option
                                    value="name_desc"
                                    <?= $selectedSort === 'name_desc'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    نام: ی تا الف
                                </option>

                            </select>

                        </div>


                        <!-- In Stock -->

                        <div class="form-check mb-4">

                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="in_stock"
                                name="in_stock"
                                value="1"
                                <?= $inStock
                                    ? 'checked'
                                    : '' ?>
                            >

                            <label
                                class="form-check-label"
                                for="in_stock"
                            >
                                فقط محصولات موجود
                            </label>

                        </div>


                        <!-- Buttons -->

                        <button
                            type="submit"
                            class="btn btn-dark w-100 mb-2"
                        >
                            اعمال فیلتر
                        </button>

                        <a
                            href="/arvand-audio-store/public/products"
                            class="btn btn-outline-secondary w-100"
                        >
                            پاک کردن فیلترها
                        </a>

                    </form>

                </div>

            </div>

        </aside>


        <!-- =================================================
             Products
        ================================================== -->

        <section class="col-lg-9">


            <!-- Result Header -->

            <div
                class="d-flex justify-content-between
                       align-items-center mb-3"
            >

                <div>

                    <?php if ($search !== ''): ?>

                        <span class="text-muted">
                            نتایج جستجو برای:
                        </span>

                        <strong>
                            <?= htmlspecialchars(
                                $search,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </strong>

                    <?php else: ?>

                        <span class="text-muted">
                            لیست محصولات
                        </span>

                    <?php endif; ?>

                </div>

                <span class="badge text-bg-secondary">
                    <?= count($products) ?>
                    محصول
                </span>

            </div>


            <!-- Products -->

            <?php if ($products === []): ?>

                <div class="alert alert-warning">

                    <h5 class="alert-heading">
                        محصولی پیدا نشد
                    </h5>

                    <p class="mb-0">
                        با تغییر فیلترها دوباره تلاش کنید.
                    </p>

                </div>

            <?php else: ?>

                <div class="row g-4">

                    <?php foreach (
                        $products
                        as $product
                    ): ?>

                        <div class="col-md-6 col-xl-4">

                            <div
                                class="card
                                       product-card
                                       h-100
                                       border-0
                                       shadow-sm"
                            >

                                <?php
                                $image = productImage(
                                    $product
                                );
                                ?>

                                <?php if ($image !== ''): ?>

                                    <img
                                        src="<?= htmlspecialchars(
                                            $image,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        class="card-img-top product-image"
                                        alt="<?= htmlspecialchars(
                                            $product['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >

                                <?php else: ?>

                                    <div
                                        class="product-placeholder"
                                    >
                                        🎧
                                    </div>

                                <?php endif; ?>


                                <div
                                    class="card-body
                                           d-flex
                                           flex-column"
                                >

                                    <?php if (
                                        !empty(
                                            $product['brand_name']
                                        )
                                    ): ?>

                                        <small
                                            class="text-muted mb-1"
                                        >
                                            <?= htmlspecialchars(
                                                $product['brand_name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </small>

                                    <?php endif; ?>


                                    <h5
                                        class="card-title"
                                    >
                                        <?= htmlspecialchars(
                                            $product['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </h5>


                                    <p
                                        class="card-text
                                               text-muted
                                               flex-grow-1"
                                    >
                                        <?= htmlspecialchars(
                                            (string) (
                                                $product['description']
                                                ?? ''
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </p>


                                    <div
                                        class="d-flex
                                               justify-content-between
                                               align-items-center"
                                    >

                                        <span class="price">

                                            <?= productPrice(
                                                $product
                                            ) ?>

                                            تومان

                                        </span>


                                        <?php if (
                                            (int) (
                                                $product['stock']
                                                ?? 0
                                            ) > 0
                                        ): ?>

                                            <span
                                                class="badge text-bg-success"
                                            >
                                                موجود
                                            </span>

                                        <?php else: ?>

                                            <span
                                                class="badge text-bg-danger"
                                            >
                                                ناموجود
                                            </span>

                                        <?php endif; ?>

                                    </div>


                                    <a
                                        href="/arvand-audio-store/public/products/<?= urlencode(
                                            $product['slug']
                                        ) ?>"
                                        class="btn btn-dark mt-3"
                                    >
                                        مشاهده محصول
                                    </a>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>

    </div>

</main>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>
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

function productImage(array $product): string
{
    $image = trim(
        (string) ($product['image'] ?? '')
    );

    if ($image !== '') {
        return htmlspecialchars(
            $image,
            ENT_QUOTES,
            'UTF-8'
        );
    }

    return '';
}

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
            transition: transform .2s ease,
                        box-shadow .2s ease;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .12);
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
    </style>
</head>

<body>

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
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

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

            <form
                class="d-flex"
                method="GET"
                action="/arvand-audio-store/public/products"
            >
                <input
                    class="form-control me-2"
                    type="search"
                    name="search"
                    value="<?= htmlspecialchars(
                        $search,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    placeholder="جستجوی محصول..."
                >

                <button
                    class="btn btn-outline-light"
                    type="submit"
                >
                    جستجو
                </button>
            </form>
        </div>
    </div>
</nav>

<main class="container py-5">

    <div class="row mb-4">

        <div class="col-md-8">
            <h1 class="fw-bold">
                <?= htmlspecialchars(
                    $title ?? 'محصولات',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </h1>

            <?php if ($search !== ''): ?>
                <p class="text-muted mb-0">
                    نتایج جستجو برای:
                    <strong>
                        <?= htmlspecialchars(
                            $search,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </strong>
                </p>
            <?php else: ?>
                <p class="text-muted mb-0">
                    محصولات صوتی فروشگاه
                </p>
            <?php endif; ?>
        </div>

    </div>

    <div class="row">

        <!-- Filters -->

        <aside class="col-lg-3 mb-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <h5 class="fw-bold mb-3">
                        دسته‌بندی‌ها
                    </h5>

                    <div class="list-group list-group-flush">

                        <?php foreach ($categories as $category): ?>

                            <a
                                class="list-group-item list-group-item-action"
                                href="/arvand-audio-store/public/categories/<?= urlencode($category['slug']) ?>"
                            >
                                <?= htmlspecialchars(
                                    $category['name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </a>

                        <?php endforeach; ?>

                    </div>

                    <hr>

                    <h5 class="fw-bold mb-3">
                        برندها
                    </h5>

                    <div class="list-group list-group-flush">

                        <?php foreach ($brands as $brand): ?>

                            <a
                                class="list-group-item list-group-item-action"
                                href="/arvand-audio-store/public/brands/<?= urlencode($brand['slug']) ?>"
                            >
                                <?= htmlspecialchars(
                                    $brand['name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </a>

                        <?php endforeach; ?>

                    </div>

                </div>

            </div>

        </aside>

        <!-- Products -->

        <section class="col-lg-9">

            <?php if ($products === []): ?>

                <div class="alert alert-warning">
                    محصولی برای نمایش پیدا نشد.
                </div>

            <?php else: ?>

                <div class="row g-4">

                    <?php foreach ($products as $product): ?>

                        <div class="col-md-6 col-xl-4">

                            <div class="card product-card h-100 border-0 shadow-sm">

                                <?php
                                $image = productImage($product);
                                ?>

                                <?php if ($image !== ''): ?>

                                    <img
                                        src="<?= $image ?>"
                                        class="card-img-top product-image"
                                        alt="<?= htmlspecialchars(
                                            $product['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >

                                <?php else: ?>

                                    <div class="product-placeholder">
                                        🎧
                                    </div>

                                <?php endif; ?>

                                <div class="card-body d-flex flex-column">

                                    <?php if (!empty($product['brand_name'])): ?>

                                        <small class="text-muted mb-1">
                                            <?= htmlspecialchars(
                                                $product['brand_name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </small>

                                    <?php endif; ?>

                                    <h5 class="card-title">
                                        <?= htmlspecialchars(
                                            $product['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </h5>

                                    <p class="card-text text-muted flex-grow-1">
                                        <?= htmlspecialchars(
                                            (string) ($product['description'] ?? ''),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </p>

                                    <div class="d-flex justify-content-between align-items-center">

                                        <span class="price">
                                            <?= productPrice($product) ?>
                                            تومان
                                        </span>

                                        <?php if ((int) $product['stock'] > 0): ?>

                                            <span class="badge text-bg-success">
                                                موجود
                                            </span>

                                        <?php else: ?>

                                            <span class="badge text-bg-danger">
                                                ناموجود
                                            </span>

                                        <?php endif; ?>

                                    </div>

                                    <a
                                        href="/arvand-audio-store/public/products/<?= urlencode($product['slug']) ?>"
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
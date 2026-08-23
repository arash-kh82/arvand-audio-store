<?php

declare(strict_types=1);

$products = is_array($products ?? null) ? $products : [];
$category = is_array($category ?? null) ? $category : [];

$categoryName = (string) ($category['name'] ?? 'دسته‌بندی');
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
            $categoryName,
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

        .product-image,
        .product-placeholder {
            height: 220px;
        }

        .product-image {
            width: 100%;
            object-fit: contain;
            background: #fff;
        }

        .product-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f3f5;
            font-size: 3rem;
        }

        .price {
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

        <a
            class="btn btn-outline-light"
            href="/arvand-audio-store/public/products"
        >
            همه محصولات
        </a>

    </div>

</nav>

<main class="container py-5">

    <div class="mb-5">

        <h1 class="fw-bold">
            <?= htmlspecialchars(
                $categoryName,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </h1>

        <?php if (!empty($category['description'])): ?>

            <p class="text-muted">
                <?= htmlspecialchars(
                    $category['description'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

        <?php else: ?>

            <p class="text-muted">
                محصولات موجود در این دسته‌بندی
            </p>

        <?php endif; ?>

    </div>

    <?php if ($products === []): ?>

        <div class="alert alert-warning">
            محصولی در این دسته‌بندی وجود ندارد.
        </div>

    <?php else: ?>

        <div class="row g-4">

            <?php foreach ($products as $product): ?>

                <?php
                $image = trim(
                    (string) ($product['image'] ?? '')
                );

                $price = (
                    !empty($product['discount_price'])
                    && (float) $product['discount_price'] > 0
                )
                    ? (float) $product['discount_price']
                    : (float) ($product['price'] ?? 0);
                ?>

                <div class="col-md-6 col-lg-4 col-xl-3">

                    <div class="card product-card h-100 border-0 shadow-sm">

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

                            <div class="product-placeholder">
                                🎧
                            </div>

                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">

                            <?php if (!empty($product['brand_name'])): ?>

                                <small class="text-muted">
                                    <?= htmlspecialchars(
                                        $product['brand_name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </small>

                            <?php endif; ?>

                            <h5 class="card-title mt-1">
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

                            <div class="mb-2">

                                <span class="price">
                                    <?= number_format(
                                        $price,
                                        0,
                                        '.',
                                        ','
                                    ) ?>
                                    تومان
                                </span>

                            </div>

                            <a
                                href="/arvand-audio-store/public/products/<?= urlencode($product['slug']) ?>"
                                class="btn btn-dark"
                            >
                                مشاهده محصول
                            </a>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</main>

</body>
</html>
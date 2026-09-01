<?php

declare(strict_types=1);

$products = is_array($products ?? null) ? $products : [];
$category = is_array($category ?? null) ? $category : [];

$e = static fn($value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);

$baseUrl = rtrim(
    (string) app_config('base_url', ''),
    '/'
);

$categoryName = (string) ($category['name'] ?? 'دسته‌بندی');
$categoryDescription = trim(
    (string) ($category['description'] ?? '')
);

// تابع کمکی برای ساخت URL تصویر
function categoryProductImageUrl(
    mixed $image,
    string $baseUrl
): ?string {
    $image = trim((string) $image);

    if ($image === '') {
        return null;
    }

    // اگر آدرس کامل باشد
    if (
        str_starts_with($image, 'http://')
        || str_starts_with($image, 'https://')
        || str_starts_with($image, '//')
    ) {
        return $image;
    }

    return $baseUrl . '/' . ltrim($image, '/');
}
?>

<!doctype html>
<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>
        <?= $e($categoryName) ?> | Arvand Audio Store
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background:
                linear-gradient(180deg,
                    #f8f9fa 0%,
                    #eef1f5 100%);
        }

        .navbar {
            box-shadow: 0 2px 12px rgba(0, 0, 0, .12);
        }

        .category-header {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .06);
        }

        .category-title {
            font-weight: 800;
            letter-spacing: -0.3px;
        }

        .category-count {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: #f1f3f5;
            color: #495057;
            border-radius: 999px;
            padding: 7px 14px;
            font-size: .85rem;
            margin-top: 14px;
        }

        .product-card {
            border: 1px solid #e9ecef;
            border-radius: 18px;
            overflow: hidden;
            background: #fff;
            transition:
                transform .2s ease,
                box-shadow .2s ease,
                border-color .2s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 14px 32px rgba(0, 0, 0, .10);
            border-color: #dee2e6;
        }

        .product-image-wrapper {
            height: 230px;
            background: #fff;
            overflow: hidden;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 14px;
            transition: transform .25s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.04);
        }

        .product-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f3f5;
            font-size: 3.5rem;
        }

        .brand-label {
            display: inline-block;
            color: #6c757d;
            font-size: .8rem;
            margin-bottom: 7px;
        }

        .product-name {
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1.7;
            min-height: 58px;
        }

        .product-description {
            color: #6c757d;
            font-size: .88rem;
            line-height: 1.8;

            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;

            min-height: 50px;
        }

        .price-box {
            margin-top: auto;
            padding-top: 12px;
        }

        .current-price {
            font-size: 1.05rem;
            font-weight: 800;
            color: #212529;
        }

        .old-price {
            color: #adb5bd;
            text-decoration: line-through;
            font-size: .82rem;
            margin-right: 6px;
        }

        .discount-badge {
            display: inline-block;
            background: #dc3545;
            color: #fff;
            border-radius: 8px;
            padding: 3px 7px;
            font-size: .72rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .product-btn {
            min-height: 44px;
            border-radius: 11px;
            font-weight: 600;
        }

        .empty-state {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 20px;
            padding: 60px 25px;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .05);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 18px;
        }

        .empty-state h2 {
            font-size: 1.3rem;
            font-weight: 700;
        }

        .empty-state p {
            color: #6c757d;
            margin-bottom: 24px;
        }

        .footer {
            color: #6c757d;
            font-size: .85rem;
            text-align: center;
            padding: 30px 0;
        }

        @media (max-width: 576px) {

            .category-header {
                padding: 22px;
                border-radius: 16px;
            }

            .product-image-wrapper {
                height: 210px;
            }

        }
    </style>

</head>

<body>

    <!-- =========================
     NAVBAR
========================= -->

    <nav class="navbar navbar-expand-lg bg-dark navbar-dark">

        <div class="container">

            <a
                class="navbar-brand fw-bold"
                href="<?= $baseUrl ?>/">
                🎧 Arvand Audio Store
            </a>

            <a
                class="btn btn-outline-light btn-sm"
                href="<?= $baseUrl ?>/products">
                همه محصولات
            </a>

        </div>

    </nav>


    <!-- =========================
     MAIN
========================= -->

    <main class="container py-4 py-md-5">

        <!-- Category Header -->

        <section class="category-header mb-4">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                <div>

                    <div class="text-muted small mb-2">
                        دسته‌بندی محصولات
                    </div>

                    <h1 class="category-title h2 mb-0">
                        <?= $e($categoryName) ?>
                    </h1>

                    <?php if ($categoryDescription !== ''): ?>

                        <p class="text-muted mt-3 mb-0">
                            <?= $e($categoryDescription) ?>
                        </p>

                    <?php else: ?>

                        <p class="text-muted mt-3 mb-0">
                            محصولات موجود در این دسته‌بندی
                        </p>

                    <?php endif; ?>

                    <div class="category-count">

                        <span>🎧</span>

                        <span>
                            <?= number_format(count($products)) ?>
                            محصول
                        </span>

                    </div>

                </div>


                <div>

                    <a
                        href="<?= $baseUrl ?>/products"
                        class="btn btn-outline-dark">
                        ← مشاهده همه محصولات
                    </a>

                </div>

            </div>

        </section>


        <!-- =========================
         PRODUCTS
    ========================= -->

        <?php if ($products === []): ?>

            <section class="empty-state">

                <div class="empty-icon">
                    🎧
                </div>

                <h2>
                    محصولی در این دسته‌بندی پیدا نشد
                </h2>

                <p>
                    در حال حاضر محصول فعالی در دسته‌بندی
                    «<?= $e($categoryName) ?>»
                    وجود ندارد.
                </p>

                <a
                    href="<?= $baseUrl ?>/products"
                    class="btn btn-dark">
                    مشاهده همه محصولات
                </a>

            </section>

        <?php else: ?>

            <div class="row g-4">

                <?php foreach ($products as $product): ?>

                    <?php

                    $image = categoryProductImageUrl(
                        $product['image'] ?? '',
                        $baseUrl
                    );

                    $productName = (string) (
                        $product['name'] ?? 'محصول'
                    );

                    $description = trim(
                        (string) ($product['description'] ?? '')
                    );

                    $regularPrice = (float) (
                        $product['price'] ?? 0
                    );

                    $discountPrice = (
                        isset($product['discount_price'])
                        && (float) $product['discount_price'] > 0
                    )
                        ? (float) $product['discount_price']
                        : null;

                    $hasDiscount = (
                        $discountPrice !== null
                        && $discountPrice < $regularPrice
                    );

                    $finalPrice = $hasDiscount
                        ? $discountPrice
                        : $regularPrice;

                    $slug = rawurlencode(
                        (string) ($product['slug'] ?? '')
                    );

                    ?>

                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">

                        <article class="card product-card h-100">

                            <!-- Product Image -->

                            <div class="product-image-wrapper">

                                <?php if ($image !== null): ?>

                                    <img
                                        src="<?= $e($image) ?>"
                                        class="product-image"
                                        alt="<?= $e($productName) ?>"
                                        loading="lazy">

                                <?php else: ?>

                                    <div
                                        class="product-placeholder"
                                        aria-label="تصویر محصول موجود نیست">
                                        🎧
                                    </div>

                                <?php endif; ?>

                            </div>


                            <!-- Product Content -->

                            <div class="card-body d-flex flex-column">

                                <?php if (!empty($product['brand_name'])): ?>

                                    <span class="brand-label">
                                        <?= $e($product['brand_name']) ?>
                                    </span>

                                <?php endif; ?>


                                <h2 class="product-name mb-2">
                                    <?= $e($productName) ?>
                                </h2>


                                <?php if ($description !== ''): ?>

                                    <p class="product-description mb-3">
                                        <?= $e($description) ?>
                                    </p>

                                <?php else: ?>

                                    <p class="product-description mb-3">
                                        اطلاعاتی برای این محصول ثبت نشده است.
                                    </p>

                                <?php endif; ?>


                                <!-- Price -->

                                <div class="price-box">

                                    <?php if ($hasDiscount): ?>

                                        <div>

                                            <span class="discount-badge">
                                                تخفیف
                                            </span>

                                        </div>

                                        <div>

                                            <span class="current-price">
                                                <?= number_format(
                                                    $finalPrice,
                                                    0,
                                                    '.',
                                                    ','
                                                ) ?>
                                                تومان
                                            </span>

                                            <span class="old-price">
                                                <?= number_format(
                                                    $regularPrice,
                                                    0,
                                                    '.',
                                                    ','
                                                ) ?>
                                                تومان
                                            </span>

                                        </div>

                                    <?php else: ?>

                                        <div class="current-price">

                                            <?= number_format(
                                                $finalPrice,
                                                0,
                                                '.',
                                                ','
                                            ) ?>

                                            تومان

                                        </div>

                                    <?php endif; ?>

                                </div>


                                <!-- Product Details -->

                                <div class="d-grid mt-3">

                                    <a
                                        href="<?= $baseUrl ?>/products/<?= $slug ?>"
                                        class="btn btn-dark product-btn">
                                        مشاهده محصول
                                    </a>

                                </div>

                            </div>

                        </article>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </main>


    <!-- =========================
     FOOTER
========================= -->

    <footer class="footer">

        <div class="container">

            <div>
                © <?= date('Y') ?> Arvand Audio Store
            </div>

            <div class="mt-1">
                فروشگاه تخصصی تجهیزات صوتی
            </div>

        </div>

    </footer>

</body>

</html>
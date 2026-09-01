<?php

declare(strict_types=1);

$products = is_array($products ?? null) ? $products : [];
$brand = is_array($brand ?? null) ? $brand : [];

$e = static fn($value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);

$baseUrl = rtrim(
    (string) app_config('base_url', ''),
    '/'
);

$brandName = (string) ($brand['name'] ?? 'برند');

// تابع کمکی برای ساخت URL تصویر
function brandProductImageUrl(
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
        <?= $e($brandName) ?> | Arvand Audio Store
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet">

    <style>
        body {
            background:
                linear-gradient(180deg,
                    #f8f9fa 0%,
                    #eef1f5 100%);
            min-height: 100vh;
        }

        .navbar {
            box-shadow: 0 2px 12px rgba(0, 0, 0, .12);
        }

        .brand-header {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .06);
            border: 1px solid #e9ecef;
        }

        .brand-title {
            font-weight: 800;
            letter-spacing: -0.3px;
        }

        .brand-count {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f1f3f5;
            color: #495057;
            border-radius: 999px;
            padding: 7px 14px;
            font-size: .85rem;
            margin-top: 12px;
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
            position: relative;
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

        .category-label {
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
            border-radius: 11px;
            min-height: 44px;
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
            padding: 30px 0;
            text-align: center;
        }

        @media (max-width: 576px) {

            .brand-header {
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

        <!-- Brand Header -->

        <section class="brand-header mb-4">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                <div>

                    <div class="text-muted small mb-2">
                        برند محصولات
                    </div>

                    <h1 class="brand-title h2 mb-0">
                        <?= $e($brandName) ?>
                    </h1>

                    <div class="brand-count">
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
                    محصولی از این برند پیدا نشد
                </h2>

                <p>
                    در حال حاضر محصول فعالی از برند
                    «<?= $e($brandName) ?>»
                    در فروشگاه وجود ندارد.
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

                    $image = brandProductImageUrl(
                        $product['image'] ?? '',
                        $baseUrl
                    );

                    $productName = (string) (
                        $product['name'] ?? 'محصول'
                    );

                    $description = (string) (
                        $product['description'] ?? ''
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

                            <!-- Image -->

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


                            <!-- Content -->

                            <div class="card-body d-flex flex-column">

                                <?php if (!empty($product['category_name'])): ?>

                                    <span class="category-label">
                                        <?= $e($product['category_name']) ?>
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


                                <!-- Product Link -->

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
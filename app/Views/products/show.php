<?php

declare(strict_types=1);

if (!isset($product) || !is_array($product)) {
    http_response_code(404);
    exit('محصول پیدا نشد.');
}

$name = (string) ($product['name'] ?? '');
$description = (string) ($product['description'] ?? '');
$image = trim((string) ($product['image'] ?? ''));

$discountPrice = $product['discount_price'] ?? null;

$finalPrice = (
    $discountPrice !== null
    && $discountPrice !== ''
    && (float) $discountPrice > 0
)
    ? (float) $discountPrice
    : (float) ($product['price'] ?? 0);

$stock = (int) ($product['stock'] ?? 0);
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
            $name,
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

        .product-detail-image {
            width: 100%;
            height: 450px;
            object-fit: contain;
            background: #fff;
        }

        .placeholder {
            width: 100%;
            height: 450px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            font-size: 6rem;
        }

        .price {
            font-size: 1.8rem;
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
            بازگشت به محصولات
        </a>

    </div>

</nav>

<main class="container py-5">

    <div class="card border-0 shadow-sm">

        <div class="card-body p-4">

            <div class="row g-5">

                <div class="col-lg-6">

                    <?php if ($image !== ''): ?>

                        <img
                            src="<?= htmlspecialchars(
                                $image,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            class="product-detail-image rounded"
                            alt="<?= htmlspecialchars(
                                $name,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                    <?php else: ?>

                        <div class="placeholder rounded">
                            🎧
                        </div>

                    <?php endif; ?>

                </div>

                <div class="col-lg-6">

                    <?php if (!empty($product['brand_name'])): ?>

                        <div class="text-muted mb-2">
                            برند:
                            <strong>
                                <?= htmlspecialchars(
                                    $product['brand_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </strong>
                        </div>

                    <?php endif; ?>

                    <h1 class="fw-bold mb-3">
                        <?= htmlspecialchars(
                            $name,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </h1>

                    <?php if (!empty($product['category_name'])): ?>

                        <div class="mb-3">

                            دسته‌بندی:

                            <a
                                href="/arvand-audio-store/public/categories/<?= urlencode($product['category_slug']) ?>"
                            >
                                <?= htmlspecialchars(
                                    $product['category_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </a>

                        </div>

                    <?php endif; ?>

                    <hr>

                    <div class="mb-4">

                        <span class="price">
                            <?= number_format(
                                $finalPrice,
                                0,
                                '.',
                                ','
                            ) ?>
                            تومان
                        </span>

                    </div>

                    <div class="mb-4">

                        <?php if ($stock > 0): ?>

                            <span class="badge text-bg-success">
                                موجود در انبار
                            </span>

                            <span class="text-muted ms-2">
                                تعداد موجود:
                                <?= $stock ?>
                            </span>

                        <?php else: ?>

                            <span class="badge text-bg-danger">
                                ناموجود
                            </span>

                        <?php endif; ?>

                    </div>

                    <?php if ($description !== ''): ?>

                        <h5 class="fw-bold">
                            توضیحات محصول
                        </h5>

                        <p class="text-muted">
                            <?= nl2br(
                                htmlspecialchars(
                                    $description,
                                    ENT_QUOTES,
                                    'UTF-8'
                                )
                            ) ?>
                        </p>

                    <?php endif; ?>

                    <div class="mt-4">

                        <?php if ($stock > 0): ?>

                            <button
                                type="button"
                                class="btn btn-dark btn-lg"
                                disabled
                            >
                                افزودن به سبد خرید
                            </button>

                            <div class="form-text mt-2">
                                بخش سبد خرید در مرحله بعد فعال می‌شود.
                            </div>

                        <?php else: ?>

                            <button
                                type="button"
                                class="btn btn-secondary btn-lg"
                                disabled
                            >
                                محصول ناموجود است
                            </button>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    </div>

</main>

</body>

</html>
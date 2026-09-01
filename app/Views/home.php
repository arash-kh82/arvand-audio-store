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

$baseUrl = rtrim(
    (string) app_config('base_url', ''),
    '/'
);


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function homeProductPrice(array $product): string
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


function homeImageUrl(
    mixed $image,
    string $baseUrl
): ?string {
    $image = trim((string) $image);

    if ($image === '') {
        return null;
    }

    /*
     * اگر آدرس کامل باشد
     */
    if (
        str_starts_with($image, 'http://')
        || str_starts_with($image, 'https://')
        || str_starts_with($image, '//')
    ) {
        return $image;
    }

    return $baseUrl
        . '/'
        . ltrim($image, '/');
}


function homeProductImage(
    array $product,
    string $baseUrl
): ?string {
    return homeImageUrl(
        $product['image'] ?? '',
        $baseUrl
    );
}


function homeCategoryImage(
    array $category,
    string $baseUrl
): ?string {
    return homeImageUrl(
        $category['image'] ?? '',
        $baseUrl
    );
}


function homeBrandLogo(
    array $brand,
    string $baseUrl
): ?string {
    return homeImageUrl(
        $brand['logo'] ?? '',
        $baseUrl
    );
}

?>

<!DOCTYPE html>

<html
    lang="fa"
    dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars(
            $title ?? 'فروشگاه صوتی آروند',
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </title>

    <meta
        name="description"
        content="فروشگاه تخصصی تجهیزات صوتی، استودیویی، میکروفون، هدفون و اسپیکر">

    <link
        rel="stylesheet"
        href="<?= $baseUrl ?>/assets/css/app.css">

</head>

<body>


    <!-- =========================================================
     Navigation
========================================================= -->

    <nav class="aa-navbar">

        <div class="aa-container">

            <div class="aa-navbar-inner">

                <!-- Brand -->

                <a
                    href="<?= $baseUrl ?>"
                    class="aa-brand">

                    <span class="aa-brand-icon">
                        🎧
                    </span>

                    <span>
                        آروند Audio
                    </span>

                </a>


                <!-- Navigation -->

                <nav class="aa-nav">

                    <a
                        href="<?= $baseUrl ?>">
                        خانه
                    </a>

                    <a
                        href="<?= $baseUrl ?>/products">
                        محصولات
                    </a>


                    <?php if (
                        class_exists('App\Core\Auth')
                        && App\Core\Auth::check()
                    ): ?>

                        <a
                            href="<?= $baseUrl ?>/account">
                            حساب کاربری
                        </a>

                    <?php else: ?>

                        <a
                            href="<?= $baseUrl ?>/login">
                            ورود
                        </a>

                    <?php endif; ?>

                </nav>


                <!-- Actions -->

                <div class="aa-nav-actions">

                    <a
                        href="<?= $baseUrl ?>/products"
                        class="aa-icon-btn"
                        title="جستجوی محصولات"
                        aria-label="جستجوی محصولات">
                        🔍
                    </a>

                    <a
                        href="<?= $baseUrl ?>/cart"
                        class="aa-icon-btn"
                        title="سبد خرید"
                        aria-label="سبد خرید">
                        🛒
                    </a>

                </div>

            </div>

        </div>

    </nav>


    <!-- =========================================================
     Hero
========================================================= -->

    <header class="aa-hero">

        <div class="aa-container">

            <div class="aa-hero-inner">

                <div class="aa-hero-content">

                    <span class="aa-hero-kicker">
                        فروشگاه تخصصی تجهیزات صوتی
                    </span>

                    <h1>
                        صدای بهتر،
                        <br>
                        تجربه‌ای متفاوت
                    </h1>

                    <p>
                        مجموعه‌ای از تجهیزات صوتی و استودیویی
                        برای کسانی که کیفیت صدا برایشان اهمیت دارد.
                    </p>

                    <div class="aa-hero-actions">

                        <a
                            href="<?= $baseUrl ?>/products"
                            class="aa-btn aa-btn-primary">
                            مشاهده محصولات
                            <span>←</span>
                        </a>

                        <a
                            href="#featured-products"
                            class="aa-btn aa-btn-dark">
                            محصولات ویژه
                        </a>

                    </div>

                </div>


                <div class="aa-hero-visual">

                    <div class="aa-hero-circle">
                        🎧
                    </div>

                </div>

            </div>

        </div>

    </header>


    <!-- =========================================================
     Main
========================================================= -->

    <main>
        <!-- =====================================================
         Featured Products
    ====================================================== -->

        <section
            id="featured-products"
            class="aa-section">

            <div class="aa-container">

                <div class="aa-section-header">

                    <div>

                        <h2 class="aa-section-title">
                            محصولات ویژه
                        </h2>

                        <p class="aa-section-subtitle">
                            انتخابی از محصولات محبوب فروشگاه
                        </p>

                    </div>

                    <a
                        href="<?= $baseUrl ?>/products"
                        class="aa-btn aa-btn-outline">
                        مشاهده همه
                    </a>

                </div>


                <?php if ($products !== []): ?>

                    <div
                        style="
                        display:grid;
                        grid-template-columns:
                            repeat(3, minmax(0, 1fr));
                        gap:20px;
                    ">

                        <?php foreach ($products as $product): ?>

                            <?php

                            $productName = (string) (
                                $product['name']
                                ?? $product['title']
                                ?? 'محصول صوتی'
                            );

                            $productSlug = (string) (
                                $product['slug']
                                ?? ''
                            );

                            $productImage =
                                homeProductImage(
                                    $product,
                                    $baseUrl
                                );

                            $stock = (int) (
                                $product['stock']
                                ?? 0
                            );

                            ?>

                            <article
                                class="aa-product-card">

                                <?php if (
                                    $productImage !== null
                                ): ?>

                                    <img
                                        src="<?= htmlspecialchars(
                                                    $productImage,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                        alt="<?= htmlspecialchars(
                                                    $productName,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                        class="aa-product-image"
                                        loading="lazy">

                                <?php else: ?>

                                    <div
                                        class="aa-product-placeholder">
                                        🎧
                                    </div>

                                <?php endif; ?>


                                <div class="aa-product-body">

                                    <?php if (
                                        !empty($product['brand_name'])
                                    ): ?>

                                        <div
                                            class="aa-product-brand">
                                            <?= htmlspecialchars(
                                                (string) $product['brand_name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </div>

                                    <?php endif; ?>


                                    <h3
                                        class="aa-product-title">
                                        <?= htmlspecialchars(
                                            $productName,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </h3>


                                    <p
                                        class="aa-product-description">
                                        <?= htmlspecialchars(
                                            (string) (
                                                $product['description']
                                                ?? (
                                                    $product['category_name']
                                                    ?? 'تجهیزات صوتی'
                                                )
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </p>


                                    <div
                                        class="aa-product-bottom">

                                        <div class="aa-price">

                                            <?= homeProductPrice(
                                                $product
                                            ) ?>

                                            <span>
                                                تومان
                                            </span>

                                        </div>


                                        <?php if (
                                            $stock > 0
                                        ): ?>

                                            <span
                                                class="aa-alert-success"
                                                style="
                                                padding:
                                                    4px 9px;
                                                border-radius:
                                                    999px;
                                                font-size:
                                                    .72rem;
                                            ">
                                                موجود
                                            </span>

                                        <?php else: ?>

                                            <span
                                                class="aa-alert-danger"
                                                style="
                                                padding:
                                                    4px 9px;
                                                border-radius:
                                                    999px;
                                                font-size:
                                                    .72rem;
                                            ">
                                                ناموجود
                                            </span>

                                        <?php endif; ?>

                                    </div>


                                    <?php if (
                                        $productSlug !== ''
                                    ): ?>

                                        <a
                                            href="<?= $baseUrl ?>/products/<?= rawurlencode(
                                                                                $productSlug
                                                                            ) ?>"
                                            class="aa-btn aa-btn-dark"
                                            style="
                                            width:100%;
                                            margin-top:16px;
                                        ">
                                            مشاهده محصول
                                        </a>

                                    <?php else: ?>

                                        <a
                                            href="<?= $baseUrl ?>/products"
                                            class="aa-btn aa-btn-dark"
                                            style="
                                            width:100%;
                                            margin-top:16px;
                                        ">
                                            مشاهده محصولات
                                        </a>

                                    <?php endif; ?>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <div
                        class="aa-alert aa-alert-warning">
                        هنوز محصولی برای نمایش وجود ندارد.
                    </div>

                <?php endif; ?>

            </div>

        </section>

        <!-- =====================================================
         Real Categories
    ====================================================== -->

        <section class="aa-section">

            <div class="aa-container">

                <div class="aa-section-header">

                    <div>

                        <h2 class="aa-section-title">
                            دسته‌بندی محصولات
                        </h2>

                        <p class="aa-section-subtitle">
                            انتخاب محصول بر اساس دسته‌بندی
                        </p>

                    </div>


                </div>


                <?php if ($categories !== []): ?>

                    <div class="aa-features">

                        <?php foreach ($categories as $category): ?>

                            <?php

                            $categoryName = (string) (
                                $category['name']
                                ?? 'دسته‌بندی'
                            );

                            $categorySlug = trim(
                                (string) (
                                    $category['slug']
                                    ?? ''
                                )
                            );

                            $categoryDescription = trim(
                                (string) (
                                    $category['description']
                                    ?? ''
                                )
                            );

                            $categoryImage =
                                homeCategoryImage(
                                    $category,
                                    $baseUrl
                                );

                            /*
                         * اگر slug وجود نداشت،
                         * لینک همه محصولات را نمایش می‌دهیم.
                         */
                            $categoryUrl = $categorySlug !== ''
                                ? $baseUrl
                                . '/categories/'
                                . rawurlencode($categorySlug)
                                : $baseUrl . '/products';

                            ?>

                            <a
                                href="<?= htmlspecialchars(
                                            $categoryUrl,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                class="aa-feature">

                                <?php if (
                                    $categoryImage !== null
                                ): ?>

                                    <div
                                        class="aa-feature-icon"
                                        style="
                                        overflow:hidden;
                                        display:flex;
                                        align-items:center;
                                        justify-content:center;
                                    ">

                                        <img
                                            src="<?= htmlspecialchars(
                                                        $categoryImage,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>"
                                            alt="<?= htmlspecialchars(
                                                        $categoryName,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>"
                                            style="
                                            width:100%;
                                            height:100%;
                                            object-fit:contain;
                                        "
                                            loading="lazy">

                                    </div>

                                <?php else: ?>

                                    <div class="aa-feature-icon">
                                        🎧
                                    </div>

                                <?php endif; ?>


                                <h3>
                                    <?= htmlspecialchars(
                                        $categoryName,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </h3>


                                <?php if (
                                    $categoryDescription !== ''
                                ): ?>

                                    <p>
                                        <?= htmlspecialchars(
                                            $categoryDescription,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </p>

                                <?php else: ?>

                                    <p>
                                        مشاهده محصولات این دسته‌بندی
                                    </p>

                                <?php endif; ?>

                            </a>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <div class="aa-alert aa-alert-warning">

                        هنوز دسته‌بندی فعالی برای نمایش وجود ندارد.

                    </div>

                <?php endif; ?>

            </div>

        </section>


        <!-- =====================================================
         Real Brands
    ====================================================== -->

        <section class="aa-section">

            <div class="aa-container">

                <div class="aa-section-header">

                    <div>

                        <h2 class="aa-section-title">
                            برندهای محصولات
                        </h2>

                        <p class="aa-section-subtitle">
                            محصولات را بر اساس برند موردنظر خود انتخاب کنید
                        </p>

                    </div>



                </div>


                <?php if ($brands !== []): ?>

                    <div class="aa-features">

                        <?php foreach ($brands as $brand): ?>

                            <?php

                            $brandName = (string) (
                                $brand['name']
                                ?? 'برند'
                            );

                            $brandSlug = trim(
                                (string) (
                                    $brand['slug']
                                    ?? ''
                                )
                            );

                            $brandLogo =
                                homeBrandLogo(
                                    $brand,
                                    $baseUrl
                                );

                            $brandUrl = $brandSlug !== ''
                                ? $baseUrl
                                . '/brands/'
                                . rawurlencode($brandSlug)
                                : $baseUrl . '/products';

                            ?>

                            <a
                                href="<?= htmlspecialchars(
                                            $brandUrl,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                class="aa-feature">

                                <?php if (
                                    $brandLogo !== null
                                ): ?>

                                    <div
                                        class="aa-feature-icon"
                                        style="
                                        overflow:hidden;
                                        display:flex;
                                        align-items:center;
                                        justify-content:center;
                                        background:#fff;
                                    ">

                                        <img
                                            src="<?= htmlspecialchars(
                                                        $brandLogo,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>"
                                            alt="<?= htmlspecialchars(
                                                        $brandName,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>"
                                            style="
                                            width:100%;
                                            height:100%;
                                            object-fit:contain;
                                        "
                                            loading="lazy">

                                    </div>

                                <?php else: ?>

                                    <div class="aa-feature-icon">
                                        🔊
                                    </div>

                                <?php endif; ?>


                                <h3>
                                    <?= htmlspecialchars(
                                        $brandName,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </h3>


                                <p>
                                    مشاهده محصولات
                                    <?= htmlspecialchars(
                                        $brandName,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </p>

                            </a>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <div class="aa-alert aa-alert-warning">

                        هنوز برند فعالی برای نمایش وجود ندارد.

                    </div>

                <?php endif; ?>

            </div>

        </section>





        <!-- =====================================================
         Why Arvand
    ====================================================== -->

        <section class="aa-section">

            <div class="aa-container">

                <div class="aa-section-header">

                    <div>

                        <h2 class="aa-section-title">
                            چرا آروند؟
                        </h2>

                        <p class="aa-section-subtitle">
                            تجربه خرید ساده، سریع و مطمئن
                        </p>

                    </div>

                </div>


                <div class="aa-features">

                    <div class="aa-feature">

                        <div class="aa-feature-icon">
                            🚚
                        </div>

                        <h3>
                            ارسال سریع
                        </h3>

                        <p>
                            سفارش شما با سرعت و دقت
                            آماده ارسال می‌شود.
                        </p>

                    </div>


                    <div class="aa-feature">

                        <div class="aa-feature-icon">
                            🔒
                        </div>

                        <h3>
                            خرید امن
                        </h3>

                        <p>
                            اطلاعات و سفارش‌های شما
                            با امنیت مدیریت می‌شوند.
                        </p>

                    </div>


                    <div class="aa-feature">

                        <div class="aa-feature-icon">
                            ✓
                        </div>

                        <h3>
                            محصولات تخصصی
                        </h3>

                        <p>
                            تمرکز فروشگاه روی تجهیزات
                            صوتی و استودیویی است.
                        </p>

                    </div>


                    <div class="aa-feature">

                        <div class="aa-feature-icon">
                            🤖
                        </div>

                        <h3>
                            خرید از Telegram
                        </h3>

                        <p>
                            امکان جستجو و ثبت سفارش
                            از طریق ربات تلگرام.
                        </p>

                    </div>

                </div>

            </div>

        </section>

    </main>


    <!-- =========================================================
     Footer
========================================================= -->

    <footer class="aa-footer">

        <div class="aa-container">

            <div class="aa-footer-grid">

                <div>

                    <h3>
                        🎧 آروند Audio
                    </h3>

                    <p>
                        فروشگاه تخصصی تجهیزات صوتی و
                        استودیویی با هدف ارائه تجربه‌ای
                        ساده و حرفه‌ای برای خرید تجهیزات صدا.
                    </p>

                </div>


                <div>

                    <h4>
                        دسترسی سریع
                    </h4>

                    <div class="aa-footer-links">

                        <a
                            href="<?= $baseUrl ?>">
                            خانه
                        </a>

                        <a
                            href="<?= $baseUrl ?>/products">
                            محصولات
                        </a>

                        <a
                            href="<?= $baseUrl ?>/cart">
                            سبد خرید
                        </a>

                        <a
                            href="<?= $baseUrl ?>/account">
                            حساب کاربری
                        </a>

                    </div>

                </div>


                <div>

                    <h4>
                        درباره فروشگاه
                    </h4>

                    <p>
                        Arvand Audio Store
                    </p>

                    <p>
                        پروژه فروشگاهی PHP با معماری MVC
                    </p>

                    <p>
                        © 2026 تمامی حقوق محفوظ است.
                    </p>

                </div>

            </div>


            <div class="aa-footer-bottom">

                © 2026 Arvand Audio Store
                — طراحی و توسعه با PHP MVC

            </div>

        </div>

    </footer>


</body>

</html>
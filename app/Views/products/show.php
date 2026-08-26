<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Session;
use App\Core\Csrf;

$product = is_array($product ?? null)
    ? $product
    : [];

$success = Session::flash('success');
$error = Session::flash('error');

$name = (string) ($product['name'] ?? '');
$description = (string) ($product['description'] ?? '');
$price = (float) ($product['price'] ?? 0);

$discountPrice = $product['discount_price'] !== null
    ? (float) $product['discount_price']
    : null;

$stock = (int) ($product['stock'] ?? 0);
$brandName = (string) ($product['brand_name'] ?? '');
$categoryName = (string) ($product['category_name'] ?? '');
$sku = (string) ($product['sku'] ?? '');

$baseUrl = rtrim(
    (string) app_config('base_url', ''),
    '/'
);

$images = is_array($images ?? null)
    ? $images
    : [];

$galleryImages = [];

foreach ($images as $image) {

    $galleryImages[] = [
        'url' => $baseUrl
            . '/'
            . ltrim(
                (string) $image['image'],
                '/'
            ),
        'alt' => (string) (
            $image['alt_text']
            ?? $name
        ),
    ];
}

if (
    $galleryImages === []
    && !empty($product['image'])
) {
    $galleryImages[] = [
        'url' => $baseUrl
            . '/'
            . ltrim(
                (string) $product['image'],
                '/'
            ),
        'alt' => $name,
    ];
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
            (string) ($title ?? $name),
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </title>

    <style>
        body {
            font-family: Tahoma, Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 30px;
            color: #222;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .product {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
        }

        h1 {
            margin-top: 0;
        }

        .meta {
            color: #666;
            margin: 8px 0;
        }

        .description {
            margin: 25px 0;
            line-height: 2;
        }

        .price {
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
        }

        .old-price {
            text-decoration: line-through;
            color: #888;
            font-size: 17px;
            margin-left: 10px;
        }

        .stock {
            margin-bottom: 20px;
        }

        .available {
            color: #16803c;
        }

        .unavailable {
            color: #b42318;
        }

        .cart-form {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        input[type="number"] {
            width: 80px;
            padding: 10px;
            text-align: center;
        }

        button {
            border: 0;
            background: #222;
            color: #fff;
            padding: 11px 20px;
            border-radius: 6px;
            cursor: pointer;
        }

        button:disabled {
            background: #999;
            cursor: not-allowed;
        }

        .message {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .success {
            background: #dff5e3;
            color: #176b2c;
        }

        .error {
            background: #fde2e2;
            color: #9b1c1c;
        }

        .login-message {
            margin-top: 20px;
            padding: 15px;
            background: #f1f1f1;
            border-radius: 6px;
        }

        a {
            color: #222;
        }

        /* Gallery */
        .product-gallery {
            margin-bottom: 30px;
        }

        .product-main-image {
            width: 100%;
            height: 420px;
            object-fit: contain;
            background: #f8f9fa;
            border-radius: 10px;
            display: block;
        }

        .product-thumbnails {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .product-thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border: 2px solid transparent;
            border-radius: 8px;
            cursor: pointer;
            background: #f8f9fa;
        }

        .product-thumbnail.active {
            border-color: #222;
        }

        /* Modal */
        .cart-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 20px;
        }

        .cart-modal.show {
            display: flex;
        }

        .cart-modal-box {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 14px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
        }

        .cart-modal-icon {
            font-size: 42px;
            margin-bottom: 10px;
        }

        .cart-modal-title {
            font-size: 21px;
            font-weight: bold;
            margin-bottom: 12px;
        }

        .cart-modal-message {
            color: #555;
            line-height: 1.9;
            margin-bottom: 20px;
        }

        .cart-modal-button {
            min-width: 120px;
        }

        .cart-modal.success .cart-modal-title {
            color: #176b2c;
        }

        .cart-modal.error .cart-modal-title {
            color: #b42318;
        }

        .cart-link {
            display: inline-block;
            margin-top: 15px;
        }

        .cart-count {
            display: inline-block;
            min-width: 20px;
            padding: 2px 6px;
            margin-right: 4px;
            border-radius: 10px;
            background: #222;
            color: #fff;
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>

<body>

<div class="container">

    <?php if ($success !== null): ?>

        <div class="message success">
            <?= htmlspecialchars(
                (string) $success,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </div>

    <?php endif; ?>

    <?php if ($error !== null): ?>

        <div class="message error">
            <?= htmlspecialchars(
                (string) $error,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </div>

    <?php endif; ?>

    <div class="product">

        <!-- Gallery -->
        <?php if ($galleryImages !== []): ?>

            <div class="product-gallery">

                <img
                    src="<?= htmlspecialchars(
                        $galleryImages[0]['url'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    alt="<?= htmlspecialchars(
                        $galleryImages[0]['alt'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    class="product-main-image"
                    id="productMainImage"
                >

                <?php if (count($galleryImages) > 1): ?>

                    <div class="product-thumbnails">

                        <?php foreach (
                            $galleryImages as $index => $galleryImage
                        ): ?>

                            <img
                                src="<?= htmlspecialchars(
                                    $galleryImage['url'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                alt="<?= htmlspecialchars(
                                    $galleryImage['alt'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                class="product-thumbnail <?= $index === 0 ? 'active' : '' ?>"
                                data-full-image="<?= htmlspecialchars(
                                    $galleryImage['url'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                data-alt="<?= htmlspecialchars(
                                    $galleryImage['alt'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>

        <?php endif; ?>

        <h1>
            <?= htmlspecialchars(
                $name,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </h1>

        <?php if ($brandName !== ''): ?>

            <div class="meta">
                برند:
                <?= htmlspecialchars(
                    $brandName,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>

        <?php endif; ?>

        <?php if ($categoryName !== ''): ?>

            <div class="meta">
                دسته‌بندی:
                <?= htmlspecialchars(
                    $categoryName,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>

        <?php endif; ?>

        <?php if ($sku !== ''): ?>

            <div class="meta">
                کد کالا:
                <?= htmlspecialchars(
                    $sku,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>

        <?php endif; ?>

        <div class="description">
            <?= nl2br(
                htmlspecialchars(
                    $description,
                    ENT_QUOTES,
                    'UTF-8'
                )
            ) ?>
        </div>

        <div class="price">

            <?php if ($discountPrice !== null): ?>

                <span class="old-price">
                    <?= number_format($price) ?>
                    تومان
                </span>

                <?= number_format($discountPrice) ?>
                تومان

            <?php else: ?>

                <?= number_format($price) ?>
                تومان

            <?php endif; ?>

        </div>

        <div class="stock">

            <?php if ($stock > 0): ?>

                <span class="available">
                    موجود است —
                    <?= $stock ?>
                    عدد
                </span>

            <?php else: ?>

                <span class="unavailable">
                    ناموجود
                </span>

            <?php endif; ?>

        </div>

        <?php if (Auth::check()): ?>

            <form
                method="POST"
                action="<?= htmlspecialchars(
                    $baseUrl . '/cart/add',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                class="cart-form"
                id="addToCartForm"
            >

                <?= Csrf::field() ?>

                <input
                    type="hidden"
                    name="product_id"
                    value="<?= (int) $product['id'] ?>"
                >

                <input
                    type="number"
                    name="quantity"
                    value="1"
                    min="1"
                    max="<?= max(1, $stock) ?>"
                    <?= $stock <= 0 ? 'disabled' : '' ?>
                >

                <button
                    type="submit"
                    id="addToCartButton"
                    <?= $stock <= 0 ? 'disabled' : '' ?>
                >
                    افزودن به سبد خرید
                </button>

            </form>

        <?php else: ?>

            <div class="login-message">
                برای افزودن محصول به سبد خرید ابتدا
                <a
                    href="<?= htmlspecialchars(
                        $baseUrl . '/login',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >
                    وارد حساب کاربری
                </a>
                شوید.
            </div>

        <?php endif; ?>

        <p>
            <a
                href="<?= htmlspecialchars(
                    $baseUrl . '/products',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >
                ← بازگشت به محصولات
            </a>

            &nbsp;&nbsp;

            <?php if (Auth::check()): ?>

                <a
                    href="<?= htmlspecialchars(
                        $baseUrl . '/cart',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    class="cart-link"
                >
                    مشاهده سبد خرید
                    <span
                        class="cart-count"
                        id="cartCount"
                    >
                        0
                    </span>
                </a>

            <?php endif; ?>

        </p>

    </div>

</div>

<!-- Cart Modal -->

<div
    class="cart-modal"
    id="cartModal"
    aria-hidden="true"
>
    <div class="cart-modal-box">

        <div
            class="cart-modal-icon"
            id="cartModalIcon"
        >
            ✓
        </div>

        <div
            class="cart-modal-title"
            id="cartModalTitle"
        >
            موفق
        </div>

        <div
            class="cart-modal-message"
            id="cartModalMessage"
        >
        </div>

        <button
            type="button"
            class="cart-modal-button"
            id="cartModalClose"
        >
            باشه
        </button>

    </div>
</div>

<?php if (Auth::check()): ?>

<script>
(function () {

    const form = document.getElementById('addToCartForm');
    const button = document.getElementById('addToCartButton');

    const modal = document.getElementById('cartModal');
    const modalBox = modal.querySelector('.cart-modal-box');

    const icon = document.getElementById('cartModalIcon');
    const title = document.getElementById('cartModalTitle');
    const message = document.getElementById('cartModalMessage');
    const closeButton = document.getElementById('cartModalClose');

    const cartCount = document.getElementById('cartCount');

    if (!form) {
        return;
    }

    function showModal(type, modalTitle, modalMessage) {

        modal.classList.add('show');
        modal.classList.remove('success', 'error');
        modal.classList.add(type);

        icon.textContent =
            type === 'success' ? '✓' : '×';

        title.textContent = modalTitle;
        message.textContent = modalMessage;

        modal.setAttribute('aria-hidden', 'false');
    }

    function hideModal() {

        modal.classList.remove(
            'show',
            'success',
            'error'
        );

        modal.setAttribute(
            'aria-hidden',
            'true'
        );
    }

    closeButton.addEventListener(
        'click',
        hideModal
    );

    modal.addEventListener(
        'click',
        function (event) {

            if (event.target === modal) {
                hideModal();
            }

        }
    );

    form.addEventListener(
        'submit',
        async function (event) {

            event.preventDefault();

            if (button.disabled) {
                return;
            }

            const originalText =
                button.textContent;

            button.disabled = true;
            button.textContent = 'در حال افزودن...';

            try {

                const response = await fetch(
                    form.action,
                    {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With':
                                'XMLHttpRequest',
                            'Accept':
                                'application/json'
                        }
                    }
                );

                let data = null;

                try {
                    data = await response.json();
                } catch (jsonError) {
                    data = null;
                }

                if (
                    !data
                    || typeof data.success === 'undefined'
                ) {
                    throw new Error(
                        'پاسخ نامعتبر از سرور دریافت شد.'
                    );
                }

                if (data.success) {

                    showModal(
                        'success',
                        'افزودن به سبد خرید',
                        data.message
                            || 'محصول با موفقیت به سبد خرید اضافه شد.'
                    );

                    if (
                        typeof data.cartQuantity !==
                        'undefined'
                    ) {
                        cartCount.textContent =
                            data.cartQuantity;
                    }

                } else {

                    showModal(
                        'error',
                        'خطا',
                        data.message
                            || 'افزودن محصول به سبد خرید انجام نشد.'
                    );
                }

            } catch (error) {

                showModal(
                    'error',
                    'خطا',
                    error.message
                        || 'ارتباط با سرور برقرار نشد.'
                );

            } finally {

                button.disabled = false;
                button.textContent = originalText;

            }

        }
    );

})();
</script>

<?php endif; ?>

<!-- Gallery JavaScript -->
<script>
document.querySelectorAll('.product-thumbnail')
    .forEach(function (thumbnail) {

        thumbnail.addEventListener(
            'click',
            function () {

                const mainImage =
                    document.getElementById(
                        'productMainImage'
                    );

                if (!mainImage) {
                    return;
                }

                mainImage.src =
                    this.dataset.fullImage;

                mainImage.alt =
                    this.dataset.alt || '';

                document
                    .querySelectorAll(
                        '.product-thumbnail'
                    )
                    .forEach(function (item) {
                        item.classList.remove(
                            'active'
                        );
                    });

                this.classList.add('active');
            }
        );

    });
</script>

</body>

</html>
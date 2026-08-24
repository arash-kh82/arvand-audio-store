<?php

declare(strict_types=1);

$items = is_array($items ?? null)
    ? $items
    : [];

$addresses = is_array($addresses ?? null)
    ? $addresses
    : [];

$totalQuantity = (int) ($totalQuantity ?? 0);
$itemCount = (int) ($itemCount ?? 0);
$total = (float) ($total ?? 0);

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
            (string) ($title ?? 'تکمیل سفارش'),
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<div class="container py-5">

    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <h1 class="h4 mb-3">
                تکمیل سفارش
            </h1>

            <p class="text-muted mb-0">
                محصولات و آدرس ارسال خود را بررسی کنید و سپس سفارش را ثبت کنید.
            </p>

        </div>

    </div>


    <!-- محصولات -->

    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <h2 class="h5 mb-4">
                محصولات سفارش
            </h2>


            <?php foreach ($items as $item): ?>

                <?php

                $productName = (string) (
                    $item['product_name']
                    ?? 'محصول'
                );

                $quantity = (int) (
                    $item['quantity']
                    ?? 1
                );

                $price = (float) (
                    $item['price']
                    ?? 0
                );

                $subtotal = $price * $quantity;

                ?>

                <div class="border-bottom py-3">

                    <div class="fw-bold">
                        <?= htmlspecialchars(
                            $productName,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </div>

                    <div class="text-muted small mt-2">

                        قیمت واحد:
                        <?= number_format($price) ?>
                        تومان

                        |

                        تعداد:
                        <?= $quantity ?>

                    </div>

                    <div class="mt-2">

                        مبلغ:
                        <strong>
                            <?= number_format($subtotal) ?>
                            تومان
                        </strong>

                    </div>

                </div>

            <?php endforeach; ?>


            <div class="mt-4">

                <div class="d-flex justify-content-between mb-2">

                    <span>
                        تعداد آیتم‌ها
                    </span>

                    <strong>
                        <?= $itemCount ?>
                    </strong>

                </div>


                <div class="d-flex justify-content-between mb-2">

                    <span>
                        تعداد کل کالاها
                    </span>

                    <strong>
                        <?= $totalQuantity ?>
                    </strong>

                </div>


                <div class="d-flex justify-content-between border-top pt-3 mt-3">

                    <span class="fw-bold">
                        مبلغ نهایی
                    </span>

                    <strong class="fs-5">
                        <?= number_format($total) ?>
                        تومان
                    </strong>

                </div>

            </div>

        </div>

    </div>


    <!-- آدرس ارسال -->

    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <h2 class="h5 mb-4">
                آدرس ارسال
            </h2>


            <?php if ($addresses === []): ?>

                <div class="alert alert-warning">

                    <div class="fw-bold mb-2">
                        هنوز آدرسی ثبت نکرده‌اید.
                    </div>

                    <div>
                        ابتدا یک آدرس برای ارسال سفارش ثبت کنید.
                    </div>

                    <a
                        href="<?= htmlspecialchars(
                            app_config('base_url', '')
                            . '/addresses',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        class="btn btn-warning mt-3"
                    >
                        مدیریت آدرس‌ها
                    </a>

                </div>

            <?php else: ?>

                <?php foreach ($addresses as $index => $address): ?>

                    <div class="border rounded p-3 mb-3">

                        <div class="form-check">

                            <input
                                class="form-check-input"
                                type="radio"
                                name="address_id"
                                id="address_<?= (int) $address['id'] ?>"
                                value="<?= (int) $address['id'] ?>"
                                form="checkout-form"
                                <?= $index === 0 ? 'checked' : '' ?>
                            >

                            <label
                                class="form-check-label w-100"
                                for="address_<?= (int) $address['id'] ?>"
                            >

                                <div class="fw-bold mb-2">

                                    <?= htmlspecialchars(
                                        (string) (
                                            $address['title']
                                            ?? 'آدرس'
                                        ),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </div>

                                <div class="mb-1">

                                    گیرنده:
                                    <?= htmlspecialchars(
                                        (string) $address['receiver_name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </div>

                                <div class="mb-1">

                                    تلفن:
                                    <?= htmlspecialchars(
                                        (string) $address['phone'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </div>

                                <div class="mb-1">

                                    <?= htmlspecialchars(
                                        (string) $address['province'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                    -

                                    <?= htmlspecialchars(
                                        (string) $address['city'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </div>

                                <div>

                                    <?= htmlspecialchars(
                                        (string) $address['address'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </div>

                                <?php if (!empty($address['postal_code'])): ?>

                                    <div class="text-muted small mt-2">

                                        کد پستی:
                                        <?= htmlspecialchars(
                                            (string) $address['postal_code'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </div>

                                <?php endif; ?>

                            </label>

                        </div>

                    </div>

                <?php endforeach; ?>

                <a
                    href="<?= htmlspecialchars(
                        app_config('base_url', '')
                        . '/addresses',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    class="btn btn-outline-secondary"
                >
                    مدیریت آدرس‌ها
                </a>

            <?php endif; ?>

        </div>

    </div>


    <!-- ثبت سفارش -->

    <div class="card shadow-sm">

        <div class="card-body">

            <form
                id="checkout-form"
                method="POST"
                action="<?= htmlspecialchars(
                    app_config('base_url', '')
                    . '/checkout',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >

                <?= $csrfField ?? '' ?>


                <?php if ($addresses !== []): ?>

                    <button
                        type="submit"
                        class="btn btn-primary btn-lg"
                    >
                        تأیید و ثبت سفارش
                    </button>

                <?php else: ?>

                    <button
                        type="button"
                        class="btn btn-secondary btn-lg"
                        disabled
                    >
                        ابتدا آدرس ثبت کنید
                    </button>

                <?php endif; ?>


                <a
                    href="<?= htmlspecialchars(
                        app_config('base_url', '')
                        . '/cart',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    class="btn btn-outline-dark btn-lg ms-2"
                >
                    بازگشت به سبد خرید
                </a>

            </form>

        </div>

    </div>

</div>

</body>

</html>
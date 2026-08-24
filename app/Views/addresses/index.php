
<?php

use App\Core\Session;

$e = static fn($value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$addresses = is_array($addresses ?? null)
    ? $addresses
    : [];

$success = Session::flash('success');
$error = Session::flash('error');

?>

<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        <?= $e($title ?? 'آدرس‌ها') ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container py-5">

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?= $e($success) ?>
        </div>
    <?php endif; ?>


    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= $e($error) ?>
        </div>
    <?php endif; ?>


    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <h1 class="h4 mb-4">
                آدرس‌های من
            </h1>


            <?php if ($addresses === []): ?>

                <div class="alert alert-info">
                    هنوز آدرسی ثبت نکرده‌اید.
                </div>

            <?php else: ?>

                <?php foreach ($addresses as $address): ?>

                    <div class="border rounded p-3 mb-3">

                        <h5>
                            <?= $e($address['title'] ?? 'آدرس') ?>
                        </h5>

                        <p class="mb-1">
                            گیرنده:
                            <?= $e($address['receiver_name']) ?>
                        </p>

                        <p class="mb-1">
                            تلفن:
                            <?= $e($address['phone']) ?>
                        </p>

                        <p class="mb-1">
                            <?= $e($address['province']) ?>
                            -
                            <?= $e($address['city']) ?>
                        </p>

                        <p>
                            <?= $e($address['address']) ?>
                        </p>


                        <form
                            method="POST"
                            action="/addresses/delete"
                        >

                            <?= $csrfField ?>

                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int) $address['id'] ?>"
                            >

                            <button
                                class="btn btn-sm btn-outline-danger"
                            >
                                حذف
                            </button>

                        </form>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </div>



    <div class="card shadow-sm">

        <div class="card-body">

            <h2 class="h5 mb-4">
                افزودن آدرس جدید
            </h2>


            <form
                method="POST"
                action="/addresses"
            >

                <?= $csrfField ?>


                <input
                    class="form-control mb-3"
                    name="title"
                    placeholder="عنوان (مثلاً منزل)"
                >


                <input
                    class="form-control mb-3"
                    name="receiver_name"
                    placeholder="نام گیرنده"
                    required
                >


                <input
                    class="form-control mb-3"
                    name="phone"
                    placeholder="شماره تماس"
                    required
                >


                <input
                    class="form-control mb-3"
                    name="province"
                    placeholder="استان"
                    required
                >


                <input
                    class="form-control mb-3"
                    name="city"
                    placeholder="شهر"
                    required
                >


                <textarea
                    class="form-control mb-3"
                    name="address"
                    placeholder="آدرس کامل"
                    required
                ></textarea>


                <input
                    class="form-control mb-3"
                    name="postal_code"
                    placeholder="کد پستی"
                >


                <button class="btn btn-primary">
                    ذخیره آدرس
                </button>

            </form>

        </div>

    </div>


</div>

</body>

</html>
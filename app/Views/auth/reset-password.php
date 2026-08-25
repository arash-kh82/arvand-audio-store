<?php

declare(strict_types=1);

use App\Core\Session;

$e = static fn($value): string =>
    htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );

$error = Session::flash('error');
$success = Session::flash('success');

?>

<!doctype html>
<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        <?= $e($title ?? 'تعیین رمز عبور جدید') ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-12 col-md-8 col-lg-5">

            <div class="bg-white border rounded-3 p-4 shadow-sm">

                <h1 class="h4 mb-4">
                    تعیین رمز عبور جدید
                </h1>

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

                <form
                    method="post"
                    action="/arvand-audio-store/public/reset-password"
                >

                    <?= $csrfField ?? '' ?>

                    <div class="mb-3">

                        <label class="form-label">
                            رمز عبور جدید
                        </label>

                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            minlength="8"
                            required
                            autocomplete="new-password"
                        >

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            تکرار رمز عبور
                        </label>

                        <input
                            type="password"
                            name="password_confirmation"
                            class="form-control"
                            minlength="8"
                            required
                            autocomplete="new-password"
                        >

                    </div>

                    <div class="d-grid">

                        <button
                            class="btn btn-primary"
                            type="submit"
                        >
                            تغییر رمز عبور
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

</body>

</html>
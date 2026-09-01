<?php

use App\Core\Session;

$e = static fn($value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);

$errors = $errors ?? [];
$old = $old ?? [];

$success = Session::flash('success');
$error = Session::flash('error');
?>

<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $e($title ?? 'ثبت‌نام | فروشگاه آروَند آدیو') ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top right,
                    rgba(13, 110, 253, 0.10),
                    transparent 35%),
                linear-gradient(135deg, #f8f9fa, #eef2f7);
        }

        .register-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 40px 0;
        }

        .register-card {
            border: 0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.08);
        }

        .register-header {
            background: linear-gradient(135deg,
                    #0d6efd,
                    #0a58ca);
            color: #fff;
            padding: 30px;
            text-align: center;
        }

        .register-logo {
            width: 64px;
            height: 64px;
            margin: 0 auto 15px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 700;
        }

        .register-body {
            padding: 30px;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-control {
            min-height: 48px;
            border-radius: 12px;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.12);
        }

        .btn {
            min-height: 48px;
            border-radius: 12px;
            font-weight: 600;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-control {
            padding-left: 48px;
        }

        .toggle-password {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #6c757d;
            padding: 6px 10px;
            cursor: pointer;
        }

        .toggle-password:hover {
            color: #0d6efd;
        }

        .password-hint {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 6px;
        }

        .register-footer {
            text-align: center;
            margin-top: 22px;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .register-footer a {
            text-decoration: none;
            font-weight: 600;
        }

        @media (max-width: 576px) {
            .register-wrapper {
                padding: 20px 0;
            }

            .register-body {
                padding: 22px;
            }

            .register-header {
                padding: 25px 20px;
            }
        }
    </style>
</head>

<body>

    <div class="container register-wrapper">
        <div class="row justify-content-center w-100">

            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">

                <div class="card register-card">

                    <!-- Header -->
                    <div class="register-header">

                        <div class="register-logo">
                            A
                        </div>

                        <h1 class="h4 fw-bold mb-2">
                            ایجاد حساب کاربری
                        </h1>

                        <p class="mb-0 opacity-75">
                            به فروشگاه آروند آدیو خوش آمدید
                        </p>

                    </div>

                    <!-- Body -->
                    <div class="register-body">

                        <?php if ($success): ?>
                            <div class="alert alert-success rounded-3">
                                <?= $e($success) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger rounded-3">
                                <?= $e($error) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($errors !== []): ?>
                            <div class="alert alert-danger rounded-3">

                                <div class="fw-bold mb-2">
                                    لطفاً موارد زیر را بررسی کنید:
                                </div>

                                <?php foreach ($errors as $message): ?>
                                    <div class="mb-1">
                                        • <?= $e($message) ?>
                                    </div>
                                <?php endforeach; ?>

                            </div>
                        <?php endif; ?>

                        <form
                            method="post"
                            action="/arvand-audio-store/public/register"
                            novalidate>

                            <?= $csrfField ?? '' ?>

                            <!-- Name -->
                            <div class="mb-3">

                                <label
                                    for="name"
                                    class="form-label">
                                    نام
                                </label>

                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    class="form-control"
                                    value="<?= $e($old['name'] ?? '') ?>"
                                    autocomplete="name"
                                    maxlength="100"
                                    required
                                    autofocus>

                            </div>

                            <!-- Email -->
                            <div class="mb-3">

                                <label
                                    for="email"
                                    class="form-label">
                                    ایمیل
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control"
                                    value="<?= $e($old['email'] ?? '') ?>"
                                    autocomplete="email"
                                    maxlength="190"
                                    dir="ltr"
                                    required>

                            </div>

                            <!-- Password -->
                            <div class="mb-3">

                                <label
                                    for="password"
                                    class="form-label">
                                    رمز عبور
                                </label>

                                <div class="password-wrapper">

                                    <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        class="form-control"
                                        minlength="8"
                                        autocomplete="new-password"
                                        dir="ltr"
                                        required>

                                    <button
                                        type="button"
                                        class="toggle-password"
                                        data-target="password"
                                        aria-label="نمایش رمز عبور">
                                        👁
                                    </button>

                                </div>

                                <div class="password-hint">
                                    رمز عبور باید حداقل ۸ کاراکتر باشد.
                                </div>

                            </div>

                            <!-- Password Confirmation -->
                            <div class="mb-4">

                                <label
                                    for="password_confirmation"
                                    class="form-label">
                                    تکرار رمز عبور
                                </label>

                                <div class="password-wrapper">

                                    <input
                                        type="password"
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        class="form-control"
                                        minlength="8"
                                        autocomplete="new-password"
                                        dir="ltr"
                                        required>

                                    <button
                                        type="button"
                                        class="toggle-password"
                                        data-target="password_confirmation"
                                        aria-label="نمایش رمز عبور">
                                        👁
                                    </button>

                                </div>

                            </div>

                            <!-- Actions -->
                            <div class="d-grid gap-2">

                                <button
                                    type="submit"
                                    class="btn btn-primary">
                                    ایجاد حساب کاربری
                                </button>

                                <a
                                    href="/arvand-audio-store/public/login"
                                    class="btn btn-outline-secondary">
                                    قبلاً حساب دارم؛ ورود
                                </a>

                            </div>

                        </form>

                        <div class="register-footer">

                            <a href="/arvand-audio-store/public/">
                                بازگشت به صفحه اصلی
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>

    <script>
        document.querySelectorAll('.toggle-password').forEach(function(button) {

            button.addEventListener('click', function() {

                const targetId = button.dataset.target;
                const input = document.getElementById(targetId);

                if (!input) {
                    return;
                }

                if (input.type === 'password') {
                    input.type = 'text';
                    button.textContent = '🙈';
                    button.setAttribute('aria-label', 'مخفی کردن رمز عبور');
                } else {
                    input.type = 'password';
                    button.textContent = '👁';
                    button.setAttribute('aria-label', 'نمایش رمز عبور');
                }

            });

        });
    </script>

</body>

</html>
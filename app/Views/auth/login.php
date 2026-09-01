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

$baseUrl = function_exists('app_config')
    ? rtrim((string) app_config('app.base_url', ''), '/')
    : '';
?>

<!doctype html>

<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $e($title ?? 'ورود به حساب') ?> | Arvand Audio Store</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet">

    <style>
        :root {
            --primary: #0d6efd;
            --dark: #111827;
            --muted: #6b7280;
            --border: #e5e7eb;
            --bg: #f4f7fb;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top right,
                    rgba(13, 110, 253, .10),
                    transparent 35%),
                var(--bg);
            color: var(--dark);
        }

        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }

        .auth-card {
            width: 100%;
            max-width: 460px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: 0 15px 45px rgba(15, 23, 42, .08);
            overflow: hidden;
        }

        .auth-header {
            text-align: center;
            padding: 34px 30px 20px;
        }

        .brand-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 18px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary);
            color: #fff;
            font-size: 27px;
            font-weight: 800;
            box-shadow: 0 10px 25px rgba(13, 110, 253, .22);
        }

        .brand-title {
            font-size: 1.35rem;
            font-weight: 800;
            margin-bottom: 7px;
        }

        .brand-subtitle {
            color: var(--muted);
            font-size: .9rem;
            margin-bottom: 0;
        }

        .auth-body {
            padding: 20px 30px 30px;
        }

        .form-label {
            font-weight: 600;
            font-size: .9rem;
        }

        .form-control {
            min-height: 48px;
            border-radius: 11px;
            border-color: #dfe3e8;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .12);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-control {
            padding-left: 48px;
        }

        .password-toggle {
            position: absolute;
            left: 7px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #6b7280;
            width: 38px;
            height: 38px;
            border-radius: 8px;
        }

        .password-toggle:hover {
            background: #f3f4f6;
            color: var(--dark);
        }

        .btn-login {
            min-height: 48px;
            border-radius: 11px;
            font-weight: 700;
        }

        .secondary-links {
            text-align: center;
            margin-top: 18px;
        }

        .secondary-links a {
            text-decoration: none;
            font-size: .9rem;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0;
            color: #9ca3af;
            font-size: .8rem;
        }

        .divider::before,
        .divider::after {
            content: "";
            height: 1px;
            background: var(--border);
            flex: 1;
        }

        .back-store {
            display: block;
            text-align: center;
            color: #374151;
            text-decoration: none;
            font-size: .9rem;
        }

        .back-store:hover {
            color: var(--primary);
        }

        .alert {
            border-radius: 11px;
            font-size: .9rem;
        }

        .alert ul {
            margin-bottom: 0;
            padding-right: 20px;
        }

        @media (max-width: 575.98px) {
            .auth-card {
                border-radius: 16px;
            }

            .auth-header {
                padding: 28px 20px 15px;
            }

            .auth-body {
                padding: 18px 20px 24px;
            }
        }
    </style>

</head>

<body>

    <div class="auth-wrapper">

        <div class="auth-card">

            <!-- Header -->
            <div class="auth-header">

                <div class="brand-icon">
                    A
                </div>

                <div class="brand-title">
                    Arvand Audio Store
                </div>

                <p class="brand-subtitle">
                    <?= $e($title ?? 'ورود به حساب کاربری') ?>
                </p>

            </div>

            <!-- Body -->
            <div class="auth-body">

                <?php if ($success): ?>
                    <div class="alert alert-success mb-3">
                        <?= $e($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger mb-3">
                        <?= $e($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($errors !== []): ?>
                    <div class="alert alert-danger mb-3">

                        <div class="fw-bold mb-2">
                            لطفاً موارد زیر را بررسی کنید:
                        </div>

                        <ul>
                            <?php foreach ($errors as $message): ?>
                                <li><?= $e($message) ?></li>
                            <?php endforeach; ?>
                        </ul>

                    </div>
                <?php endif; ?>

                <form
                    method="post"
                    action="login"
                    novalidate>

                    <?= $csrfField ?? '' ?>

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
                            placeholder="example@email.com"
                            autocomplete="email"
                            required>

                    </div>

                    <!-- Password -->
                    <div class="mb-3">

                        <div class="d-flex justify-content-between align-items-center mb-2">

                            <label
                                for="password"
                                class="form-label mb-0">
                                رمز عبور
                            </label>

                            <a
                                href="forgot-password"
                                class="small text-decoration-none">
                                فراموشی رمز عبور
                            </a>

                        </div>

                        <div class="password-wrapper">

                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                placeholder="رمز عبور خود را وارد کنید"
                                autocomplete="current-password"
                                required>

                            <button
                                type="button"
                                class="password-toggle"
                                id="passwordToggle"
                                aria-label="نمایش رمز عبور">

                                👁

                            </button>

                        </div>

                    </div>

                    <!-- Submit -->
                    <div class="d-grid mt-4">

                        <button
                            type="submit"
                            class="btn btn-primary btn-login">

                            ورود به حساب

                        </button>

                    </div>

                </form>

                <div class="divider">
                    حساب کاربری ندارید؟
                </div>

                <div class="d-grid">

                    <a
                        href="register"
                        class="btn btn-outline-secondary btn-login">

                        ایجاد حساب جدید

                    </a>

                </div>

                <div class="secondary-links">

                    <a
                        href="http://localhost/arvand-audio-store/public/"
                        class="back-store">

                        ← بازگشت به فروشگاه

                    </a>

                </div>

            </div>

        </div>

    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('passwordToggle');

        if (passwordInput && passwordToggle) {
            passwordToggle.addEventListener('click', function() {
                const isPassword = passwordInput.type === 'password';

                passwordInput.type = isPassword ?
                    'text' :
                    'password';

                passwordToggle.textContent = isPassword ?
                    '🙈' :
                    '👁';

                passwordToggle.setAttribute(
                    'aria-label',
                    isPassword ?
                    'مخفی کردن رمز عبور' :
                    'نمایش رمز عبور'
                );
            });
        }
    </script>

</body>

</html>
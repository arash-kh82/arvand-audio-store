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
        content="width=device-width, initial-scale=1">

    <title>
        <?= $e($title ?? 'تعیین رمز عبور جدید') ?> | Arvand Audio Store
    </title>

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
            line-height: 1.8;
            margin-bottom: 0;
        }

        .auth-body {
            padding: 20px 30px 30px;
        }

        .info-box {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 13px 14px;
            margin-bottom: 20px;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 11px;
            color: #4b5563;
            font-size: .88rem;
            line-height: 1.8;
        }

        .info-icon {
            flex-shrink: 0;
            font-size: 1.1rem;
        }

        .form-label {
            font-weight: 600;
            font-size: .9rem;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-control {
            padding-left: 48px;
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

        .password-toggle {
            position: absolute;
            left: 7px;
            top: 50%;
            transform: translateY(-50%);
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #6b7280;
        }

        .password-toggle:hover {
            background: #f3f4f6;
            color: var(--dark);
        }

        .password-hint {
            color: var(--muted);
            font-size: .78rem;
            margin-top: 7px;
        }

        .password-match {
            font-size: .78rem;
            margin-top: 7px;
            min-height: 18px;
        }

        .btn-main {
            min-height: 48px;
            border-radius: 11px;
            font-weight: 700;
        }

        .alert {
            border-radius: 11px;
            font-size: .9rem;
        }

        .back-login {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: #374151;
            text-decoration: none;
            font-size: .9rem;
        }

        .back-login:hover {
            color: var(--primary);
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
                    <?= $e($title ?? 'تعیین رمز عبور جدید') ?>
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

                <div class="info-box">

                    <div class="info-icon">
                        🔑
                    </div>

                    <div>
                        رمز عبور جدید خود را وارد کنید.
                        رمز عبور باید حداقل ۸ کاراکتر داشته باشد.
                    </div>

                </div>

                <form
                    method="post"
                    action="/arvand-audio-store/public/reset-password"
                    id="resetPasswordForm">

                    <?= $csrfField ?? '' ?>

                    <!-- New Password -->
                    <div class="mb-3">

                        <label
                            for="password"
                            class="form-label">
                            رمز عبور جدید
                        </label>

                        <div class="password-wrapper">

                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                minlength="8"
                                autocomplete="new-password"
                                required>

                            <button
                                type="button"
                                class="password-toggle"
                                data-target="password"
                                aria-label="نمایش رمز عبور">
                                👁
                            </button>

                        </div>

                        <div class="password-hint">
                            حداقل ۸ کاراکتر
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
                                required>

                            <button
                                type="button"
                                class="password-toggle"
                                data-target="password_confirmation"
                                aria-label="نمایش رمز عبور">
                                👁
                            </button>

                        </div>

                        <div
                            id="passwordMatch"
                            class="password-match"></div>

                    </div>

                    <!-- Submit -->
                    <div class="d-grid">

                        <button
                            class="btn btn-primary btn-main"
                            type="submit">
                            تغییر رمز عبور
                        </button>

                    </div>

                </form>

                <a
                    class="back-login"
                    href="/arvand-audio-store/public/login">
                    ← بازگشت به صفحه ورود
                </a>

            </div>

        </div>

    </div>

    <script>
        // Show / hide password
        document
            .querySelectorAll('.password-toggle')
            .forEach(function(button) {

                button.addEventListener('click', function() {

                    const targetId = this.dataset.target;
                    const input = document.getElementById(targetId);

                    if (!input) {
                        return;
                    }

                    const isPassword = input.type === 'password';

                    input.type = isPassword ?
                        'text' :
                        'password';

                    this.textContent = isPassword ?
                        '🙈' :
                        '👁';

                    this.setAttribute(
                        'aria-label',
                        isPassword ?
                        'مخفی کردن رمز عبور' :
                        'نمایش رمز عبور'
                    );

                });

            });


        // Password confirmation check
        const password = document.getElementById('password');
        const confirmation = document.getElementById('password_confirmation');
        const passwordMatch = document.getElementById('passwordMatch');

        function checkPasswordMatch() {

            if (
                !password ||
                !confirmation ||
                !passwordMatch
            ) {
                return;
            }

            if (confirmation.value === '') {
                passwordMatch.textContent = '';
                return;
            }

            if (password.value === confirmation.value) {

                passwordMatch.textContent = 'رمزهای عبور یکسان هستند.';
                passwordMatch.className = 'password-match text-success';

            } else {

                passwordMatch.textContent = 'رمزهای عبور یکسان نیستند.';
                passwordMatch.className = 'password-match text-danger';

            }

        }

        if (password && confirmation) {

            password.addEventListener(
                'input',
                checkPasswordMatch
            );

            confirmation.addEventListener(
                'input',
                checkPasswordMatch
            );

        }
    </script>

</body>

</html>
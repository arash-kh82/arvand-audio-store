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
        <?= $e($title ?? 'تایید کد بازیابی') ?> | Arvand Audio Store
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

        .code-input {
            min-height: 58px;
            border-radius: 11px;
            border-color: #dfe3e8;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: .35rem;
        }

        .code-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .12);
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

        .back-link {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: #374151;
            text-decoration: none;
            font-size: .9rem;
        }

        .back-link:hover {
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
                    <?= $e($title ?? 'تایید کد بازیابی') ?>
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
                        🔐
                    </div>

                    <div>
                        کد ۶ رقمی ارسال‌شده به ایمیل خود را وارد کنید.
                        کد را با دقت وارد کنید تا بتوانید رمز عبور جدید تعیین کنید.
                    </div>

                </div>

                <form
                    method="post"
                    action="/arvand-audio-store/public/verify-reset-code">

                    <?= $csrfField ?? '' ?>

                    <div class="mb-4">

                        <label
                            for="code"
                            class="form-label">
                            کد بازیابی
                        </label>

                        <input
                            type="text"
                            id="code"
                            name="code"
                            class="form-control text-center code-input"
                            maxlength="6"
                            minlength="6"
                            pattern="[0-9]{6}"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            placeholder="------"
                            autofocus
                            required>

                    </div>

                    <div class="d-grid">

                        <button
                            class="btn btn-primary btn-main"
                            type="submit">
                            تایید کد
                        </button>

                    </div>

                </form>

                <a
                    class="back-link"
                    href="/arvand-audio-store/public/forgot-password">
                    ← بازگشت به بازیابی رمز عبور
                </a>

            </div>

        </div>

    </div>

    <script>
        const codeInput = document.getElementById('code');

        if (codeInput) {
            codeInput.addEventListener('input', function() {
                this.value = this.value
                    .replace(/\D/g, '')
                    .slice(0, 6);
            });
        }
    </script>

</body>

</html>
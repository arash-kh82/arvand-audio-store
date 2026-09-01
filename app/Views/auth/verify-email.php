<?php

declare(strict_types=1);

use App\Core\Session;

$e = static fn($value): string => htmlspecialchars(
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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $e($title ?? 'تأیید ایمیل | فروشگاه آروَند آدیو') ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top right,
                    rgba(25, 135, 84, 0.10),
                    transparent 35%),
                linear-gradient(135deg, #f8f9fa, #eef2f7);
        }

        .verify-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 40px 0;
        }

        .verify-card {
            border: 0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.08);
        }

        .verify-header {
            background: linear-gradient(135deg,
                    #198754,
                    #157347);
            color: #fff;
            padding: 30px;
            text-align: center;
        }

        .verify-icon {
            width: 68px;
            height: 68px;
            margin: 0 auto 15px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }

        .verify-body {
            padding: 30px;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
        }

        .code-input {
            min-height: 58px;
            border-radius: 14px;
            text-align: center;
            direction: ltr;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 8px;
        }

        .code-input:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.12);
        }

        .code-help {
            margin-top: 8px;
            color: #6c757d;
            font-size: 0.85rem;
            text-align: center;
        }

        .btn {
            min-height: 48px;
            border-radius: 12px;
            font-weight: 600;
        }

        .divider {
            position: relative;
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 28px 0;
            color: #6c757d;
            font-size: 0.85rem;
        }

        .divider::before,
        .divider::after {
            content: "";
            height: 1px;
            background: #dee2e6;
            flex: 1;
        }

        .info-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 22px;
            color: #495057;
            font-size: 0.9rem;
            line-height: 1.8;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-link:hover {
            color: #198754;
        }

        @media (max-width: 576px) {
            .verify-wrapper {
                padding: 20px 0;
            }

            .verify-header {
                padding: 25px 20px;
            }

            .verify-body {
                padding: 22px;
            }

            .code-input {
                font-size: 21px;
                letter-spacing: 6px;
            }
        }
    </style>
</head>

<body>

    <div class="container verify-wrapper">

        <div class="row justify-content-center w-100">

            <div class="col-12 col-sm-10 col-md-8 col-lg-5">

                <div class="card verify-card">

                    <!-- Header -->
                    <div class="verify-header">

                        <div class="verify-icon">
                            ✉
                        </div>

                        <h1 class="h4 fw-bold mb-2">
                            تأیید ایمیل
                        </h1>

                        <p class="mb-0 opacity-75">
                            کد ارسال‌شده به ایمیل خود را وارد کنید
                        </p>

                    </div>

                    <!-- Body -->
                    <div class="verify-body">

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


                        <div class="info-box">
                            کد تأیید ارسال‌شده به ایمیل شما را در کادر زیر وارد کنید.
                            کد تأیید معمولاً یک کد ۶ رقمی است.
                        </div>


                        <!-- Verify Form -->
                        <form
                            method="post"
                            action="/arvand-audio-store/public/verify-email"
                            autocomplete="off">

                            <?= $csrfField ?? '' ?>

                            <div class="mb-4">

                                <label
                                    for="code"
                                    class="form-label">
                                    کد تأیید
                                </label>

                                <input
                                    type="text"
                                    id="code"
                                    name="code"
                                    class="form-control code-input"
                                    maxlength="6"
                                    minlength="6"
                                    inputmode="numeric"
                                    pattern="[0-9]{6}"
                                    placeholder="۱۲۳۴۵۶"
                                    autocomplete="one-time-code"
                                    required
                                    autofocus>

                                <div class="code-help">
                                    کد ۶ رقمی ارسال‌شده به ایمیل را وارد کنید.
                                </div>

                            </div>


                            <div class="d-grid">

                                <button
                                    type="submit"
                                    class="btn btn-success">
                                    تأیید ایمیل
                                </button>

                            </div>

                        </form>


                        <!-- Divider -->
                        <div class="divider">
                            <span>یا</span>
                        </div>


                        <!-- Resend Form -->
                        <form
                            method="post"
                            action="/arvand-audio-store/public/verify-email/resend">

                            <?= $csrfField ?? '' ?>

                            <div class="d-grid">

                                <button
                                    type="submit"
                                    class="btn btn-outline-secondary">
                                    ارسال مجدد کد
                                </button>

                            </div>

                        </form>


                        <a
                            href="/arvand-audio-store/public/"
                            class="back-link">
                            بازگشت به صفحه اصلی
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <script>
        const codeInput = document.getElementById('code');

        if (codeInput) {

            codeInput.addEventListener('input', function() {

                // فقط اعداد انگلیسی
                this.value = this.value
                    .replace(/[^0-9]/g, '')
                    .slice(0, 6);

            });

        }
    </script>

</body>

</html>
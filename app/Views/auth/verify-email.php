<?php

declare(strict_types=1);

use App\Core\Session;

$e = static fn($value): string =>
    htmlspecialchars(
        (string)$value,
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= $e($title ?? 'تایید ایمیل') ?>
    </title>

    <style>
        /* ====== RESET & BASE ====== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Tahoma, Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            color: #222;
        }

        .container {
            background: #fff;
            max-width: 500px;
            width: 100%;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        /* ====== TYPOGRAPHY ====== */
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
            color: #1a1a1a;
        }

        /* ====== MESSAGES ====== */
        .message {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .message-success {
            background: #dff5e3;
            color: #176b2c;
            border-right: 4px solid #176b2c;
        }

        .message-error {
            background: #fde2e2;
            color: #9b1c1c;
            border-right: 4px solid #9b1c1c;
        }

        /* ====== FORM ====== */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 14px;
            color: #333;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.2s;
            text-align: center;
            letter-spacing: 4px;
            font-weight: bold;
            direction: ltr;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #198754;
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.1);
        }

        /* ====== BUTTONS ====== */
        .btn {
            display: inline-block;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            text-align: center;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-primary {
            background: #198754;
            color: #fff;
        }

        .btn-primary:hover {
            background: #157347;
        }

        .btn-secondary {
            background: #6c757d;
            color: #fff;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        /* ====== DIVIDER ====== */
        .divider {
            margin: 30px 0;
            border: none;
            border-top: 1px solid #e5e5e5;
            position: relative;
        }

        .divider::after {
            content: 'یا';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 0 15px;
            color: #999;
            font-size: 13px;
        }

        /* ====== RESEND FORM ====== */
        .resend-form .btn {
            width: 100%;
        }

        /* ====== RESPONSIVE ====== */
        @media (max-width: 480px) {
            .container {
                padding: 25px;
            }

            h1 {
                font-size: 20px;
            }

            input[type="text"] {
                font-size: 18px;
                padding: 10px;
            }
        }
    </style>

</head>

<body>

    <div class="container">

        <h1>تایید ایمیل</h1>

        <!-- ====== MESSAGES ====== -->
        <?php if ($success): ?>
            <div class="message message-success">
                <?= $e($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message message-error">
                <?= $e($error) ?>
            </div>
        <?php endif; ?>

        <!-- ====== FORM: VERIFY EMAIL ====== -->
        <form method="post">

            <?= $csrfField ?? '' ?>

            <div class="form-group">
                <label for="code">کد ارسال شده به ایمیل:</label>
                <input
                    type="text"
                    id="code"
                    name="code"
                    maxlength="6"
                    placeholder="مثلاً ۱۲۳۴۵۶"
                    required
                    autofocus
                >
            </div>

            <button type="submit" class="btn btn-primary">
                تایید ایمیل
            </button>

        </form>

        <!-- ====== DIVIDER ====== -->
        <hr class="divider">

        <!-- ====== FORM: RESEND CODE ====== -->
        <form method="post" action="/arvand-audio-store/public/verify-email/resend">

            <?= $csrfField ?? '' ?>

            <button type="submit" class="btn btn-secondary">
                ارسال مجدد کد
            </button>

        </form>

    </div>

</body>

</html>
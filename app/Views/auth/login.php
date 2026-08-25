<?php

use App\Core\Session;

$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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
    <title><?= $e($title ?? 'ورود') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="bg-white border rounded-3 p-4 shadow-sm">
                    <h1 class="h4 mb-4"><?= $e($title ?? 'ورود') ?></h1>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $e($success) ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $e($error) ?></div>
                    <?php endif; ?>

                    <?php if ($errors !== []): ?>
                        <div class="alert alert-danger mb-3">
                            <?php foreach ($errors as $message): ?>
                                <div><?= $e($message) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="login" novalidate>
                        <?= $csrfField ?? '' ?>
                        <div class="mb-3">
                            <label class="form-label">ایمیل</label>
                            <input type="email" name="email" class="form-control" value="<?= $e($old['email'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">رمز عبور</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" type="submit">ورود</button>

                            <a
                                class="btn btn-link"
                                href="forgot-password">
                                رمز عبور را فراموش کرده‌اید؟
                            </a>

                            <a
                                class="btn btn-outline-secondary"
                                href="register">
                                ثبت‌نام
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
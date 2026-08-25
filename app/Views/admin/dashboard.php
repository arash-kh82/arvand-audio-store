<?php

declare(strict_types=1);

use App\Core\Session;

$e = static fn($value): string =>
    htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$success = Session::flash('success');
$error = Session::flash('error');

?>

<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $e($title ?? 'پنل مدیریت') ?></title>

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

    <div class="card shadow-sm">

        <div class="card-body">

            <h1 class="h3 mb-3">
                پنل مدیریت فروشگاه
            </h1>

            <p class="text-muted mb-0">
                ورود به پنل مدیریت با موفقیت انجام شد.
            </p>

        </div>

    </div>

</div>

</body>

</html>
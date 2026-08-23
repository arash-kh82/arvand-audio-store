<?php

use App\Core\Session;

$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$user = $user ?? [];
$success = Session::flash('success');
$error = Session::flash('error');
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($title ?? 'حساب کاربری') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="bg-white border rounded-3 p-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                    <h1 class="h4 mb-0"><?= $e($title ?? 'حساب کاربری') ?></h1>
                    <form method="post" action="logout" class="m-0">
                        <?= $csrfField ?? '' ?>
                        <button type="submit" class="btn btn-outline-danger btn-sm">خروج</button>
                    </form>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $e($success) ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $e($error) ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <tbody>
                            <tr>
                                <th class="w-25">شناسه</th>
                                <td><?= $e($user['id'] ?? '') ?></td>
                            </tr>
                            <tr>
                                <th>نام</th>
                                <td><?= $e($user['name'] ?? '') ?></td>
                            </tr>
                            <tr>
                                <th>ایمیل</th>
                                <td><?= $e($user['email'] ?? '') ?></td>
                            </tr>
                            <tr>
                                <th>نقش</th>
                                <td><?= $e($user['role'] ?? 'customer') ?></td>
                            </tr>
                            <tr>
                                <th>وضعیت</th>
                                <td><?= $e($user['status'] ?? 'active') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
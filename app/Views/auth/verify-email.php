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

<title>
<?= $e($title ?? 'تایید ایمیل') ?>
</title>

</head>


<body>

<h1>
تایید ایمیل
</h1>


<?php if ($success): ?>

<p>
<?= $e($success) ?>
</p>

<?php endif; ?>


<?php if ($error): ?>

<p>
<?= $e($error) ?>
</p>

<?php endif; ?>


<form method="post">

<?= $csrfField ?? '' ?>


<label>
کد ارسال شده به ایمیل:
</label>


<br>


<input
    type="text"
    name="code"
    maxlength="6"
    required
>


<br><br>


<button type="submit">
تایید ایمیل
</button>


</form>


</body>

</html>
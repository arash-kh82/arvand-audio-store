<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'فروشگاه صوتی آروند' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: system-ui, -apple-system, sans-serif; }
        .hero { background: linear-gradient(135deg, #1e293b, #0f172a); color: white; padding: 60px 0; border-radius: 0 0 25px 25px; }
        .card-product { transition: transform 0.2s, box-shadow 0.2s; border: none; border-radius: 15px; }
        .card-product:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">🎵 صوتـی آرونــد</a>
    <div class="d-flex">
      <span class="badge bg-primary fs-6">نسخه توسعه (MVC Core)</span>
    </div>
  </div>
</nav>

<header class="hero text-center mb-5">
    <div class="container">
        <h1 class="display-5 fw-bold">فروشگاه تخصصی تجهیزات صوتی و استودیویی</h1>
        <p class="lead text-light opacity-75 mt-3">بررسی، انتخاب و خرید جدیدترین میکروفون‌ها، هدفون‌های استودیویی و اسپیکر مانیتورینگ</p>
    </div>
</header>

<main class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold border-bottom border-3 border-primary pb-2">جدیدترین محصولات</h4>
    </div>

    <div class="row g-4">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $item): ?>
                <div class="col-md-4 col-sm-6">
                    <div class="card card-product h-100 p-3 bg-white">
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-light text-dark align-self-start mb-2"><?= htmlspecialchars($item['brand_name'] ?? 'متفرقه') ?></span>
                            <h5 class="card-title fw-bold"><?= htmlspecialchars($item['name'] ?? $item['title'] ?? 'محصول صوتی') ?></h5>
                            <p class="text-muted small"><?= htmlspecialchars($item['category_name'] ?? 'تجهیزات صوتی') ?></p>
                            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-success fs-5"><?= number_format((float)($item['price'] ?? 0)) ?> تومان</span>
                                <button class="btn btn-outline-primary btn-sm rounded-pill px-3">جزئیات</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning text-center">هنوز هیچ محصولی در دیتابیس ثبت نشده است!</div>
            </div>
        <?php endif; ?>
    </div>
</main>

<footer class="text-center py-4 mt-5 text-muted border-top">
    <small>© 2026 فروشگاه صوتی آروند - معماری MVC</small>
</footer>

</body>
</html>
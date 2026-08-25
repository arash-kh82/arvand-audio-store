<?php

declare(strict_types=1);

use App\Controllers\ProductController;
use App\Controllers\CartController;


/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/

$router->get('/', 'HomeController@index');
$router->get('/home', 'HomeController@index');


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');

$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');

$router->get('/account', 'AuthController@account');
$router->post('/logout', 'AuthController@logout');

$router->get(
    '/verify-email',
    [App\Controllers\VerificationController::class, 'show']
);

$router->post(
    '/verify-email',
    [App\Controllers\VerificationController::class, 'verify']
);

$router->post(
    '/verify-email/resend',
    [App\Controllers\VerificationController::class, 'resend']
);

$router->get(
    '/forgot-password',
    [App\Controllers\PasswordResetController::class, 'showForgotPassword']
);

$router->post(
    '/forgot-password',
    [App\Controllers\PasswordResetController::class, 'sendCode']
);

$router->get(
    '/verify-reset-code',
    [App\Controllers\PasswordResetController::class, 'showVerifyCode']
);

$router->post(
    '/verify-reset-code',
    [App\Controllers\PasswordResetController::class, 'verifyCode']
);

$router->get(
    '/reset-password',
    [App\Controllers\PasswordResetController::class, 'showResetPassword']
);

$router->post(
    '/reset-password',
    [App\Controllers\PasswordResetController::class, 'resetPassword']
);

/*
|--------------------------------------------------------------------------
| Products
|--------------------------------------------------------------------------
*/

$router->get(
    '/products',
    [ProductController::class, 'index']
);

$router->get(
    '/products/{slug}',
    [ProductController::class, 'show']
);

$router->get(
    '/categories/{slug}',
    [ProductController::class, 'category']
);

$router->get(
    '/brands/{slug}',
    [ProductController::class, 'brand']
);


/*
|--------------------------------------------------------------------------
| Cart
|--------------------------------------------------------------------------
*/

$router->get(
    '/cart',
    [CartController::class, 'index']
);

$router->post(
    '/cart/add',
    [CartController::class, 'add']
);

$router->post(
    '/cart/update',
    [CartController::class, 'update']
);

$router->post(
    '/cart/remove',
    [CartController::class, 'remove']
);

$router->post(
    '/cart/clear',
    [CartController::class, 'clear']
);


/*
|--------------------------------------------------------------------------
| Checkout
|--------------------------------------------------------------------------
*/

$router->get(
    '/checkout',
    [App\Controllers\CheckoutController::class, 'index']
);

$router->post(
    '/checkout',
    [App\Controllers\CheckoutController::class, 'store']
);


/*
|--------------------------------------------------------------------------
| Orders
|--------------------------------------------------------------------------
*/

$router->get(
    '/orders/{id}',
    [App\Controllers\OrderController::class, 'show']
);


/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

$router->get(
    '/admin',
    [App\Controllers\AdminDashboardController::class, 'index']
);

$router->get(
    '/admin/products',
    [App\Controllers\AdminProductController::class, 'index']
);

$router->get(
    '/admin/products/create',
    [App\Controllers\AdminProductController::class, 'create']
);

$router->post(
    '/admin/products',
    [App\Controllers\AdminProductController::class, 'store']
);

$router->get(
    '/admin/products/{id}/edit',
    [App\Controllers\AdminProductController::class, 'edit']
);

$router->post(
    '/admin/products/{id}/update',
    [App\Controllers\AdminProductController::class, 'update']
);

$router->post(
    '/admin/products/{id}/delete',
    [App\Controllers\AdminProductController::class, 'delete']
);


/*
|--------------------------------------------------------------------------
| Payments
|--------------------------------------------------------------------------
*/

$router->get(
    '/payment/{orderId}',
    [App\Controllers\PaymentController::class, 'index']
);

$router->post(
    '/payment/{orderId}/success',
    [App\Controllers\PaymentController::class, 'success']
);

$router->post(
    '/payment/{orderId}/failed',
    [App\Controllers\PaymentController::class, 'failed']
);


/*
|--------------------------------------------------------------------------
| Addresses
|--------------------------------------------------------------------------
*/

$router->get(
    '/addresses',
    [App\Controllers\AddressController::class, 'index']
);

$router->post(
    '/addresses',
    [App\Controllers\AddressController::class, 'store']
);

$router->post(
    '/addresses/delete',
    [App\Controllers\AddressController::class, 'delete']
);

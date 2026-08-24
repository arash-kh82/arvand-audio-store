<?php

declare(strict_types=1);

use App\Controllers\ProductController;
use App\Controllers\CartController;

$router->get('/', 'HomeController@index');
$router->get('/home', 'HomeController@index');

$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');

$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');

$router->get('/account', 'AuthController@account');
$router->post('/logout', 'AuthController@logout');

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

$router->get(
    '/checkout',
    [App\Controllers\CheckoutController::class, 'index']
);

$router->post(
    '/checkout',
    [App\Controllers\CheckoutController::class, 'store']
);

$router->get(
    '/orders/{id}',
    [App\Controllers\OrderController::class, 'show']
);

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
<?php

declare(strict_types=1);

use App\Controllers\ProductController;

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
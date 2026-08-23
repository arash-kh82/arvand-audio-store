<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

class HomeController extends Controller {
    public function index(): void {
        $productModel = new Product();
        $products = $productModel->getFeaturedProducts();

        $this->view('home', [
            'title' => 'فروشگاه تخصصی لوازم صوتی آروند',
            'products' => $products
        ]);
    }
}
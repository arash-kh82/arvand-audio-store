<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

class HomeController extends Controller {
    public function index(): void {
        $productModel = new Product();
        $categoryModel = new Category();
        $brandModel = new Brand();
        
        // دریافت محصولات ویژه
        $products = $productModel->getFeaturedProducts(6);
        
        // دریافت دسته‌بندی‌های فعال
        $categories = $categoryModel->getActiveCategories();
        
        // دریافت برندهای فعال
        $brands = $brandModel->getActiveBrands();

        // اگر فایل در views/home.php است
        $this->view('home', [
            'title' => 'فروشگاه تخصصی لوازم صوتی آروند',
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands
        ]);
    }
}
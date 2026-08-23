<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

final class ProductController extends Controller
{
    private Product $products;

    private Category $categories;

    private Brand $brands;

    public function __construct()
    {
        $this->products = new Product();
        $this->categories = new Category();
        $this->brands = new Brand();
    }

    public function index(): void
    {
        $search = trim(
            (string) ($_GET['search'] ?? '')
        );

        if ($search !== '') {
            $products = $this->products->search(
                $search,
                24
            );
        } else {
            $products = $this->products->getActiveProducts(
                24
            );
        }

        $this->view('products/index', [
            'title' => 'محصولات',
            'products' => $products,
            'categories' =>
                $this->categories->getActiveCategories(),
            'brands' =>
                $this->brands->getActiveBrands(),
            'search' => $search,
        ]);
    }

    public function show(string $slug): void
    {
        $product = $this->products->findBySlug($slug);

        if ($product === null) {
            $this->notFound();
        }

        $this->view('products/show', [
            'title' => $product['name'],
            'product' => $product,
        ]);
    }

    public function category(string $slug): void
    {
        $category = $this->categories->findBySlug(
            $slug
        );

        if ($category === null) {
            $this->notFound();
        }

        $products = $this->products->getByCategory(
            (int) $category['id'],
            24
        );

        $this->view('products/category', [
            'title' => $category['name'],
            'category' => $category,
            'products' => $products,
        ]);
    }

    public function brand(string $slug): void
    {
        $brand = $this->brands->findBySlug(
            $slug
        );

        if ($brand === null) {
            $this->notFound();
        }

        $products = $this->products->getByBrand(
            (int) $brand['id'],
            24
        );

        $this->view('products/brand', [
            'title' => $brand['name'],
            'brand' => $brand,
            'products' => $products,
        ]);
    }

    private function notFound(): never
    {
        http_response_code(404);

        echo '404 - محصول یا صفحه مورد نظر پیدا نشد.';
        exit;
    }
}
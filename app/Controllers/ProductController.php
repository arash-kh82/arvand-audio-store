<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Core\Csrf;
use App\Models\ProductImage;

final class ProductController extends Controller
{
    private Product $products;
    private Category $categories;
    private Brand $brands;
    private ProductImage $productImages;

    public function __construct()
    {
        $this->products = new Product();
        $this->categories = new Category();
        $this->brands = new Brand();
        $this->productImages = new ProductImage();
    }

    /**
     * نمایش محصولات و فیلترها
     *
     * پشتیبانی از:
     * - search
     * - category
     * - brand
     * - min_price
     * - max_price
     * - sort
     * - in_stock
     */
    public function index(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) ($_GET['search'] ?? $_GET['q'] ?? '')
        );

        /*
        |--------------------------------------------------------------------------
        | Category
        |--------------------------------------------------------------------------
        */

        $categorySlug = trim(
            (string) ($_GET['category'] ?? '')
        );

        $category = null;

        if ($categorySlug !== '') {
            $category = $this->categories->findBySlug(
                $categorySlug
            );

            if ($category === null) {
                $this->notFound();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Brand
        |--------------------------------------------------------------------------
        */

        $brandSlug = trim(
            (string) ($_GET['brand'] ?? '')
        );

        $brand = null;

        if ($brandSlug !== '') {
            $brand = $this->brands->findBySlug(
                $brandSlug
            );

            if ($brand === null) {
                $this->notFound();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Price
        |--------------------------------------------------------------------------
        */

        $minPrice = $this->getPriceFilter(
            $_GET['min_price'] ?? null
        );

        $maxPrice = $this->getPriceFilter(
            $_GET['max_price'] ?? null
        );

        /*
        |--------------------------------------------------------------------------
        | اگر حداقل قیمت از حداکثر بیشتر باشد،
        | آن‌ها را جابه‌جا می‌کنیم.
        |--------------------------------------------------------------------------
        */

        if (
            $minPrice !== null
            && $maxPrice !== null
            && $minPrice > $maxPrice
        ) {
            [$minPrice, $maxPrice] = [
                $maxPrice,
                $minPrice,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Sort
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            '',
            'newest',
            'price_asc',
            'price_desc',
            'name_asc',
            'name_desc',
        ];

        $sort = (string) (
            $_GET['sort'] ?? ''
        );

        if (!in_array(
            $sort,
            $allowedSorts,
            true
        )) {
            $sort = '';
        }

        /*
        |--------------------------------------------------------------------------
        | Stock
        |--------------------------------------------------------------------------
        */

        $inStock = isset($_GET['in_stock'])
            && (
                $_GET['in_stock'] === '1'
                || $_GET['in_stock'] === 1
                || $_GET['in_stock'] === true
            );

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $filters = [
            'search' => $search,

            'category_id' => $category !== null
                ? (int) $category['id']
                : 0,

            'brand_id' => $brand !== null
                ? (int) $brand['id']
                : 0,

            'min_price' => $minPrice,
            'max_price' => $maxPrice,

            'sort' => $sort,

            'in_stock' => $inStock,
        ];

        /*
        |--------------------------------------------------------------------------
        | دریافت محصولات
        |--------------------------------------------------------------------------
        */

        $products = $this->products->filter(
            $filters,
            24
        );

        /*
        |--------------------------------------------------------------------------
        | دریافت تصاویر محصولات
        |--------------------------------------------------------------------------
        |
        | نکته مهم:
        | تصاویر اصلی از جدول product_images گرفته می‌شوند.
        |
        */

        foreach ($products as &$product) {

            $productId = (int) (
                $product['id'] ?? 0
            );

            $product['images'] = [];

            if ($productId > 0) {

                $product['images'] =
                    $this->productImages->getByProductId(
                        $productId
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | تنظیم تصویر اصلی برای نمایش در لیست محصولات
            |--------------------------------------------------------------------------
            |
            | اگر محصول تصویر اصلی داشت، از همان استفاده می‌شود.
            | در غیر این صورت، اولین تصویر از گالری به عنوان تصویر اصلی تنظیم می‌شود.
            |
            */

            // اگر تصویر اصلی وجود نداشت و گالری تصاویر پر بود
            if (
                empty($product['image'])
                && !empty($product['images'])
                && is_array($product['images'])
            ) {
                // اولین تصویر گالری را به عنوان تصویر اصلی انتخاب کن
                $firstImage = $product['images'][0] ?? null;

                if (is_array($firstImage)) {
                    $product['image'] = $firstImage['image'] ?? '';
                } elseif (is_string($firstImage)) {
                    $product['image'] = $firstImage;
                }
            }
        }

        unset($product);

        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        $this->view('products/index', [

            'title' => 'محصولات',

            'products' => $products,

            'categories' =>
                $this->categories->getActiveCategories(),

            'brands' =>
                $this->brands->getActiveBrands(),

            'search' => $search,

            'selectedCategory' => $categorySlug,

            'selectedBrand' => $brandSlug,

            'selectedMinPrice' => $minPrice,

            'selectedMaxPrice' => $maxPrice,

            'selectedSort' => $sort,

            'inStock' => $inStock,
        ]);
    }

    /**
     * نمایش جزئیات محصول
     */
    public function show(string $slug): void
    {
        $product = $this->products->findBySlug($slug);

        if ($product === null) {
            $this->notFound();
        }

        $images = $this->productImages->getByProductId(
            (int) $product['id']
        );

        $this->view('products/show', [
            'title' => $product['name'],
            'product' => $product,
            'images' => $images,
            'csrfField' => Csrf::field(),
        ]);
    }

    /**
     * نمایش محصولات یک دسته‌بندی
     */
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

    /**
     * نمایش محصولات یک برند
     */
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

    /**
     * تبدیل ورودی قیمت به مقدار معتبر
     */
    private function getPriceFilter(
        mixed $value
    ): ?float {
        if (
            $value === null
            || $value === ''
            || !is_numeric($value)
        ) {
            return null;
        }

        $price = (float) $value;

        if ($price < 0) {
            return null;
        }

        return $price;
    }

    /**
     * نمایش صفحه 404
     */
    private function notFound(): never
    {
        http_response_code(404);

        echo '404 - محصول یا صفحه مورد نظر پیدا نشد.';

        exit;
    }
}
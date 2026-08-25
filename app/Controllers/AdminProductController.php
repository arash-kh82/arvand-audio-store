<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Throwable;

final class AdminProductController extends AdminController
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

    /**
     * لیست محصولات
     */
    public function index(): void
    {
        $this->requireAdmin();

        $this->view('admin/products/index', [
            'title' => 'مدیریت محصولات',
            'products' => $this->products->getAdminProducts(),
            'csrfField' => Csrf::field(),
        ]);
    }

    /**
     * فرم افزودن محصول
     */
    public function create(): void
    {
        $this->requireAdmin();

        $this->view('admin/products/create', [
            'title' => 'افزودن محصول',
            'categories' => $this->categories->getActiveCategories(),
            'brands' => $this->brands->getActiveBrands(),
            'csrfField' => Csrf::field(),
        ]);
    }

    /**
     * ذخیره محصول جدید
     */
    public function store(): void
    {
        $this->requireAdmin();

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            Session::flash(
                'error',
                'درخواست امنیتی نامعتبر است.'
            );

            $this->redirect('/admin/products');
        }

        $data = $this->validateProductData();

        if ($data === null) {
            $this->redirect('/admin/products/create');
        }

        try {
            $this->products->create($data);
        } catch (Throwable $exception) {
            Session::flash(
                'error',
                'ذخیره محصول انجام نشد. ممکن است نام یا SKU تکراری باشد.'
            );

            $this->redirect('/admin/products/create');
        }

        Session::flash(
            'success',
            'محصول با موفقیت اضافه شد.'
        );

        $this->redirect('/admin/products');
    }

    /**
     * فرم ویرایش محصول
     */
    public function edit($id): void
    {
        $this->requireAdmin();

        $id = (int) $id;

        if ($id <= 0) {
            Session::flash(
                'error',
                'شناسه محصول نامعتبر است.'
            );

            $this->redirect('/admin/products');
        }

        $product = $this->products->findAdminById($id);

        if ($product === null) {
            Session::flash(
                'error',
                'محصول مورد نظر پیدا نشد.'
            );

            $this->redirect('/admin/products');
        }

        $this->view(
            'admin/products/edit',
            [
                'title' => 'ویرایش محصول',
                'product' => $product,
                'categories' => $this->categories->getActiveCategories(),
                'brands' => $this->brands->getActiveBrands(),
                'csrfField' => Csrf::field(),
            ]
        );
    }

    /**
     * بروزرسانی محصول
     */
    public function update($id): void
    {
        $this->requireAdmin();

        $id = (int) $id;

        if ($id <= 0) {
            Session::flash(
                'error',
                'شناسه محصول نامعتبر است.'
            );

            $this->redirect('/admin/products');
        }

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            Session::flash(
                'error',
                'درخواست امنیتی نامعتبر است.'
            );

            $this->redirect(
                '/admin/products/' . $id . '/edit'
            );
        }

        $product = $this->products->findAdminById($id);

        if ($product === null) {
            Session::flash(
                'error',
                'محصول مورد نظر پیدا نشد.'
            );

            $this->redirect('/admin/products');
        }

        $name = trim(
            (string) ($_POST['name'] ?? '')
        );

        $slug = trim(
            (string) ($_POST['slug'] ?? '')
        );

        $sku = trim(
            (string) ($_POST['sku'] ?? '')
        );

        $categoryId = (int) (
            $_POST['category_id'] ?? 0
        );

        $brandRaw = $_POST['brand_id'] ?? '';

        $brandId = $brandRaw !== ''
            ? (int) $brandRaw
            : null;

        $description = trim(
            (string) ($_POST['description'] ?? '')
        );

        $priceRaw = $_POST['price'] ?? '';

        $discountRaw = $_POST['discount_price'] ?? '';

        $stock = (int) (
            $_POST['stock'] ?? 0
        );

        $image = trim(
            (string) ($_POST['image'] ?? '')
        );

        $status = (
            $_POST['status'] ?? 'active'
        ) === 'inactive'
            ? 'inactive'
            : 'active';

        $featured = isset($_POST['featured']);

        if (
            $name === ''
            || $slug === ''
            || $sku === ''
            || $categoryId <= 0
            || $priceRaw === ''
            || !is_numeric($priceRaw)
            || (float) $priceRaw < 0
            || $stock < 0
        ) {
            Session::flash(
                'error',
                'اطلاعات محصول صحیح و کامل نیست.'
            );

            $this->redirect(
                '/admin/products/' . $id . '/edit'
            );
        }

        $price = (float) $priceRaw;

        $discountPrice = null;

        if (
            $discountRaw !== ''
            && is_numeric($discountRaw)
            && (float) $discountRaw >= 0
        ) {
            $discountPrice = (float) $discountRaw;

            if ($discountPrice >= $price) {
                Session::flash(
                    'error',
                    'قیمت تخفیف باید کمتر از قیمت اصلی باشد.'
                );

                $this->redirect(
                    '/admin/products/' . $id . '/edit'
                );
            }
        }

        try {
            $updated = $this->products->update(
                $id,
                [
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                    'name' => $name,
                    'slug' => $slug,
                    'sku' => $sku,
                    'description' => $description,
                    'price' => $price,
                    'discount_price' => $discountPrice,
                    'stock' => $stock,
                    'image' => $image,
                    'status' => $status,
                    'featured' => $featured,
                ]
            );
        } catch (Throwable $exception) {
            Session::flash(
                'error',
                'ویرایش محصول انجام نشد. ممکن است نام یا SKU تکراری باشد.'
            );

            $this->redirect(
                '/admin/products/' . $id . '/edit'
            );
        }

        if (!$updated) {
            Session::flash(
                'error',
                'تغییری در محصول ذخیره نشد.'
            );

            $this->redirect(
                '/admin/products/' . $id . '/edit'
            );
        }

        Session::flash(
            'success',
            'محصول با موفقیت ویرایش شد.'
        );

        $this->redirect('/admin/products');
    }

    /**
     * حذف محصول
     */
    public function delete($id): void
    {
        $this->requireAdmin();
        
        $id = (int) $id;

        if (!Csrf::validate($_POST['_token'] ?? null)) {
            Session::flash(
                'error',
                'درخواست امنیتی نامعتبر است.'
            );

            $this->redirect('/admin/products');
        }

        $product = $this->products->findAdminById($id);

        if ($product === null) {
            Session::flash(
                'error',
                'محصول مورد نظر پیدا نشد.'
            );

            $this->redirect('/admin/products');
        }

        try {
            $deleted = $this->products->delete($id);

            if (!$deleted) {
                throw new \RuntimeException(
                    'Product deletion failed.'
                );
            }
        } catch (Throwable $exception) {
            Session::flash(
                'error',
                'حذف محصول انجام نشد.'
            );

            $this->redirect('/admin/products');
        }

        Session::flash(
            'success',
            'محصول با موفقیت حذف شد.'
        );

        $this->redirect('/admin/products');
    }

    /**
     * اعتبارسنجی اطلاعات محصول
     */
    private function validateProductData(): ?array
    {
        $name = trim(
            (string) ($_POST['name'] ?? '')
        );

        $slug = trim(
            (string) ($_POST['slug'] ?? '')
        );

        $sku = trim(
            (string) ($_POST['sku'] ?? '')
        );

        $categoryId = (int) (
            $_POST['category_id'] ?? 0
        );

        $brandRaw = $_POST['brand_id'] ?? '';

        $brandId = $brandRaw !== ''
            ? (int) $brandRaw
            : null;

        $description = trim(
            (string) ($_POST['description'] ?? '')
        );

        $priceRaw = $_POST['price'] ?? '';

        $discountRaw = $_POST['discount_price'] ?? '';

        $stock = (int) (
            $_POST['stock'] ?? 0
        );

        $image = trim(
            (string) ($_POST['image'] ?? '')
        );

        $status = (
            $_POST['status'] ?? 'active'
        ) === 'inactive'
            ? 'inactive'
            : 'active';

        $featured = isset(
            $_POST['featured']
        );

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        if (
            $name === ''
            || $slug === ''
            || $sku === ''
            || $categoryId <= 0
            || $priceRaw === ''
            || !is_numeric($priceRaw)
            || (float) $priceRaw < 0
            || $stock < 0
        ) {
            Session::flash(
                'error',
                'اطلاعات محصول صحیح و کامل نیست.'
            );

            return null;
        }

        $price = (float) $priceRaw;

        $discountPrice = null;

        if (
            $discountRaw !== ''
            && is_numeric($discountRaw)
            && (float) $discountRaw >= 0
        ) {
            $discountPrice = (float) $discountRaw;

            if ($discountPrice >= $price) {
                Session::flash(
                    'error',
                    'قیمت تخفیف باید کمتر از قیمت اصلی باشد.'
                );

                return null;
            }
        }

        return [
            'category_id' => $categoryId,
            'brand_id' => $brandId,
            'name' => $name,
            'slug' => $slug,
            'sku' => $sku,
            'description' => $description,
            'price' => $price,
            'discount_price' => $discountPrice,
            'stock' => $stock,
            'image' => $image,
            'status' => $status,
            'featured' => $featured,
        ];
    }

}
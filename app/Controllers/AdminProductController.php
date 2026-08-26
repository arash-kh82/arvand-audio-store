<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Throwable;
use App\Models\ProductImage;

final class AdminProductController extends AdminController
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
                'images' => $this->productImages->getByProductId(
                    (int) $product['id']
                ),
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
     * آپلود تصویر محصول
     */
    public function uploadImage($id): void
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

        try {

            $file = $_FILES['image'] ?? [];

            $extension =
                $this->productImages->validateUpload(
                    $file
                );

            $filename =
                $this->productImages->storeUploadedFile(
                    $file,
                    $extension
                );

            $imageId =
                $this->productImages->create(
                    $id,
                    $filename,
                    (string) (
                        $_POST['alt_text'] ?? ''
                    )
                );

            $images =
                $this->productImages->getByProductId(
                    $id
                );

            if (count($images) === 1) {
                $this->productImages->setPrimary(
                    $imageId
                );
            }
        } catch (Throwable $exception) {

            if (
                isset($filename)
                && $filename !== ''
            ) {
                $this->productImages->deleteFile(
                    ProductImage::publicPath(
                        $filename
                    )
                );
            }

            Session::flash(
                'error',
                $exception->getMessage()
            );

            $this->redirect(
                '/admin/products/' . $id . '/edit'
            );
        }

        Session::flash(
            'success',
            'تصویر با موفقیت آپلود شد.'
        );

        $this->redirect(
            '/admin/products/' . $id . '/edit'
        );
    }


    /**
     * تعیین تصویر اصلی
     */
    public function setPrimaryImage($id, $imageId): void
    {
        $this->requireAdmin();

        $id = (int) $id;
        $imageId = (int) $imageId;

        if ($id <= 0 || $imageId <= 0) {
            Session::flash(
                'error',
                'شناسه تصویر یا محصول نامعتبر است.'
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

        $image =
            $this->productImages->findById(
                $imageId
            );

        if (
            $image === null
            || (int) $image['product_id'] !== $id
        ) {
            Session::flash(
                'error',
                'تصویر مورد نظر پیدا نشد.'
            );

            $this->redirect(
                '/admin/products/' . $id . '/edit'
            );
        }

        try {

            $updated =
                $this->productImages->setPrimary(
                    $imageId
                );
        } catch (Throwable $exception) {

            Session::flash(
                'error',
                'تغییر تصویر اصلی انجام نشد.'
            );

            $this->redirect(
                '/admin/products/' . $id . '/edit'
            );
        }

        if (!$updated) {
            Session::flash(
                'error',
                'تغییر تصویر اصلی انجام نشد.'
            );

            $this->redirect(
                '/admin/products/' . $id . '/edit'
            );
        }

        Session::flash(
            'success',
            'تصویر اصلی با موفقیت تغییر کرد.'
        );

        $this->redirect(
            '/admin/products/' . $id . '/edit'
        );
    }


    /**
     * حذف تصویر محصول
     */
    public function deleteImage($id, $imageId): void
    {
        $this->requireAdmin();

        $id = (int) $id;
        $imageId = (int) $imageId;

        if ($id <= 0 || $imageId <= 0) {
            Session::flash(
                'error',
                'شناسه تصویر یا محصول نامعتبر است.'
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

        $image =
            $this->productImages->findById(
                $imageId
            );

        if (
            $image === null
            || (int) $image['product_id'] !== $id
        ) {
            Session::flash(
                'error',
                'تصویر مورد نظر پیدا نشد.'
            );

            $this->redirect(
                '/admin/products/' . $id . '/edit'
            );
        }

        try {

            $deleted =
                $this->productImages->delete(
                    $imageId
                );

            if ($deleted !== null) {
                $this->productImages->deleteFile(
                    (string) $deleted['image']
                );
            }
        } catch (Throwable $exception) {

            Session::flash(
                'error',
                'حذف تصویر انجام نشد.'
            );

            $this->redirect(
                '/admin/products/' . $id . '/edit'
            );
        }

        Session::flash(
            'success',
            'تصویر با موفقیت حذف شد.'
        );

        $this->redirect(
            '/admin/products/' . $id . '/edit'
        );
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

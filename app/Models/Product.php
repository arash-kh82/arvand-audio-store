<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Product extends Model
{
    /**
     * دریافت محصولات ویژه و فعال
     */
    public function getFeaturedProducts(int $limit = 6): array
    {
        $limit = max(1, min($limit, 50));

        $stmt = $this->db->prepare("
            SELECT
                p.*,
                b.name AS brand_name,
                c.name AS category_name
            FROM products p
            LEFT JOIN brands b
                ON p.brand_id = b.id
            INNER JOIN categories c
                ON p.category_id = c.id
            WHERE p.status = 'active'
              AND p.featured = 1
              AND c.status = 1
            ORDER BY p.id DESC
            LIMIT :limit
        ");

        $stmt->bindValue(
            ':limit',
            $limit,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * دریافت محصولات فعال
     */
    public function getActiveProducts(int $limit = 12): array
    {
        $limit = max(1, min($limit, 100));

        $stmt = $this->db->prepare("
            SELECT
                p.*,
                b.name AS brand_name,
                c.name AS category_name
            FROM products p
            LEFT JOIN brands b
                ON p.brand_id = b.id
            INNER JOIN categories c
                ON p.category_id = c.id
            WHERE p.status = 'active'
              AND c.status = 1
            ORDER BY p.id DESC
            LIMIT :limit
        ");

        $stmt->bindValue(
            ':limit',
            $limit,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * دریافت محصول با ID
     *
     * فقط محصولات فعال قابل نمایش عمومی هستند.
     */
    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT
                p.*,
                b.name AS brand_name,
                b.slug AS brand_slug,
                c.name AS category_name,
                c.slug AS category_slug
            FROM products p
            LEFT JOIN brands b
                ON p.brand_id = b.id
            INNER JOIN categories c
                ON p.category_id = c.id
            WHERE p.id = :id
              AND p.status = 'active'
              AND c.status = 1
            LIMIT 1
        ");

        $stmt->execute([
            ':id' => $id,
        ]);

        $product = $stmt->fetch();

        return $product !== false ? $product : null;
    }

    /**
     * دریافت محصول با Slug
     */
    public function findBySlug(string $slug): ?array
    {
        $slug = trim($slug);

        if ($slug === '') {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT
                p.*,
                b.name AS brand_name,
                b.slug AS brand_slug,
                c.name AS category_name,
                c.slug AS category_slug
            FROM products p
            LEFT JOIN brands b
                ON p.brand_id = b.id
            INNER JOIN categories c
                ON p.category_id = c.id
            WHERE p.slug = :slug
              AND p.status = 'active'
              AND c.status = 1
            LIMIT 1
        ");

        $stmt->execute([
            ':slug' => $slug,
        ]);

        $product = $stmt->fetch();

        return $product !== false ? $product : null;
    }

    /**
     * جستجوی محصولات
     */
    public function search(
        string $keyword,
        int $limit = 12
    ): array {
        $keyword = trim($keyword);

        if ($keyword === '') {
            return [];
        }

        $limit = max(1, min($limit, 100));

        $search = '%' . $keyword . '%';

        $stmt = $this->db->prepare("
            SELECT
                p.*,
                b.name AS brand_name,
                c.name AS category_name
            FROM products p
            LEFT JOIN brands b
                ON p.brand_id = b.id
            INNER JOIN categories c
                ON p.category_id = c.id
            WHERE p.status = 'active'
              AND c.status = 1
              AND (
                    p.name LIKE :search_name
                    OR p.sku LIKE :search_sku
                    OR p.description LIKE :search_description
                    OR b.name LIKE :search_brand
                    OR c.name LIKE :search_category
              )
            ORDER BY
                CASE
                    WHEN p.name LIKE :exact_name THEN 1
                    WHEN b.name LIKE :exact_brand THEN 2
                    ELSE 3
                END,
                p.id DESC
            LIMIT :limit
        ");

        $stmt->bindValue(
            ':search_name',
            $search,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':search_sku',
            $search,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':search_description',
            $search,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':search_brand',
            $search,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':search_category',
            $search,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':exact_name',
            $search,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':exact_brand',
            $search,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':limit',
            $limit,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * محصولات یک دسته‌بندی
     */
    public function getByCategory(
        int $categoryId,
        int $limit = 12
    ): array {
        if ($categoryId <= 0) {
            return [];
        }

        $limit = max(1, min($limit, 100));

        $stmt = $this->db->prepare("
            SELECT
                p.*,
                b.name AS brand_name,
                c.name AS category_name
            FROM products p
            LEFT JOIN brands b
                ON p.brand_id = b.id
            INNER JOIN categories c
                ON p.category_id = c.id
            WHERE p.category_id = :category_id
              AND p.status = 'active'
              AND c.status = 1
            ORDER BY p.id DESC
            LIMIT :limit
        ");

        $stmt->bindValue(
            ':category_id',
            $categoryId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':limit',
            $limit,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * محصولات یک برند
     */
    public function getByBrand(
        int $brandId,
        int $limit = 12
    ): array {
        if ($brandId <= 0) {
            return [];
        }

        $limit = max(1, min($limit, 100));

        $stmt = $this->db->prepare("
            SELECT
                p.*,
                b.name AS brand_name,
                c.name AS category_name
            FROM products p
            INNER JOIN brands b
                ON p.brand_id = b.id
            INNER JOIN categories c
                ON p.category_id = c.id
            WHERE p.brand_id = :brand_id
              AND p.status = 'active'
              AND c.status = 1
              AND b.status = 1
            ORDER BY p.id DESC
            LIMIT :limit
        ");

        $stmt->bindValue(
            ':brand_id',
            $brandId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':limit',
            $limit,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * بررسی وجود موجودی کافی
     */
    public function hasStock(
        int $productId,
        int $quantity = 1
    ): bool {
        if ($productId <= 0 || $quantity <= 0) {
            return false;
        }

        $stmt = $this->db->prepare("
            SELECT stock
            FROM products
            WHERE id = :id
              AND status = 'active'
            LIMIT 1
        ");

        $stmt->execute([
            ':id' => $productId,
        ]);

        $stock = $stmt->fetchColumn();

        if ($stock === false) {
            return false;
        }

        return (int) $stock >= $quantity;
    }

    /**
     * دریافت محصولات با فیلترهای ترکیبی
     *
     * فیلترهای قابل استفاده:
     * - search
     * - category_id
     * - brand_id
     * - min_price
     * - max_price
     * - in_stock
     * - sort
     */
    public function filter(array $filters = [], int $limit = 12): array
    {
        $limit = max(1, min($limit, 100));

        $where = [
            "p.status = 'active'",
            "c.status = 1",
            "b.status = 1",
        ];

        $params = [];

        /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

        $search = trim(
            (string) ($filters['search'] ?? '')
        );

        if ($search !== '') {
            $where[] = "
            (
                p.name LIKE :search_name
                OR p.sku LIKE :search_sku
                OR p.description LIKE :search_description
                OR b.name LIKE :search_brand
                OR c.name LIKE :search_category
            )
        ";

            $searchValue = '%' . $search . '%';

            $params[':search_name'] = $searchValue;
            $params[':search_sku'] = $searchValue;
            $params[':search_description'] = $searchValue;
            $params[':search_brand'] = $searchValue;
            $params[':search_category'] = $searchValue;
        }

        /*
    |--------------------------------------------------------------------------
    | Category
    |--------------------------------------------------------------------------
    */

        $categoryId = (int) ($filters['category_id'] ?? 0);

        if ($categoryId > 0) {
            $where[] = "p.category_id = :category_id";

            $params[':category_id'] = $categoryId;
        }

        /*
    |--------------------------------------------------------------------------
    | Brand
    |--------------------------------------------------------------------------
    */

        $brandId = (int) ($filters['brand_id'] ?? 0);

        if ($brandId > 0) {
            $where[] = "p.brand_id = :brand_id";

            $params[':brand_id'] = $brandId;
        }

        /*
    |--------------------------------------------------------------------------
    | Minimum Price
    |--------------------------------------------------------------------------
    */

        $minPrice = $filters['min_price'] ?? null;

        if (
            $minPrice !== null
            && $minPrice !== ''
            && is_numeric($minPrice)
            && (float) $minPrice >= 0
        ) {
            $where[] = "
            COALESCE(
                NULLIF(p.discount_price, 0),
                p.price
            ) >= :min_price
        ";

            $params[':min_price'] = (float) $minPrice;
        }

        /*
    |--------------------------------------------------------------------------
    | Maximum Price
    |--------------------------------------------------------------------------
    */

        $maxPrice = $filters['max_price'] ?? null;

        if (
            $maxPrice !== null
            && $maxPrice !== ''
            && is_numeric($maxPrice)
            && (float) $maxPrice >= 0
        ) {
            $where[] = "
            COALESCE(
                NULLIF(p.discount_price, 0),
                p.price
            ) <= :max_price
        ";

            $params[':max_price'] = (float) $maxPrice;
        }

        /*
    |--------------------------------------------------------------------------
    | فقط محصولات موجود
    |--------------------------------------------------------------------------
    */

        if (
            isset($filters['in_stock'])
            && (
                $filters['in_stock'] === true
                || (int) $filters['in_stock'] === 1
            )
        ) {
            $where[] = "p.stock > 0";
        }

        /*
    |--------------------------------------------------------------------------
    | Sort
    |--------------------------------------------------------------------------
    |
    | مقدار sort مستقیماً وارد SQL نمی‌شود.
    | فقط مقادیر مشخص‌شده در whitelist مجاز هستند.
    |
    */

        $sort = (string) ($filters['sort'] ?? 'newest');

        $orderBy = match ($sort) {
            'price_asc' => "
            COALESCE(
                NULLIF(p.discount_price, 0),
                p.price
            ) ASC,
            p.id DESC
        ",

            'price_desc' => "
            COALESCE(
                NULLIF(p.discount_price, 0),
                p.price
            ) DESC,
            p.id DESC
        ",

            'name_asc' => "
            p.name ASC,
            p.id DESC
        ",

            'name_desc' => "
            p.name DESC,
            p.id DESC
        ",

            default => "
            p.id DESC
        ",
        };

        /*
    |--------------------------------------------------------------------------
    | Query
    |--------------------------------------------------------------------------
    */

        $sql = "
        SELECT
            p.*,
            b.name AS brand_name,
            b.slug AS brand_slug,
            c.name AS category_name,
            c.slug AS category_slug
        FROM products p

        INNER JOIN brands b
            ON p.brand_id = b.id

        INNER JOIN categories c
            ON p.category_id = c.id

        WHERE " . implode(
            ' AND ',
            $where
        ) . "

        ORDER BY
            {$orderBy}

        LIMIT :limit
    ";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue(
                    $key,
                    $value,
                    PDO::PARAM_INT
                );
            } else {
                $stmt->bindValue(
                    $key,
                    $value
                );
            }
        }

        $stmt->bindValue(
            ':limit',
            $limit,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }
    /** * دریافت همه محصولات برای پنل مدیریت */ public function getAdminProducts(): array
    {
        $stmt = $this->db->query(" SELECT p.*, b.name AS brand_name, c.name AS category_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id INNER JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC ");
        return $stmt->fetchAll();
    }

    /** * ایجاد محصول جدید */ public function create(array $data): int
    {
        $stmt = $this->db->prepare(" INSERT INTO products ( category_id, brand_id, name, slug, sku, description, price, discount_price, stock, image, status, featured ) VALUES ( :category_id, :brand_id, :name, :slug, :sku, :description, :price, :discount_price, :stock, :image, :status, :featured ) ");
        $stmt->execute([':category_id' => (int) $data['category_id'], ':brand_id' => $data['brand_id'] !== null ? (int) $data['brand_id'] : null, ':name' => trim((string) $data['name']), ':slug' => trim((string) $data['slug']), ':sku' => trim((string) $data['sku']), ':description' => trim((string) ($data['description'] ?? '')), ':price' => (float) $data['price'], ':discount_price' => $data['discount_price'] !== null ? (float) $data['discount_price'] : null, ':stock' => (int) $data['stock'], ':image' => ($data['image'] ?? '') !== '' ? trim((string) $data['image']) : null, ':status' => $data['status'] ?? 'active', ':featured' => !empty($data['featured']) ? 1 : 0,]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * دریافت محصول برای پنل مدیریت
     */
    public function findAdminById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare("
        SELECT
            p.*,
            b.name AS brand_name,
            c.name AS category_name
        FROM products p
        LEFT JOIN brands b
            ON p.brand_id = b.id
        INNER JOIN categories c
            ON p.category_id = c.id
        WHERE p.id = :id
        LIMIT 1
    ");

        $stmt->execute([
            ':id' => $id,
        ]);

        $product = $stmt->fetch();

        return $product !== false ? $product : null;
    }

    /**
     * ویرایش محصول
     */
    public function update(int $id, array $data): bool
    {
        if ($id <= 0) {
            return false;
        }

        $stmt = $this->db->prepare("
        UPDATE products
        SET
            category_id = :category_id,
            brand_id = :brand_id,
            name = :name,
            slug = :slug,
            sku = :sku,
            description = :description,
            price = :price,
            discount_price = :discount_price,
            stock = :stock,
            image = :image,
            status = :status,
            featured = :featured
        WHERE id = :id
    ");

        return $stmt->execute([
            ':id' => $id,
            ':category_id' => (int) $data['category_id'],
            ':brand_id' => $data['brand_id'] !== null
                ? (int) $data['brand_id']
                : null,
            ':name' => trim((string) $data['name']),
            ':slug' => trim((string) $data['slug']),
            ':sku' => trim((string) $data['sku']),
            ':description' => trim(
                (string) ($data['description'] ?? '')
            ),
            ':price' => (float) $data['price'],
            ':discount_price' => $data['discount_price'] !== null
                ? (float) $data['discount_price']
                : null,
            ':stock' => (int) $data['stock'],
            ':image' => ($data['image'] ?? '') !== ''
                ? trim((string) $data['image'])
                : null,
            ':status' => $data['status'] ?? 'active',
            ':featured' => !empty($data['featured']) ? 1 : 0,
        ]);
    }

    /**
     * حذف محصول
     */
    public function delete(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $stmt = $this->db->prepare("
        DELETE FROM products
        WHERE id = :id
        LIMIT 1
    ");

        return $stmt->execute([
            ':id' => $id,
        ]);
    }
}

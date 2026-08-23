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
}
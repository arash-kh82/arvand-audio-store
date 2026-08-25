<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Brand extends Model
{
    /**
     * دریافت برندهای فعال برای بخش عمومی سایت
     */
    public function getActiveBrands(): array
    {
        $stmt = $this->db->query("
            SELECT
                id,
                name,
                slug,
                logo
            FROM brands
            WHERE status = 1
            ORDER BY name ASC
        ");

        return $stmt->fetchAll();
    }

    /**
     * دریافت یک برند فعال با ID
     */
    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT *
            FROM brands
            WHERE id = :id
              AND status = 1
            LIMIT 1
        ");

        $stmt->execute([
            ':id' => $id,
        ]);

        $brand = $stmt->fetch();

        return $brand !== false ? $brand : null;
    }

    /**
     * دریافت یک برند فعال با Slug
     */
    public function findBySlug(string $slug): ?array
    {
        $slug = trim($slug);

        if ($slug === '') {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT *
            FROM brands
            WHERE slug = :slug
              AND status = 1
            LIMIT 1
        ");

        $stmt->execute([
            ':slug' => $slug,
        ]);

        $brand = $stmt->fetch();

        return $brand !== false ? $brand : null;
    }

    /**
     * دریافت لیست برندها برای پنل مدیریت
     */
    public function getAdminBrands(
        string $search = '',
        string $status = ''
    ): array {
        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = "(
                b.name LIKE :search_name
                OR b.slug LIKE :search_slug
            )";

            $searchValue = '%' . $search . '%';

            $params[':search_name'] = $searchValue;
            $params[':search_slug'] = $searchValue;
        }

        if ($status !== '') {
            $where[] = 'b.status = :status';

            $params[':status'] = (int) $status;
        }

        $whereSql = '';

        if ($where !== []) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        $sql = "
            SELECT
                b.id,
                b.name,
                b.slug,
                b.logo,
                b.status,
                b.created_at,
                b.updated_at,
                COUNT(p.id) AS products_count
            FROM brands b

            LEFT JOIN products p
                ON p.brand_id = b.id

            {$whereSql}

            GROUP BY
                b.id,
                b.name,
                b.slug,
                b.logo,
                b.status,
                b.created_at,
                b.updated_at

            ORDER BY b.id DESC
        ";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(
                $key,
                $value,
                is_int($value)
                    ? PDO::PARAM_INT
                    : PDO::PARAM_STR
            );
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * دریافت یک برند برای پنل مدیریت
     */
    public function findAdminById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT
                b.*,
                COUNT(p.id) AS products_count
            FROM brands b

            LEFT JOIN products p
                ON p.brand_id = b.id

            WHERE b.id = :id

            GROUP BY
                b.id,
                b.name,
                b.slug,
                b.logo,
                b.status,
                b.created_at,
                b.updated_at

            LIMIT 1
        ");

        $stmt->execute([
            ':id' => $id,
        ]);

        $brand = $stmt->fetch();

        return $brand !== false ? $brand : null;
    }

    /**
     * بررسی تکراری بودن Slug
     */
    public function slugExists(
        string $slug,
        int $exceptId = 0
    ): bool {
        $slug = trim($slug);

        if ($slug === '') {
            return false;
        }

        $sql = "
            SELECT COUNT(*)
            FROM brands
            WHERE slug = :slug
        ";

        $params = [
            ':slug' => $slug,
        ];

        if ($exceptId > 0) {
            $sql .= "
                AND id <> :except_id
            ";

            $params[':except_id'] = $exceptId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * ایجاد برند
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO brands (
                name,
                slug,
                logo,
                status
            ) VALUES (
                :name,
                :slug,
                :logo,
                :status
            )
        ");

        $stmt->bindValue(
            ':name',
            trim((string) $data['name']),
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':slug',
            trim((string) $data['slug']),
            PDO::PARAM_STR
        );

        $logo = trim((string) ($data['logo'] ?? ''));

        $stmt->bindValue(
            ':logo',
            $logo !== '' ? $logo : null,
            $logo !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL
        );

        $stmt->bindValue(
            ':status',
            !empty($data['status']) ? 1 : 0,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    /**
     * ویرایش برند
     */
    public function update(
        int $id,
        array $data
    ): bool {
        if ($id <= 0) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE brands
            SET
                name = :name,
                slug = :slug,
                logo = :logo,
                status = :status,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':name',
            trim((string) $data['name']),
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':slug',
            trim((string) $data['slug']),
            PDO::PARAM_STR
        );

        $logo = trim((string) ($data['logo'] ?? ''));

        $stmt->bindValue(
            ':logo',
            $logo !== '' ? $logo : null,
            $logo !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL
        );

        $stmt->bindValue(
            ':status',
            !empty($data['status']) ? 1 : 0,
            PDO::PARAM_INT
        );

        return $stmt->execute();
    }

    /**
     * تغییر وضعیت برند
     */
    public function updateStatus(
        int $id,
        int $status
    ): bool {
        if ($id <= 0) {
            return false;
        }

        $status = $status === 1 ? 1 : 0;

        $stmt = $this->db->prepare("
            UPDATE brands
            SET
                status = :status,
                updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            ':status' => $status,
            ':id' => $id,
        ]);
    }

    /**
     * بررسی وجود محصول با این برند
     */
    public function hasProducts(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM products
            WHERE brand_id = :brand_id
        ");

        $stmt->execute([
            ':brand_id' => $id,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * حذف برند
     */
    public function delete(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        /*
         * اگر محصولی به این برند متصل باشد،
         * حذف انجام نمی‌شود.
         */
        if ($this->hasProducts($id)) {
            return false;
        }

        $stmt = $this->db->prepare("
            DELETE FROM brands
            WHERE id = :id
            LIMIT 1
        ");

        return $stmt->execute([
            ':id' => $id,
        ]);
    }
}
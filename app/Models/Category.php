<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Category extends Model
{
    /**
     * دریافت دسته‌بندی‌های فعال برای بخش عمومی سایت
     */
    public function getActiveCategories(): array
    {
        $stmt = $this->db->query("
            SELECT
                id,
                parent_id,
                name,
                slug,
                description,
                image
            FROM categories
            WHERE status = 1
            ORDER BY name ASC
        ");

        return $stmt->fetchAll();
    }

    /**
     * دریافت یک دسته‌بندی فعال با ID
     */
    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT *
            FROM categories
            WHERE id = :id
              AND status = 1
            LIMIT 1
        ");

        $stmt->execute([
            ':id' => $id,
        ]);

        $category = $stmt->fetch();

        return $category !== false ? $category : null;
    }

    /**
     * دریافت یک دسته‌بندی فعال با Slug
     */
    public function findBySlug(string $slug): ?array
    {
        $slug = trim($slug);

        if ($slug === '') {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT *
            FROM categories
            WHERE slug = :slug
              AND status = 1
            LIMIT 1
        ");

        $stmt->execute([
            ':slug' => $slug,
        ]);

        $category = $stmt->fetch();

        return $category !== false ? $category : null;
    }

    /**
     * دریافت لیست دسته‌بندی‌ها برای پنل مدیریت
     */
    public function getAdminCategories(
        string $search = '',
        string $status = ''
    ): array {
        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = "(
                c.name LIKE :search_name
                OR c.slug LIKE :search_slug
            )";

            $searchValue = '%' . $search . '%';

            $params[':search_name'] = $searchValue;
            $params[':search_slug'] = $searchValue;
        }

        if ($status !== '') {
            $where[] = 'c.status = :status';

            $params[':status'] = (int) $status;
        }

        $whereSql = '';

        if ($where !== []) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        $sql = "
            SELECT
                c.id,
                c.parent_id,
                c.name,
                c.slug,
                c.description,
                c.image,
                c.status,
                c.created_at,
                c.updated_at,
                p.name AS parent_name,
                COUNT(pr.id) AS products_count
            FROM categories c

            LEFT JOIN categories p
                ON c.parent_id = p.id

            LEFT JOIN products pr
                ON pr.category_id = c.id

            {$whereSql}

            GROUP BY
                c.id,
                c.parent_id,
                c.name,
                c.slug,
                c.description,
                c.image,
                c.status,
                c.created_at,
                c.updated_at,
                p.name

            ORDER BY c.id DESC
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
     * دریافت یک دسته‌بندی برای پنل مدیریت
     */
    public function findAdminById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT
                c.*,
                p.name AS parent_name,
                COUNT(pr.id) AS products_count
            FROM categories c

            LEFT JOIN categories p
                ON c.parent_id = p.id

            LEFT JOIN products pr
                ON pr.category_id = c.id

            WHERE c.id = :id

            GROUP BY
                c.id,
                c.parent_id,
                c.name,
                c.slug,
                c.description,
                c.image,
                c.status,
                c.created_at,
                c.updated_at,
                p.name

            LIMIT 1
        ");

        $stmt->execute([
            ':id' => $id,
        ]);

        $category = $stmt->fetch();

        return $category !== false ? $category : null;
    }

    /**
     * دریافت دسته‌بندی‌های قابل انتخاب به‌عنوان والد
     */
    public function getParentOptions(int $exceptId = 0): array
    {
        $sql = "
            SELECT
                id,
                parent_id,
                name,
                slug
            FROM categories
        ";

        $params = [];

        if ($exceptId > 0) {
            $sql .= "
                WHERE id <> :except_id
            ";

            $params[':except_id'] = $exceptId;
        }

        $sql .= "
            ORDER BY name ASC
        ";

        $stmt = $this->db->prepare($sql);

        if ($params !== []) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll();
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
            FROM categories
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
     * ایجاد دسته‌بندی
     */
    public function create(array $data): int
    {
        $parentId = isset($data['parent_id'])
            && $data['parent_id'] !== ''
            ? (int) $data['parent_id']
            : null;

        $stmt = $this->db->prepare("
            INSERT INTO categories (
                parent_id,
                name,
                slug,
                description,
                image,
                status
            ) VALUES (
                :parent_id,
                :name,
                :slug,
                :description,
                :image,
                :status
            )
        ");

        $stmt->bindValue(
            ':parent_id',
            $parentId,
            $parentId === null
                ? PDO::PARAM_NULL
                : PDO::PARAM_INT
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

        $stmt->bindValue(
            ':description',
            trim((string) ($data['description'] ?? '')),
            PDO::PARAM_STR
        );

        $image = trim((string) ($data['image'] ?? ''));

        $stmt->bindValue(
            ':image',
            $image !== '' ? $image : null,
            $image !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL
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
     * ویرایش دسته‌بندی
     */
    public function update(
        int $id,
        array $data
    ): bool {
        if ($id <= 0) {
            return false;
        }

        $parentId = isset($data['parent_id'])
            && $data['parent_id'] !== ''
            ? (int) $data['parent_id']
            : null;

        /*
         * جلوگیری از اینکه دسته‌بندی والد خودش شود.
         */
        if ($parentId !== null && $parentId === $id) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE categories
            SET
                parent_id = :parent_id,
                name = :name,
                slug = :slug,
                description = :description,
                image = :image,
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
            ':parent_id',
            $parentId,
            $parentId === null
                ? PDO::PARAM_NULL
                : PDO::PARAM_INT
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

        $stmt->bindValue(
            ':description',
            trim((string) ($data['description'] ?? '')),
            PDO::PARAM_STR
        );

        $image = trim((string) ($data['image'] ?? ''));

        $stmt->bindValue(
            ':image',
            $image !== '' ? $image : null,
            $image !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL
        );

        $stmt->bindValue(
            ':status',
            !empty($data['status']) ? 1 : 0,
            PDO::PARAM_INT
        );

        return $stmt->execute();
    }

    /**
     * تغییر وضعیت دسته‌بندی
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
            UPDATE categories
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
     * بررسی وجود محصول در دسته‌بندی
     */
    public function hasProducts(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM products
            WHERE category_id = :category_id
        ");

        $stmt->execute([
            ':category_id' => $id,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * بررسی وجود زیرمجموعه
     */
    public function hasChildren(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM categories
            WHERE parent_id = :parent_id
        ");

        $stmt->execute([
            ':parent_id' => $id,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * حذف دسته‌بندی
     */
    public function delete(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        if ($this->hasProducts($id)) {
            return false;
        }

        if ($this->hasChildren($id)) {
            return false;
        }

        $stmt = $this->db->prepare("
            DELETE FROM categories
            WHERE id = :id
            LIMIT 1
        ");

        return $stmt->execute([
            ':id' => $id,
        ]);
    }
}
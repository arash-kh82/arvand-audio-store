<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class AdminUser extends Model
{
    /**
     * دریافت لیست کاربران
     */
    public function getUsers(
        string $search = '',
        string $role = '',
        string $status = '',
        int $page = 1,
        int $perPage = 10
    ): array {
        $page = max(1, $page);
        $perPage = max(1, min($perPage, 100));

        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = '(
                name LIKE :search_name
                OR email LIKE :search_email
            )';

            $searchValue = '%' . $search . '%';

            $params[':search_name'] = $searchValue;
            $params[':search_email'] = $searchValue;
        }

        if ($role !== '') {
            $where[] = 'role = :role';
            $params[':role'] = $role;
        }

        if ($status !== '') {
            $where[] = 'status = :status';
            $params[':status'] = $status;
        }

        $whereSql = '';

        if ($where !== []) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        $sql = "
            SELECT
                id,
                name,
                email,
                role,
                status,
                created_at,
                updated_at
            FROM users
            {$whereSql}
            ORDER BY id DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(
                $key,
                $value,
                PDO::PARAM_STR
            );
        }

        $stmt->bindValue(
            ':limit',
            $perPage,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':offset',
            $offset,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * تعداد کاربران مطابق فیلترها
     */
    public function countUsers(
        string $search = '',
        string $role = '',
        string $status = ''
    ): int {
        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = '(
                name LIKE :search_name
                OR email LIKE :search_email
            )';

            $searchValue = '%' . $search . '%';

            $params[':search_name'] = $searchValue;
            $params[':search_email'] = $searchValue;
        }

        if ($role !== '') {
            $where[] = 'role = :role';
            $params[':role'] = $role;
        }

        if ($status !== '') {
            $where[] = 'status = :status';
            $params[':status'] = $status;
        }

        $whereSql = '';

        if ($where !== []) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        $sql = "
            SELECT COUNT(*)
            FROM users
            {$whereSql}
        ";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(
                $key,
                $value,
                PDO::PARAM_STR
            );
        }

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * دریافت یک کاربر
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "
            SELECT
                id,
                name,
                email,
                role,
                status,
                created_at,
                updated_at
            FROM users
            WHERE id = :id
            LIMIT 1
            "
        );

        $stmt->execute([
            ':id' => $id,
        ]);

        $user = $stmt->fetch();

        return $user !== false ? $user : null;
    }

    /**
     * بررسی وجود ایمیل برای کاربر دیگر
     */
    public function emailExists(
        string $email,
        int $exceptUserId = 0
    ): bool {
        $sql = "
            SELECT COUNT(*)
            FROM users
            WHERE email = :email
        ";

        $params = [
            ':email' => $email,
        ];

        if ($exceptUserId > 0) {
            $sql .= ' AND id <> :id';

            $params[':id'] = $exceptUserId;
        }

        $stmt = $this->db->prepare($sql);

        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * ویرایش اطلاعات پایه کاربر
     */
    public function updateUser(
        int $id,
        string $name,
        string $email
    ): bool {
        $stmt = $this->db->prepare(
            "
            UPDATE users
            SET
                name = :name,
                email = :email,
                updated_at = NOW()
            WHERE id = :id
            "
        );

        return $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':id' => $id,
        ]);
    }

    /**
     * تغییر نقش کاربر
     */
    public function updateRole(
        int $id,
        string $role
    ): bool {
        $stmt = $this->db->prepare(
            "
            UPDATE users
            SET
                role = :role,
                updated_at = NOW()
            WHERE id = :id
            "
        );

        return $stmt->execute([
            ':role' => $role,
            ':id' => $id,
        ]);
    }

    /**
     * تغییر وضعیت کاربر
     */
    public function updateStatus(
        int $id,
        string $status
    ): bool {
        $stmt = $this->db->prepare(
            "
            UPDATE users
            SET
                status = :status,
                updated_at = NOW()
            WHERE id = :id
            "
        );

        return $stmt->execute([
            ':status' => $status,
            ':id' => $id,
        ]);
    }

    /**
     * آمار کاربران
     */
    public function getStatistics(): array
    {
        $statistics = [];

        $statistics['total'] = (int) $this->db
            ->query(
                'SELECT COUNT(*) FROM users'
            )
            ->fetchColumn();

        $statistics['customers'] = (int) $this->db
            ->query(
                "SELECT COUNT(*)
                 FROM users
                 WHERE role = 'customer'"
            )
            ->fetchColumn();

        $statistics['admins'] = (int) $this->db
            ->query(
                "SELECT COUNT(*)
                 FROM users
                 WHERE role = 'admin'"
            )
            ->fetchColumn();

        $statistics['active'] = (int) $this->db
            ->query(
                "SELECT COUNT(*)
                 FROM users
                 WHERE status = 'active'"
            )
            ->fetchColumn();

        $statistics['inactive'] = (int) $this->db
            ->query(
                "SELECT COUNT(*)
                 FROM users
                 WHERE status <> 'active'"
            )
            ->fetchColumn();

        return $statistics;
    }
}
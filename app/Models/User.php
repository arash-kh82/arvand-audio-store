<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        return $user !== false ? $user : null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        return $user !== false ? $user : null;
    }

    public function findByLogin(string $login): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT *
             FROM users
             WHERE email = :login
                OR phone = :login
             LIMIT 1'
        );

        $stmt->execute([
            ':login' => $login,
        ]);

        $user = $stmt->fetch();

        return $user !== false ? $user : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password, role, status, created_at, updated_at)
             VALUES (:name, :email, :password, :role, :status, NOW(), NOW())'
        );

        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':password' => $data['password'],
            ':role' => $data['role'] ?? 'customer',
            ':status' => $data['status'] ?? 'active',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function publicUser(array $user): array
    {
        return [
            'id' => $user['id'] ?? null,
            'name' => $user['name'] ?? '',
            'email' => $user['email'] ?? '',
            'phone' => $user['phone'] ?? null,
            'email_verified_at' => $user['email_verified_at'] ?? null,
            'role' => $user['role'] ?? 'customer',
            'status' => $user['status'] ?? 'active',
            'created_at' => $user['created_at'] ?? null,
            'updated_at' => $user['updated_at'] ?? null,
        ];
    }

    public function markEmailVerified(int $userId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users
             SET email_verified_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id'
        );

        return $stmt->execute([
            ':id' => $userId,
        ]);
    }

    public function updatePhone(int $userId, ?string $phone): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users
             SET phone = :phone,
                 updated_at = NOW()
             WHERE id = :id'
        );

        return $stmt->execute([
            ':phone' => $phone,
            ':id' => $userId,
        ]);
    }

    public function updatePassword(
        int $userId,
        string $password
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE users
         SET password = :password,
             updated_at = NOW()
         WHERE id = :id'
        );

        return $stmt->execute([
            ':password' => password_hash(
                $password,
                PASSWORD_DEFAULT
            ),
            ':id' => $userId,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Address
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }


    public function getUserAddresses(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT *
             FROM addresses
             WHERE user_id = :user_id
             ORDER BY id DESC'
        );

        $stmt->execute([
            ':user_id' => $userId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function findById(
        int $id,
        int $userId
    ): ?array {

        $stmt = $this->db->prepare(
            'SELECT *
             FROM addresses
             WHERE id = :id
             AND user_id = :user_id
             LIMIT 1'
        );

        $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId,
        ]);

        $address = $stmt->fetch(PDO::FETCH_ASSOC);

        return $address ?: null;
    }


    public function create(
        int $userId,
        array $data
    ): int {

        $stmt = $this->db->prepare(
            'INSERT INTO addresses (
                user_id,
                title,
                receiver_name,
                phone,
                province,
                city,
                address,
                postal_code
            ) VALUES (
                :user_id,
                :title,
                :receiver_name,
                :phone,
                :province,
                :city,
                :address,
                :postal_code
            )'
        );


        $stmt->execute([
            ':user_id' => $userId,
            ':title' => $data['title'] ?? null,
            ':receiver_name' => $data['receiver_name'],
            ':phone' => $data['phone'],
            ':province' => $data['province'],
            ':city' => $data['city'],
            ':address' => $data['address'],
            ':postal_code' => $data['postal_code'] ?? null,
        ]);


        return (int) $this->db->lastInsertId();
    }


    public function delete(
        int $id,
        int $userId
    ): bool {

        $stmt = $this->db->prepare(
            'DELETE FROM addresses
             WHERE id = :id
             AND user_id = :user_id'
        );


        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId,
        ]);
    }
}
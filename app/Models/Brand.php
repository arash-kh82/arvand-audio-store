<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Brand extends Model
{
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
}
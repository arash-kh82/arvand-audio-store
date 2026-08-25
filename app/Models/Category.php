<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Category extends Model
{
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
}

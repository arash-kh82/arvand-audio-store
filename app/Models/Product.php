<?php
namespace App\Models;

use App\Core\Model;

class Product extends Model {
    public function getFeaturedProducts(int $limit = 6): array {
        $stmt = $this->db->prepare("
            SELECT p.*, b.name as brand_name, c.name as category_name 
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.id DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class AdminDashboard extends Model
{
    /**
     * دریافت آمار کلی داشبورد مدیریت
     */
    public function getStatistics(): array
    {
        $statistics = [];

        $statistics['users'] = (int) $this->db
            ->query(
                'SELECT COUNT(*) FROM users'
            )
            ->fetchColumn();

        $statistics['products'] = (int) $this->db
            ->query(
                'SELECT COUNT(*) FROM products'
            )
            ->fetchColumn();

        $statistics['active_products'] = (int) $this->db
            ->query(
                "SELECT COUNT(*)
                 FROM products
                 WHERE status = 'active'"
            )
            ->fetchColumn();

        $statistics['orders'] = (int) $this->db
            ->query(
                'SELECT COUNT(*) FROM orders'
            )
            ->fetchColumn();

        $statistics['pending_orders'] = (int) $this->db
            ->query(
                "SELECT COUNT(*)
                 FROM orders
                 WHERE status = 'pending'"
            )
            ->fetchColumn();

        $statistics['processing_orders'] = (int) $this->db
            ->query(
                "SELECT COUNT(*)
                 FROM orders
                 WHERE status = 'processing'"
            )
            ->fetchColumn();

        $statistics['shipped_orders'] = (int) $this->db
            ->query(
                "SELECT COUNT(*)
                 FROM orders
                 WHERE status = 'shipped'"
            )
            ->fetchColumn();

        $statistics['delivered_orders'] = (int) $this->db
            ->query(
                "SELECT COUNT(*)
                 FROM orders
                 WHERE status = 'delivered'"
            )
            ->fetchColumn();

        $statistics['successful_sales'] = (float) $this->db
            ->query(
                "SELECT COALESCE(SUM(total), 0)
                 FROM orders
                 WHERE payment_status = 'success'
                   AND status <> 'cancelled'"
            )
            ->fetchColumn();

        return $statistics;
    }

    /**
     * آخرین سفارش‌ها
     */
    public function getLatestOrders(int $limit = 5): array
    {
        $limit = max(1, min($limit, 20));

        $stmt = $this->db->prepare(
            "SELECT
                o.id,
                o.order_number,
                o.status,
                o.payment_status,
                o.total,
                o.created_at,
                u.name AS user_name,
                u.email AS user_email
             FROM orders o
             INNER JOIN users u
                ON o.user_id = u.id
             ORDER BY o.id DESC
             LIMIT :limit"
        );

        $stmt->bindValue(
            ':limit',
            $limit,
            \PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * محصولات کم‌موجودی
     */
    public function getLowStockProducts(
        int $threshold = 5,
        int $limit = 5
    ): array {
        $threshold = max(0, $threshold);
        $limit = max(1, min($limit, 20));

        $stmt = $this->db->prepare(
            "SELECT
                id,
                name,
                sku,
                stock,
                status
             FROM products
             WHERE stock <= :threshold
             ORDER BY stock ASC, id DESC
             LIMIT :limit"
        );

        $stmt->bindValue(
            ':threshold',
            $threshold,
            \PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':limit',
            $limit,
            \PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }
}
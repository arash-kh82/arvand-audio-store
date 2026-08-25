<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;
use RuntimeException;

final class Order extends Model
{
    /**
     * ثبت سفارش از روی سبد خرید کاربر
     */
    public function createFromCart(int $userId, int $addressId): int
    {
        if ($userId <= 0) {
            throw new RuntimeException(
                'کاربر نامعتبر است.'
            );
        }

        $this->db->beginTransaction();

        try {
            /*
             * دریافت آیتم‌های سبد همراه با اطلاعات محصول
             */
            $stmt = $this->db->prepare(
                'SELECT
                    ci.product_id,
                    ci.quantity,
                    p.name,
                    p.price,
                    p.discount_price,
                    p.stock,
                    p.status
                 FROM cart_items ci
                 INNER JOIN products p
                    ON p.id = ci.product_id
                 WHERE ci.user_id = :user_id
                 FOR UPDATE'
            );

            $stmt->execute([
                ':user_id' => $userId,
            ]);

            $items = $stmt->fetchAll();

            if ($items === []) {
                throw new RuntimeException(
                    'سبد خرید شما خالی است.'
                );
            }

            $subtotal = 0.0;

            /*
             * بررسی موجودی و محاسبه مبلغ
             */
            foreach ($items as $item) {
                $stock = (int) $item['stock'];
                $quantity = (int) $item['quantity'];

                if ($item['status'] !== 'active') {
                    throw new RuntimeException(
                        'محصول «'
                            . $item['name']
                            . '» دیگر فعال نیست.'
                    );
                }

                if ($quantity <= 0) {
                    throw new RuntimeException(
                        'تعداد یکی از محصولات نامعتبر است.'
                    );
                }

                if ($stock < $quantity) {
                    throw new RuntimeException(
                        'موجودی محصول «'
                            . $item['name']
                            . '» کافی نیست.'
                    );
                }

                $price = $item['discount_price'] !== null
                    ? (float) $item['discount_price']
                    : (float) $item['price'];

                $subtotal += $price * $quantity;
            }

            /*
             * فعلاً هزینه ارسال و تخفیف صفر است.
             * در مراحل بعدی می‌توانیم سیستم تخفیف
             * و هزینه ارسال را اضافه کنیم.
             */
            $discount = 0.0;
            $shippingCost = 0.0;
            $total = $subtotal - $discount + $shippingCost;

            /*
             * ساخت شماره سفارش
             */
            $orderNumber = $this->generateOrderNumber();

            /*
             * ثبت سفارش
             */
            $stmt = $this->db->prepare(
                'INSERT INTO orders (
                    user_id,
                    address_id,
                    order_number,
                    status,
                    payment_status,
                    subtotal,
                    discount,
                    shipping_cost,
                    total,
                    created_at,
                    updated_at
                ) VALUES (
                    :user_id,
                    :address_id,
                    :order_number,
                    :status,
                    :payment_status,
                    :subtotal,
                    :discount,
                    :shipping_cost,
                    :total,
                    NOW(),
                    NOW()
                )'
            );

            $stmt->execute([
                ':user_id' => $userId,
                ':address_id' => $addressId,
                ':order_number' => $orderNumber,
                ':status' => 'pending',
                ':payment_status' => 'pending',
                ':subtotal' => $subtotal,
                ':discount' => $discount,
                ':shipping_cost' => $shippingCost,
                ':total' => $total,
            ]);

            $orderId = (int) $this->db->lastInsertId();

            if ($orderId <= 0) {
                throw new RuntimeException(
                    'ثبت سفارش انجام نشد.'
                );
            }

            /*
             * انتقال آیتم‌های سبد به order_items
             * و کاهش موجودی محصولات
             */
            $insertItem = $this->db->prepare(
                'INSERT INTO order_items (
                    order_id,
                    product_id,
                    product_name,
                    price,
                    quantity,
                    total
                ) VALUES (
                    :order_id,
                    :product_id,
                    :product_name,
                    :price,
                    :quantity,
                    :total
                )'
            );

            $updateStock = $this->db->prepare(
                'UPDATE products
                 SET stock = stock - :quantity_decrease,
                     updated_at = NOW()
                 WHERE id = :product_id
                   AND stock >= :quantity_check'
            );

            foreach ($items as $item) {
                $price = $item['discount_price'] !== null
                    ? (float) $item['discount_price']
                    : (float) $item['price'];

                $quantity = (int) $item['quantity'];
                $itemTotal = $price * $quantity;

                $insertItem->execute([
                    ':order_id' => $orderId,
                    ':product_id' => (int) $item['product_id'],
                    ':product_name' => (string) $item['name'],
                    ':price' => $price,
                    ':quantity' => $quantity,
                    ':total' => $itemTotal,
                ]);

                $updateStock->execute([
                    ':quantity_decrease' => $quantity,
                    ':product_id' => (int) $item['product_id'],
                    ':quantity_check' => $quantity,
                ]);

                if ($updateStock->rowCount() !== 1) {
                    throw new RuntimeException(
                        'کاهش موجودی محصول «'
                            . $item['name']
                            . '» انجام نشد.'
                    );
                }
            }

            /*
             * خالی کردن سبد خرید
             */
            $clearCart = $this->db->prepare(
                'DELETE FROM cart_items
                 WHERE user_id = :user_id'
            );

            $clearCart->execute([
                ':user_id' => $userId,
            ]);

            $this->db->commit();

            return $orderId;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /**
     * دریافت سفارش با شناسه
     */
    public function findById(
        int $orderId,
        int $userId
    ): ?array {
        $stmt = $this->db->prepare(
            'SELECT *
             FROM orders
             WHERE id = :id
               AND user_id = :user_id
             LIMIT 1'
        );

        $stmt->execute([
            ':id' => $orderId,
            ':user_id' => $userId,
        ]);

        $order = $stmt->fetch();

        return $order !== false
            ? $order
            : null;
    }

    /**
     * دریافت آیتم‌های سفارش
     */
    public function getItems(
        int $orderId
    ): array {
        $stmt = $this->db->prepare(
            'SELECT *
             FROM order_items
             WHERE order_id = :order_id
             ORDER BY id ASC'
        );

        $stmt->execute([
            ':order_id' => $orderId,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * تولید شماره سفارش یکتا
     */
    private function generateOrderNumber(): string
    {
        do {
            $number =
                'ARV-'
                . date('YmdHis')
                . '-'
                . strtoupper(
                    bin2hex(random_bytes(3))
                );

            $stmt = $this->db->prepare(
                'SELECT id
                 FROM orders
                 WHERE order_number = :order_number
                 LIMIT 1'
            );

            $stmt->execute([
                ':order_number' => $number,
            ]);
        } while ($stmt->fetch() !== false);

        return $number;
    }

    public function getUserOrders(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                order_number,
                status,
                payment_status,
                subtotal,
                discount,
                shipping_cost,
                total,
                created_at
             FROM orders
             WHERE user_id = :user_id
             ORDER BY id DESC'
        );

        $stmt->execute([
            ':user_id' => $userId,
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * علامت‌گذاری سفارش به عنوان پرداخت‌شده
     */
    public function markAsPaid(int $orderId, int $userId): bool
    {
        if ($orderId <= 0 || $userId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE orders
             SET
                payment_status = :payment_status,
                status = :status,
                updated_at = NOW()
             WHERE id = :id
               AND user_id = :user_id
             LIMIT 1'
        );

        return $stmt->execute([
            ':payment_status' => 'success',
            ':status' => 'processing',
            ':id' => $orderId,
            ':user_id' => $userId,
        ]);
    }

    /**
     * علامت‌گذاری سفارش به عنوان پرداخت ناموفق
     */
    public function markPaymentFailed(
        int $orderId,
        int $userId
    ): bool {
        if ($orderId <= 0 || $userId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE orders
             SET
                payment_status = :payment_status,
                updated_at = NOW()
             WHERE id = :id
               AND user_id = :user_id
             LIMIT 1'
        );

        return $stmt->execute([
            ':payment_status' => 'failed',
            ':id' => $orderId,
            ':user_id' => $userId,
        ]);
    }

    /**
     * دریافت همه سفارش‌ها برای پنل مدیریت
     */
    public function getAdminOrders(): array
    {
        $stmt = $this->db->query(
            'SELECT
            o.id,
            o.order_number,
            o.status,
            o.payment_status,
            o.subtotal,
            o.discount,
            o.shipping_cost,
            o.total,
            o.created_at,
            o.updated_at,

            u.id AS user_id,
            u.name AS user_name,
            u.email AS user_email,
            u.phone AS user_phone,

            a.receiver_name,
            a.phone AS address_phone,
            a.province,
            a.city,
            a.address,
            a.postal_code

         FROM orders o

         INNER JOIN users u
            ON o.user_id = u.id

         LEFT JOIN addresses a
            ON o.address_id = a.id

         ORDER BY o.id DESC'
        );

        return $stmt->fetchAll();
    }

    /**
     * دریافت یک سفارش برای پنل مدیریت
     */
    public function findAdminById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
            o.*,

            u.name AS user_name,
            u.email AS user_email,
            u.phone AS user_phone,

            a.title AS address_title,
            a.receiver_name,
            a.phone AS address_phone,
            a.province,
            a.city,
            a.address,
            a.postal_code

         FROM orders o

         INNER JOIN users u
            ON o.user_id = u.id

         LEFT JOIN addresses a
            ON o.address_id = a.id

         WHERE o.id = :id

         LIMIT 1'
        );

        $stmt->execute([
            ':id' => $id,
        ]);

        $order = $stmt->fetch();

        return $order !== false
            ? $order
            : null;
    }

    /**
     * دریافت آیتم‌های یک سفارش برای پنل مدیریت
     */
    public function getAdminItems(int $orderId): array
    {
        if ($orderId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT
            oi.*,
            p.slug AS product_slug,
            p.image AS product_image

         FROM order_items oi

         LEFT JOIN products p
            ON oi.product_id = p.id

         WHERE oi.order_id = :order_id

         ORDER BY oi.id ASC'
        );

        $stmt->execute([
            ':order_id' => $orderId,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * تغییر وضعیت سفارش توسط مدیر
     */
    public function updateStatus(
        int $orderId,
        string $status
    ): bool {
        if ($orderId <= 0) {
            return false;
        }

        $allowedStatuses = [
            'pending',
            'paid',
            'processing',
            'shipped',
            'delivered',
            'cancelled',
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE orders
         SET
            status = :status,
            updated_at = NOW()
         WHERE id = :id
         LIMIT 1'
        );

        return $stmt->execute([
            ':status' => $status,
            ':id' => $orderId,
        ]);
    }

    /**
     * تغییر وضعیت پرداخت توسط مدیر
     */
    public function updatePaymentStatus(
        int $orderId,
        string $paymentStatus
    ): bool {
        if ($orderId <= 0) {
            return false;
        }

        $allowedStatuses = [
            'pending',
            'success',
            'failed',
        ];

        if (!in_array($paymentStatus, $allowedStatuses, true)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE orders
         SET
            payment_status = :payment_status,
            updated_at = NOW()
         WHERE id = :id
         LIMIT 1'
        );

        return $stmt->execute([
            ':payment_status' => $paymentStatus,
            ':id' => $orderId,
        ]);
    }
}

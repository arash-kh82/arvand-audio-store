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
    public function createFromCart(int $userId): int
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
                    NULL,
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
}
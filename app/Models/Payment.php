<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use RuntimeException;

final class Payment
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * ایجاد پرداخت جدید برای سفارش
     */
    public function create(
        int $orderId,
        float $amount
    ): int {
        if ($orderId <= 0) {
            throw new RuntimeException(
                'شناسه سفارش نامعتبر است.'
            );
        }

        if ($amount <= 0) {
            throw new RuntimeException(
                'مبلغ پرداخت نامعتبر است.'
            );
        }

        $stmt = $this->db->prepare(
            'INSERT INTO payments (
                order_id,
                amount,
                transaction_code,
                status,
                paid_at,
                created_at
            ) VALUES (
                :order_id,
                :amount,
                NULL,
                :status,
                NULL,
                NOW()
            )'
        );

        $stmt->execute([
            ':order_id' => $orderId,
            ':amount' => $amount,
            ':status' => 'pending',
        ]);

        $id = (int) $this->db->lastInsertId();

        if ($id <= 0) {
            throw new RuntimeException(
                'ایجاد پرداخت انجام نشد.'
            );
        }

        return $id;
    }

    /**
     * پیدا کردن پرداخت با شناسه
     */
    public function findById(
        int $id
    ): ?array {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                order_id,
                amount,
                transaction_code,
                status,
                paid_at,
                created_at
             FROM payments
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            ':id' => $id,
        ]);

        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        return $payment !== false
            ? $payment
            : null;
    }

    /**
     * پیدا کردن پرداخت سفارش
     */
    public function findByOrderId(
        int $orderId
    ): ?array {
        if ($orderId <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                order_id,
                amount,
                transaction_code,
                status,
                paid_at,
                created_at
             FROM payments
             WHERE order_id = :order_id
             ORDER BY id DESC
             LIMIT 1'
        );

        $stmt->execute([
            ':order_id' => $orderId,
        ]);

        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        return $payment !== false
            ? $payment
            : null;
    }

    /**
     * موفق کردن پرداخت
     */
    public function markSuccess(
        int $id,
        string $transactionCode
    ): bool {
        if ($id <= 0) {
            throw new RuntimeException(
                'شناسه پرداخت نامعتبر است.'
            );
        }

        $transactionCode = trim($transactionCode);

        if ($transactionCode === '') {
            throw new RuntimeException(
                'کد تراکنش نامعتبر است.'
            );
        }

        $stmt = $this->db->prepare(
            'UPDATE payments
             SET
                status = :status,
                transaction_code = :transaction_code,
                paid_at = NOW()
             WHERE id = :id
             LIMIT 1'
        );

        return $stmt->execute([
            ':status' => 'success',
            ':transaction_code' => $transactionCode,
            ':id' => $id,
        ]);
    }

    /**
     * ناموفق کردن پرداخت
     */
    public function markFailed(
        int $id
    ): bool {
        if ($id <= 0) {
            throw new RuntimeException(
                'شناسه پرداخت نامعتبر است.'
            );
        }

        $stmt = $this->db->prepare(
            'UPDATE payments
             SET
                status = :status,
                transaction_code = NULL,
                paid_at = NULL
             WHERE id = :id
             LIMIT 1'
        );

        return $stmt->execute([
            ':status' => 'failed',
            ':id' => $id,
        ]);
    }
}
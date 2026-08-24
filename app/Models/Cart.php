<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;
use RuntimeException;

final class Cart extends Model
{
    /**
     * دریافت آیتم‌های سبد خرید یک کاربر
     */
    public function getItems(int $userId): array
    {
        $this->validateUserId($userId);

        $stmt = $this->db->prepare("
            SELECT
                ci.id AS cart_item_id,
                ci.user_id,
                ci.product_id,
                ci.quantity,
                ci.created_at,
                ci.updated_at,

                p.name,
                p.slug,
                p.sku,
                p.description,
                p.price,
                p.discount_price,
                p.stock,
                p.image,
                p.status,

                b.name AS brand_name,
                b.slug AS brand_slug,

                c.name AS category_name,
                c.slug AS category_slug

            FROM cart_items ci

            INNER JOIN products p
                ON ci.product_id = p.id

            LEFT JOIN brands b
                ON p.brand_id = b.id

            INNER JOIN categories c
                ON p.category_id = c.id

            WHERE ci.user_id = :user_id

            ORDER BY ci.id DESC
        ");

        $stmt->execute([
            ':user_id' => $userId,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * دریافت یک آیتم مشخص از سبد
     */
    public function findItem(
        int $userId,
        int $productId
    ): ?array {
        $this->validateUserId($userId);
        $this->validateProductId($productId);

        $stmt = $this->db->prepare("
            SELECT
                ci.id AS cart_item_id,
                ci.user_id,
                ci.product_id,
                ci.quantity,
                ci.created_at,
                ci.updated_at,

                p.name,
                p.slug,
                p.sku,
                p.price,
                p.discount_price,
                p.stock,
                p.image,
                p.status

            FROM cart_items ci

            INNER JOIN products p
                ON ci.product_id = p.id

            WHERE ci.user_id = :user_id
              AND ci.product_id = :product_id

            LIMIT 1
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId,
        ]);

        $item = $stmt->fetch();

        return $item !== false ? $item : null;
    }

    /**
     * افزودن محصول به سبد خرید
     *
     * اگر محصول از قبل در سبد باشد،
     * تعداد آن افزایش پیدا می‌کند.
     */
    public function add(
        int $userId,
        int $productId,
        int $quantity = 1
    ): bool {
        $this->validateUserId($userId);
        $this->validateProductId($productId);

        if ($quantity <= 0) {
            throw new RuntimeException(
                'تعداد محصول باید بیشتر از صفر باشد.'
            );
        }

        $product = $this->getAvailableProduct(
            $productId
        );

        if ($product === null) {
            throw new RuntimeException(
                'محصول مورد نظر موجود یا فعال نیست.'
            );
        }

        $existing = $this->findItem(
            $userId,
            $productId
        );

        $currentQuantity = $existing !== null
            ? (int) $existing['quantity']
            : 0;

        $newQuantity = $currentQuantity + $quantity;

        if (
            $newQuantity > (int) $product['stock']
        ) {
            throw new RuntimeException(
                'تعداد درخواستی بیشتر از موجودی محصول است.'
            );
        }

        if ($existing !== null) {
            $stmt = $this->db->prepare("
                UPDATE cart_items
                SET
                    quantity = :quantity,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
                  AND user_id = :user_id
            ");

            return $stmt->execute([
                ':quantity' => $newQuantity,
                ':id' => $existing['cart_item_id'],
                ':user_id' => $userId,
            ]);
        }

        $stmt = $this->db->prepare("
            INSERT INTO cart_items (
                user_id,
                product_id,
                quantity
            )
            VALUES (
                :user_id,
                :product_id,
                :quantity
            )
        ");

        return $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId,
            ':quantity' => $quantity,
        ]);
    }

    /**
     * تغییر مستقیم تعداد یک محصول
     */
    public function updateQuantity(
        int $userId,
        int $productId,
        int $quantity
    ): bool {
        $this->validateUserId($userId);
        $this->validateProductId($productId);

        if ($quantity <= 0) {
            return $this->remove(
                $userId,
                $productId
            );
        }

        $product = $this->getAvailableProduct(
            $productId
        );

        if ($product === null) {
            throw new RuntimeException(
                'محصول مورد نظر موجود یا فعال نیست.'
            );
        }

        if (
            $quantity > (int) $product['stock']
        ) {
            throw new RuntimeException(
                'تعداد انتخاب‌شده بیشتر از موجودی محصول است.'
            );
        }

        $stmt = $this->db->prepare("
            UPDATE cart_items
            SET
                quantity = :quantity,
                updated_at = CURRENT_TIMESTAMP
            WHERE user_id = :user_id
              AND product_id = :product_id
        ");

        $stmt->execute([
            ':quantity' => $quantity,
            ':user_id' => $userId,
            ':product_id' => $productId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * حذف محصول از سبد
     */
    public function remove(
        int $userId,
        int $productId
    ): bool {
        $this->validateUserId($userId);
        $this->validateProductId($productId);

        $stmt = $this->db->prepare("
            DELETE FROM cart_items
            WHERE user_id = :user_id
              AND product_id = :product_id
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * حذف یک آیتم بر اساس ID سبد
     */
    public function removeItem(
        int $userId,
        int $cartItemId
    ): bool {
        $this->validateUserId($userId);

        if ($cartItemId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare("
            DELETE FROM cart_items
            WHERE id = :id
              AND user_id = :user_id
        ");

        $stmt->execute([
            ':id' => $cartItemId,
            ':user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * خالی کردن کامل سبد
     */
    public function clear(int $userId): bool
    {
        $this->validateUserId($userId);

        $stmt = $this->db->prepare("
            DELETE FROM cart_items
            WHERE user_id = :user_id
        ");

        $stmt->execute([
            ':user_id' => $userId,
        ]);

        return true;
    }

    /**
     * تعداد کل کالاهای سبد
     *
     * مثال:
     * محصول اول = 2
     * محصول دوم = 3
     *
     * نتیجه = 5
     */
    public function getTotalQuantity(
        int $userId
    ): int {
        $this->validateUserId($userId);

        $stmt = $this->db->prepare("
            SELECT COALESCE(
                SUM(quantity),
                0
            )
            FROM cart_items
            WHERE user_id = :user_id
        ");

        $stmt->execute([
            ':user_id' => $userId,
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * تعداد آیتم‌های متفاوت سبد
     */
    public function getItemCount(
        int $userId
    ): int {
        $this->validateUserId($userId);

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM cart_items
            WHERE user_id = :user_id
        ");

        $stmt->execute([
            ':user_id' => $userId,
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * محاسبه مبلغ کل سبد
     *
     * قیمت تخفیف‌خورده در صورت وجود
     * اولویت دارد.
     */
    public function getTotal(int $userId): float
    {
        $this->validateUserId($userId);

        $stmt = $this->db->prepare("
            SELECT
                COALESCE(
                    SUM(
                        ci.quantity *
                        CASE
                            WHEN p.discount_price IS NOT NULL
                                 AND p.discount_price > 0
                            THEN p.discount_price
                            ELSE p.price
                        END
                    ),
                    0
                )
            FROM cart_items ci

            INNER JOIN products p
                ON ci.product_id = p.id

            WHERE ci.user_id = :user_id
        ");

        $stmt->execute([
            ':user_id' => $userId,
        ]);

        return (float) $stmt->fetchColumn();
    }

    /**
     * دریافت محصول فعال و موجود
     */
    private function getAvailableProduct(
        int $productId
    ): ?array {
        $stmt = $this->db->prepare("
            SELECT
                id,
                price,
                discount_price,
                stock,
                status
            FROM products
            WHERE id = :id
              AND status = 'active'
            LIMIT 1
        ");

        $stmt->execute([
            ':id' => $productId,
        ]);

        $product = $stmt->fetch();

        if ($product === false) {
            return null;
        }

        if ((int) $product['stock'] <= 0) {
            return null;
        }

        return $product;
    }

    /**
     * اعتبارسنجی User ID
     */
    private function validateUserId(
        int $userId
    ): void {
        if ($userId <= 0) {
            throw new RuntimeException(
                'شناسه کاربر نامعتبر است.'
            );
        }
    }

    /**
     * اعتبارسنجی Product ID
     */
    private function validateProductId(
        int $productId
    ): void {
        if ($productId <= 0) {
            throw new RuntimeException(
                'شناسه محصول نامعتبر است.'
            );
        }
    }
}
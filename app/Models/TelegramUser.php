<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class TelegramUser extends Model
{
    /**
     * Find Telegram user by Telegram ID.
     */
    public function findByTelegramId(
        int $telegramId
    ): ?array {
        $stmt = $this->db->prepare(
            'SELECT *
             FROM telegram_users
             WHERE telegram_id = :telegram_id
             LIMIT 1'
        );

        $stmt->execute([
            ':telegram_id' => $telegramId,
        ]);

        $user = $stmt->fetch();

        return $user !== false
            ? $user
            : null;
    }

    /**
     * Create a Telegram user.
     */
    public function create(
        int $telegramId,
        ?string $username,
        ?string $firstName,
        ?string $lastName
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO telegram_users
                (
                    telegram_id,
                    username,
                    first_name,
                    last_name,
                    is_active
                )
             VALUES
                (
                    :telegram_id,
                    :username,
                    :first_name,
                    :last_name,
                    1
                )'
        );

        $stmt->execute([
            ':telegram_id' => $telegramId,
            ':username' => $username,
            ':first_name' => $firstName,
            ':last_name' => $lastName,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update Telegram profile information.
     */
    public function updateProfile(
        int $telegramId,
        ?string $username,
        ?string $firstName,
        ?string $lastName
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE telegram_users
             SET
                username = :username,
                first_name = :first_name,
                last_name = :last_name,
                updated_at = CURRENT_TIMESTAMP
             WHERE telegram_id = :telegram_id'
        );

        return $stmt->execute([
            ':telegram_id' => $telegramId,
            ':username' => $username,
            ':first_name' => $firstName,
            ':last_name' => $lastName,
        ]);
    }

    /**
     * Link Telegram account to website user.
     */
    public function linkToUser(
        int $telegramId,
        int $userId
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE telegram_users
             SET
                user_id = :user_id,
                updated_at = CURRENT_TIMESTAMP
             WHERE telegram_id = :telegram_id'
        );

        return $stmt->execute([
            ':telegram_id' => $telegramId,
            ':user_id' => $userId,
        ]);
    }

    /**
     * Get linked website user ID.
     */
    public function getLinkedUserId(
        int $telegramId
    ): ?int {
        $stmt = $this->db->prepare(
            'SELECT user_id
             FROM telegram_users
             WHERE telegram_id = :telegram_id
             LIMIT 1'
        );

        $stmt->execute([
            ':telegram_id' => $telegramId,
        ]);

        $userId = $stmt->fetchColumn();

        if ($userId === false || $userId === null) {
            return null;
        }

        return (int) $userId;
    }

    /**
     * Activate Telegram user.
     */
    public function activate(
        int $telegramId
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE telegram_users
             SET
                is_active = 1,
                updated_at = CURRENT_TIMESTAMP
             WHERE telegram_id = :telegram_id'
        );

        return $stmt->execute([
            ':telegram_id' => $telegramId,
        ]);
    }

    /**
     * Deactivate Telegram user.
     */
    public function deactivate(
        int $telegramId
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE telegram_users
             SET
                is_active = 0,
                updated_at = CURRENT_TIMESTAMP
             WHERE telegram_id = :telegram_id'
        );

        return $stmt->execute([
            ':telegram_id' => $telegramId,
        ]);
    }

    /**
     * Unlink Telegram account from website user.
     */
    public function unlinkFromUser(
        int $telegramId
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE telegram_users
         SET
            user_id = NULL,
            updated_at = CURRENT_TIMESTAMP
         WHERE telegram_id = :telegram_id'
        );

        return $stmt->execute([
            ':telegram_id' => $telegramId,
        ]);
    }

    /**
     * Create or update Telegram user from Telegram data.
     */
    public function sync(
        int $telegramId,
        ?string $username,
        ?string $firstName,
        ?string $lastName
    ): array {
        $existing =
            $this->findByTelegramId(
                $telegramId
            );

        if ($existing === null) {

            $id = $this->create(
                $telegramId,
                $username,
                $firstName,
                $lastName
            );

            return $this->findByTelegramId(
                $telegramId
            ) ?? [
                'id' => $id,
                'telegram_id' => $telegramId,
                'user_id' => null,
            ];
        }

        $this->updateProfile(
            $telegramId,
            $username,
            $firstName,
            $lastName
        );

        return $this->findByTelegramId(
            $telegramId
        ) ?? $existing;
    }
}

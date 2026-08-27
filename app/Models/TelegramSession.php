<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class TelegramSession extends Model
{
    /**
     * Find session by Telegram ID.
     */
    public function findByTelegramId(
        int $telegramId
    ): ?array {
        $stmt = $this->db->prepare(
            'SELECT *
             FROM telegram_sessions
             WHERE telegram_id = :telegram_id
             LIMIT 1'
        );

        $stmt->execute([
            ':telegram_id' => $telegramId,
        ]);

        $session = $stmt->fetch();

        return $session !== false
            ? $session
            : null;
    }

    /**
     * Create a new Telegram session.
     */
    public function create(
        int $telegramId,
        string $state = 'idle',
        ?array $data = null
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO telegram_sessions
                (
                    telegram_id,
                    state,
                    data
                )
             VALUES
                (
                    :telegram_id,
                    :state,
                    :data
                )'
        );

        $stmt->execute([
            ':telegram_id' => $telegramId,
            ':state' => $state,
            ':data' => $data !== null
                ? json_encode(
                    $data,
                    JSON_UNESCAPED_UNICODE
                )
                : null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Create or update a Telegram session.
     */
    public function setState(
        int $telegramId,
        string $state,
        ?array $data = null
    ): array {
        $existing = $this->findByTelegramId(
            $telegramId
        );

        $jsonData = $data !== null
            ? json_encode(
                $data,
                JSON_UNESCAPED_UNICODE
            )
            : null;

        if ($existing === null) {

            $this->create(
                $telegramId,
                $state,
                $data
            );

        } else {

            $stmt = $this->db->prepare(
                'UPDATE telegram_sessions
                 SET
                    state = :state,
                    data = :data,
                    updated_at = CURRENT_TIMESTAMP
                 WHERE telegram_id = :telegram_id'
            );

            $stmt->execute([
                ':telegram_id' => $telegramId,
                ':state' => $state,
                ':data' => $jsonData,
            ]);
        }

        return $this->findByTelegramId(
            $telegramId
        ) ?? [
            'telegram_id' => $telegramId,
            'state' => $state,
            'data' => $jsonData,
        ];
    }

    /**
     * Get current session state.
     */
    public function getState(
        int $telegramId
    ): string {
        $session = $this->findByTelegramId(
            $telegramId
        );

        if ($session === null) {
            return 'idle';
        }

        return (string) $session['state'];
    }

    /**
     * Get session data.
     */
    public function getData(
        int $telegramId
    ): ?array {
        $session = $this->findByTelegramId(
            $telegramId
        );

        if (
            $session === null
            || $session['data'] === null
        ) {
            return null;
        }

        $data = json_decode(
            (string) $session['data'],
            true
        );

        return is_array($data)
            ? $data
            : null;
    }

    /**
     * Clear the current session.
     */
    public function clear(
        int $telegramId
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE telegram_sessions
             SET
                state = "idle",
                data = NULL,
                updated_at = CURRENT_TIMESTAMP
             WHERE telegram_id = :telegram_id'
        );

        return $stmt->execute([
            ':telegram_id' => $telegramId,
        ]);
    }
}
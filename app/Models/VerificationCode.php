<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class VerificationCode extends Model
{
    public function create(
        int $userId,
        string $purpose,
        string $code,
        int $minutes = 10
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO user_verification_codes
                (
                    user_id,
                    purpose,
                    code_hash,
                    expires_at,
                    attempts,
                    used_at,
                    created_at,
                    updated_at
                )
             VALUES
                (
                    :user_id,
                    :purpose,
                    :code_hash,
                    DATE_ADD(NOW(), INTERVAL :minutes MINUTE),
                    0,
                    NULL,
                    NOW(),
                    NOW()
                )'
        );

        $stmt->execute([
            ':user_id' => $userId,
            ':purpose' => $purpose,
            ':code_hash' => password_hash(
                $code,
                PASSWORD_DEFAULT
            ),
            ':minutes' => $minutes,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findLatestActive(
        int $userId,
        string $purpose
    ): ?array {
        $stmt = $this->db->prepare(
            'SELECT *
             FROM user_verification_codes
             WHERE user_id = :user_id
               AND purpose = :purpose
               AND used_at IS NULL
               AND expires_at > NOW()
             ORDER BY id DESC
             LIMIT 1'
        );

        $stmt->execute([
            ':user_id' => $userId,
            ':purpose' => $purpose,
        ]);

        $code = $stmt->fetch();

        return $code !== false ? $code : null;
    }

    public function verify(
        int $id,
        string $code
    ): bool {
        $stmt = $this->db->prepare(
            'SELECT *
             FROM user_verification_codes
             WHERE id = :id
               AND used_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([
            ':id' => $id,
        ]);

        $record = $stmt->fetch();

        if ($record === false) {
            return false;
        }

        if (
            strtotime(
                (string) $record['expires_at']
            ) < time()
        ) {
            return false;
        }

        if (
            (int) $record['attempts'] >= 5
        ) {
            return false;
        }

        if (
            !password_verify(
                $code,
                (string) $record['code_hash']
            )
        ) {
            $this->incrementAttempts(
                (int) $record['id']
            );

            return false;
        }

        return $this->markUsed(
            (int) $record['id']
        );
    }

    public function incrementAttempts(
        int $id
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE user_verification_codes
             SET attempts = attempts + 1,
                 updated_at = NOW()
             WHERE id = :id
               AND used_at IS NULL'
        );

        return $stmt->execute([
            ':id' => $id,
        ]);
    }

    public function markUsed(
        int $id
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE user_verification_codes
             SET used_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id
               AND used_at IS NULL'
        );

        return $stmt->execute([
            ':id' => $id,
        ]);
    }

    public function invalidatePrevious(
        int $userId,
        string $purpose
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE user_verification_codes
             SET used_at = NOW(),
                 updated_at = NOW()
             WHERE user_id = :user_id
               AND purpose = :purpose
               AND used_at IS NULL'
        );

        return $stmt->execute([
            ':user_id' => $userId,
            ':purpose' => $purpose,
        ]);
    }
}
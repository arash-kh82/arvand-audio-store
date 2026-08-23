<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(
        string $viewPath,
        array $data = []
    ): void {
        $viewPath = trim($viewPath, '/');

        if ($viewPath === '') {
            throw new \InvalidArgumentException(
                'مسیر View نمی‌تواند خالی باشد.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | جلوگیری از Path Traversal
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($viewPath, '..')
            || str_contains($viewPath, '\\')
        ) {
            throw new \InvalidArgumentException(
                'مسیر View نامعتبر است.'
            );
        }

        extract(
            $data,
            EXTR_SKIP
        );

        $file = __DIR__
            . '/../Views/'
            . $viewPath
            . '.php';

        if (!is_file($file)) {
            http_response_code(500);

            echo 'View پیدا نشد: '
                . htmlspecialchars(
                    $viewPath,
                    ENT_QUOTES,
                    'UTF-8'
                );

            return;
        }

        require $file;
    }
}
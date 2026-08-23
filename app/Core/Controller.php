<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $viewPath, array $data = []): void
    {
        View::render($viewPath, $data);
    }

    protected function redirect(string $path, int $status = 302): never
    {
        header('Location: ' . $this->url($path), true, $status);
        exit;
    }

    protected function url(string $path): string
    {
        $baseUrl = '';

        if (function_exists('app_config')) {
            $baseUrl = (string) app_config('base_url', '');
        }

        $baseUrl = rtrim($baseUrl, '/');
        $path = '/' . ltrim($path, '/');

        if ($baseUrl === '') {
            return $path;
        }

        return $baseUrl . $path;
    }
}
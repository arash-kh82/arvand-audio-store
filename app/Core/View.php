<?php
namespace App\Core;

class View {
    public static function render(string $viewPath, array $data = []): void {
        extract($data);
        $file = __DIR__ . '/../Views/' . $viewPath . '.php';
        if (file_exists($file)) {
            require_once $file;
        } else {
            die("صفحه مورد نظر یافت نشد: {$viewPath}");
        }
    }
}
<?php
namespace App\Core;

abstract class Controller {
    protected function view(string $viewPath, array $data = []): void {
        View::render($viewPath, $data);
    }
}
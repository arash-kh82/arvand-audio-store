<?php
namespace App\Core;

class Router {
    private array $routes = [];

    public function get(string $uri, string $action): void {
        $this->routes['GET'][$uri] = $action;
    }

    public function post(string $uri, string $action): void {
        $this->routes['POST'][$uri] = $action;
    }

    public function dispatch(): void {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // اصلاح مسیر در صورتی که پروژه در ساب‌فولدر Laragon باشد
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/' && str_starts_with($uri, $scriptName)) {
            $uri = substr($uri, strlen($scriptName));
        }
        $uri = '/' . trim($uri, '/');

        $method = $_SERVER['REQUEST_METHOD'];

        if (isset($this->routes[$method][$uri])) {
            $action = $this->routes[$method][$uri];
            [$controllerClass, $methodName] = explode('@', $action);
            
            $controllerClass = "App\\Controllers\\" . $controllerClass;
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if (method_exists($controller, $methodName)) {
                    $controller->$methodName();
                    return;
                }
            }
        }

        http_response_code(404);
        echo "<h2 style='text-align:center;margin-top:50px;'>404 - صفحه مورد نظر در آروند پیدا نشد!</h2>";
    }
}
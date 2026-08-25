<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Router
{
    private array $routes = [];

    public function get(
        string $path,
        callable|array|string $handler
    ): void {
        $this->add('GET', $path, $handler);
    }

    public function post(
        string $path,
        callable|array|string $handler
    ): void {
        $this->add('POST', $path, $handler);
    }

    public function add(
        string $method,
        string $path,
        callable|array|string $handler
    ): void {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $this->normalizePath($path),
            'handler' => $handler,
        ];
    }

    public function dispatch(): void
    {
        $method = strtoupper(
            $_SERVER['REQUEST_METHOD'] ?? 'GET'
        );

        $uri = parse_url(
            $_SERVER['REQUEST_URI'] ?? '/',
            PHP_URL_PATH
        );

        if (!is_string($uri) || $uri === '') {
            $uri = '/';
        }

        /*
        |--------------------------------------------------------------------------
        | Remove Application Base URL
        |--------------------------------------------------------------------------
        */

        $baseUrl = '';

        if (function_exists('app_config')) {
            $baseUrl = (string) app_config(
                'base_url',
                ''
            );
        }

        $baseUrl = $this->normalizePath($baseUrl);

        if (
            $baseUrl !== '/'
            && $baseUrl !== ''
            && (
                $uri === $baseUrl
                || str_starts_with(
                    $uri,
                    $baseUrl . '/'
                )
            )
        ) {
            $uri = substr(
                $uri,
                strlen($baseUrl)
            );
        }

        $path = $this->normalizePath($uri);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->compilePattern(
                $route['path']
            );

            if (
                !preg_match(
                    $pattern,
                    $path,
                    $matches
                )
            ) {
                continue;
            }

            $parameters = [];

            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $parameters[$key] = urldecode($value);
                }
            }

            $this->execute(
                $route['handler'],
                $parameters
            );

            return;
        }

        $this->notFound();
    }

    private function compilePattern(
        string $path
    ): string {
        if ($path === '/') {
            return '#^/$#';
        }

        $segments = explode(
            '/',
            trim($path, '/')
        );

        $pattern = '';

        foreach ($segments as $segment) {
            if (
                preg_match(
                    '/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/',
                    $segment,
                    $match
                )
            ) {
                $pattern .= '/(?P<' . $match[1] . '>[^/]+)';
            } else {
                $pattern .= '/' . preg_quote(
                    $segment,
                    '#'
                );
            }
        }

        return '#^' . $pattern . '/?$#';
    }

    private function execute(
        callable|array|string $handler,
        array $parameters
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Closure / Callable
        |--------------------------------------------------------------------------
        */

        if (is_callable($handler)) {
            $handler(...array_values($parameters));
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | [Controller::class, 'method']
        |--------------------------------------------------------------------------
        */

        if (is_array($handler)) {
            if (count($handler) !== 2) {
                throw new RuntimeException(
                    'Route handler نامعتبر است.'
                );
            }

            [$controllerClass, $method] = $handler;

            $this->executeController(
                $controllerClass,
                $method,
                $parameters
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 'Controller@method'
        |--------------------------------------------------------------------------
        */

        if (is_string($handler)) {
            if (!str_contains($handler, '@')) {
                throw new RuntimeException(
                    'Route handler نامعتبر است: ' . $handler
                );
            }

            [$controllerClass, $method] = explode(
                '@',
                $handler,
                2
            );

            $controllerClass = $this->resolveControllerClass(
                $controllerClass
            );

            $this->executeController(
                $controllerClass,
                $method,
                $parameters
            );

            return;
        }

        throw new RuntimeException(
            'Route handler نامعتبر است.'
        );
    }

    private function executeController(
        string $controllerClass,
        string $method,
        array $parameters
    ): void {
        if (!class_exists($controllerClass)) {
            throw new RuntimeException(
                "Controller پیدا نشد: {$controllerClass}"
            );
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            throw new RuntimeException(
                "متد Controller پیدا نشد: {$controllerClass}@{$method}"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Convert route parameters according to Controller type
        |--------------------------------------------------------------------------
        */

        $routeParameters = array_values($parameters);

        $reflection = new \ReflectionMethod(
            $controller,
            $method
        );

        $methodParameters = $reflection->getParameters();

        foreach ($methodParameters as $index => $parameter) {
            if (!array_key_exists($index, $routeParameters)) {
                continue;
            }

            $type = $parameter->getType();

            if (!$type instanceof \ReflectionNamedType) {
                continue;
            }

            if (!$type->isBuiltin()) {
                continue;
            }

            switch ($type->getName()) {
                case 'int':
                    $routeParameters[$index] =
                        (int) $routeParameters[$index];
                    break;

                case 'float':
                    $routeParameters[$index] =
                        (float) $routeParameters[$index];
                    break;

                case 'bool':
                    $routeParameters[$index] =
                        filter_var(
                            $routeParameters[$index],
                            FILTER_VALIDATE_BOOLEAN
                        );
                    break;

                case 'string':
                    $routeParameters[$index] =
                        (string) $routeParameters[$index];
                    break;
            }
        }

        $controller->{$method}(
            ...$routeParameters
        );
    }

    private function resolveControllerClass(
        string $controller
    ): string {
        if (str_contains($controller, '\\')) {
            return $controller;
        }

        return 'App\\Controllers\\' . $controller;
    }

    private function normalizePath(
        string $path
    ): string {
        $path = trim($path);

        if ($path === '') {
            return '/';
        }

        $path = '/' . ltrim($path, '/');

        $path = preg_replace(
            '#/+#',
            '/',
            $path
        ) ?? '/';

        if (
            $path !== '/'
            && str_ends_with($path, '/')
        ) {
            $path = rtrim($path, '/');
        }

        return $path;
    }

    private function notFound(): never
    {
        http_response_code(404);

        echo '404 - صفحه مورد نظر پیدا نشد.';

        exit;
    }
}
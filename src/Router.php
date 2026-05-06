<?php

declare(strict_types=1);

namespace Swarm;

/**
 * Router — Simple PHP request router.
 *
 * Supports GET, POST, PUT, PATCH, DELETE methods.
 * Route parameters via {name} placeholders.
 * Middleware groups via route registration.
 * Route groups with prefix and shared middleware.
 */
class Router
{
    /** @var array<string, array<array{pattern: string, handler: array, middleware: string[]}>> */
    private array $routes = [];

    /** Current group prefix */
    private string $groupPrefix = '';

    /** Current group middleware */
    private array $groupMiddleware = [];

    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function patch(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('PATCH', $path, $handler, $middleware);
    }

    public function delete(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    /**
     * Register a group of routes with a shared prefix and middleware.
     */
    public function group(array $options, callable $callback): void
    {
        $previousPrefix     = $this->groupPrefix;
        $previousMiddleware  = $this->groupMiddleware;

        $this->groupPrefix     .= $options['prefix'] ?? '';
        $this->groupMiddleware  = array_merge(
            $this->groupMiddleware,
            $options['middleware'] ?? []
        );

        $callback($this);

        $this->groupPrefix     = $previousPrefix;
        $this->groupMiddleware  = $previousMiddleware;
    }

    /**
     * Dispatch the current request to the matching route.
     */
    public function dispatch(string $method, string $uri): void
    {
        // Strip query string
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rtrim($path, '/') ?: '/';

        // Support method override via _method for forms (PUT, PATCH, DELETE)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $route) {
            $params = $this->match($route['pattern'], $path);
            if ($params !== false) {
                // Run middleware
                foreach ($route['middleware'] as $mw) {
                    $this->runMiddleware($mw);
                }

                // Instantiate controller and call method
                [$class, $action] = $route['handler'];
                $controller = new $class();
                $controller->$action(...array_values($params));
                return;
            }
        }

        // No route matched
        http_response_code(404);
        echo $this->render404();
    }

    private function addRoute(string $method, string $path, array $handler, array $middleware): void
    {
        $fullPath = $this->groupPrefix . $path;
        $fullPath = rtrim($fullPath, '/') ?: '/';

        $this->routes[$method][] = [
            'pattern'    => $fullPath,
            'handler'    => $handler,
            'middleware'  => array_merge($this->groupMiddleware, $middleware),
        ];
    }

    /**
     * Match a route pattern against a path.
     * Returns an array of named parameters on match, false on no match.
     *
     * @return array<string, string>|false
     */
    private function match(string $pattern, string $path): array|false
    {
        // Convert {param} to named regex groups
        $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $path, $matches)) {
            // Filter to named groups only
            return array_filter($matches, fn($key) => is_string($key), ARRAY_FILTER_USE_KEY);
        }

        return false;
    }

    /**
     * Run a middleware by name.
     */
    private function runMiddleware(string $name): void
    {
        $middlewareMap = [
            'control'        => Middleware\ControlApp::class,
            'auth'            => Middleware\Auth::class,
            'throttle:signup' => [Middleware\Throttle::class, 'signup'],
            'throttle:login'  => [Middleware\Throttle::class, 'login'],
        ];

        if (!isset($middlewareMap[$name])) {
            throw new \RuntimeException("Unknown middleware: {$name}");
        }

        $config = $middlewareMap[$name];

        if (is_array($config)) {
            [$class, $type] = $config;
            (new $class())->handle($type);
        } else {
            (new $config())->handle();
        }
    }

    private function render404(): string
    {
        // Use the proper public layout if views exist
        $viewPath = SWARM_ROOT . '/views/404.php';
        $layoutPath = SWARM_ROOT . '/views/layouts/public.php';

        if (file_exists($viewPath) && file_exists($layoutPath)) {
            ob_start();
            require $viewPath;
            $content = ob_get_clean();
            ob_start();
            require $layoutPath;
            return ob_get_clean();
        }

        return '<!DOCTYPE html><html><head><title>404 — Ricsian</title></head>'
             . '<body style="font-family:Inter,system-ui,sans-serif;display:flex;align-items:center;'
             . 'justify-content:center;min-height:100vh;margin:0;background:#09090B;color:#FAFAFA;">'
             . '<div style="text-align:center"><h1 style="font-size:48px;font-weight:700;margin:0;">404</h1>'
             . '<p style="color:#71717A;margin-top:8px;">Page not found.</p></div></body></html>';
    }
}

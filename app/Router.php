<?php

namespace App;
class Router
{
    private $routes = [];

    public function get($path, $callback)
    {
        $this->add('GET', $path, $callback);
    }

    public function post($path, $callback)
    {
        $this->add('POST', $path, $callback);
    }

    public function put($path, $callback)
    {
        $this->add('PUT', $path, $callback);
    }

    public function delete($path, $callback)
    {
        $this->add('DELETE', $path, $callback);
    }

    public function add($method, $path, $callback)
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'callback' => $callback
        ];
    }

    public function dispatch()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if ($requestUri !== '/' && substr($requestUri, -1) === '/') {
            $requestUri = rtrim($requestUri, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            $pattern = $this->convertPatternToRegex($route['path']);

            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches);

                $response = $this->executeCallback($route['callback'], $matches);
                $this->handleResponse($response);
                return;
            }
        }

        $this->notFound();
    }

    private function executeCallback($callback, $params)
    {
        if (is_array($callback)) {
            $controller = new $callback[0];
            return $controller->{$callback[1]}(...$params);
        }

        if (is_callable($callback)) {
            return $callback(...$params);
        }

        throw new \Exception('Invalid callback provided');
    }

    private function handleResponse($response)
    {
        if (is_array($response) || is_object($response)) {
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            echo $response;
        }
    }

    private function convertPatternToRegex($pattern)
    {
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = preg_replace('/:([a-zA-Z0-9_]+)/', '([a-zA-Z0-9_-]+)', $pattern);
        return '/^' . $pattern . '$/';
    }

    private function notFound()
    {
        http_response_code(404);

        if (file_exists(__DIR__ . '/../views/404.php')) {
            require __DIR__ . '/../views/404.php';
        } else {
            echo '<h1>404 - Page Not Found</h1>';
            echo '<p>The page you are looking for does not exist.</p>';
            echo '<a href="/">Go Home</a>';
        }
    }
}
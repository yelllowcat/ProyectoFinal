<?php

class Router {
    private $routes = [];
    
    /**
     * Add a route to the router
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $path URL path pattern
     * @param callable $callback Function to execute
     */
    public function add($method, $path, $callback) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'callback' => $callback
        ];
    }
    
   
    public function dispatch() {
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
                
                call_user_func_array($route['callback'], $matches);
                return;
            }
        }
        
        $this->notFound();
    }
    
    /**
     * Convert route pattern to regex
     * @param string $pattern Route pattern like /profile/:id
     * @return string Regex pattern
     */
    private function convertPatternToRegex($pattern) {
        $pattern = str_replace('/', '\/', $pattern);
        
        $pattern = preg_replace('/:([a-zA-Z0-9_]+)/', '([a-zA-Z0-9_-]+)', $pattern);
        
        return '/^' . $pattern . '$/';
    }
    
  
    private function notFound() {
        http_response_code(404);
        if (file_exists('views/404.php')) {
            require 'views/404.php';
        } else {
            echo '<h1>404 - Page Not Found</h1>';
            echo '<p>The page you are looking for does not exist.</p>';
            echo '<a href="/">Go Home</a>';
        }
    }
}
<?php

$isDev = (getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'development')) === 'development';

if ($isDev) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
}

require_once __DIR__ . '/vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

require_once __DIR__ . '/config/database.php';

$router = new App\Router();

require_once __DIR__ . '/routes/web.php';

try {
    $router->dispatch();
} catch (Throwable $e) {
    error_log("Router Error: " . $e->getMessage());
    if ($isDev) {
        die("Router Error: " . htmlspecialchars($e->getMessage()));
    }
    http_response_code(500);
    echo "<h1>500 - Error del Servidor</h1><p>Ocurri un error inesperado al procesar la solicitud.</p>";
}
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

require_once __DIR__ . '/vendor/autoload.php';
session_start();

require_once __DIR__ . '/config/database.php';

$router = new App\Router();

require_once __DIR__ . '/routes/web.php';

try {
    $router->dispatch();
} catch (Exception $e) {
    error_log("Router Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    
    echo "<pre>";
    echo "Router Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
    echo "</pre>";
    die();
}
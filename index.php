<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('', 1);
require_once __DIR__ . '/vendor/autoload.php';
session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/auth.php';

$router = new App\Router();

require_once __DIR__ . '/routes/web.php';

try {
    $router->dispatch();
} catch (Exception $e) {
    die("Router Error: " . $e->getMessage());
}

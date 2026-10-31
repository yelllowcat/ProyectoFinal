<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/config/tcpdf_config.php';

define('PHPUNIT_RUNNING', true);

if (!isset($pdo) || $pdo === null) {
    $pdo = new PDO('sqlite::memory:', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

if (!defined('DB_HOST')) {
    define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', $_ENV['DB_NAME'] ?? 'unired_DB');
}
if (!defined('DB_USER')) {
    define('DB_USER', $_ENV['DB_USER'] ?? 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', $_ENV['DB_PASS'] ?? '');
}

if (!function_exists('getDB')) {
    /**
     * Stub de conexión PDO para análisis estático y pruebas unitarias aisladas.
     *
     * @return \PDO|null
     */
    function getDB()
    {
        global $pdo;
        return $pdo ?? null;
    }
}

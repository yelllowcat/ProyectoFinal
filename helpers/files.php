<?php

/**
 * Genera la ruta de un recurso estatico con control de version (cache busting).
 *
 * @param string $path Ruta relativa del archivo.
 * @return string|null Ruta con timestamp de modificacion o null si no existe.
 */
function asset($path)
{
    static $cache = [];

    if (isset($cache[$path])) {
        return $cache[$path];
    }

    $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? (__DIR__ . '/..');
    $realFile = $docRoot . '/' . $path;

    if (file_exists($realFile)) {
        return $cache[$path] = '/' . $path . '?v=' . filemtime($realFile);
    }

    return null;
}

/**
 * Formatea una fecha en formato relativo (hace X tiempo).
 *
 * @param string|int|null $datetime Fecha o timestamp.
 * @return string Texto relativo o fecha formateada.
 */
function getTimeAgo($datetime): string
{
    if (empty($datetime)) {
        return '';
    }

    $timestamp = is_numeric($datetime) ? (int) $datetime : strtotime((string) $datetime);
    if (!$timestamp) {
        return (string) $datetime;
    }

    $diff = time() - $timestamp;
    if ($diff < 0) {
        $diff = 0;
    }

    if ($diff < 60) {
        return 'Justo ahora';
    }

    $minutes = (int) floor($diff / 60);
    if ($minutes < 60) {
        return "Hace {$minutes} min" . ($minutes > 1 ? 's' : '');
    }

    $hours = (int) floor($diff / 3600);
    if ($hours < 24) {
        return "Hace {$hours} hora" . ($hours > 1 ? 's' : '');
    }

    $days = (int) floor($diff / 86400);
    if ($days < 7) {
        return "Hace {$days} dia" . ($days > 1 ? 's' : '');
    }

    return date('d/m/Y', $timestamp);
}

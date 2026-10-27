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

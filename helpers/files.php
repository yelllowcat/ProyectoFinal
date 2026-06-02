<?php

/**
 * Genera la ruta de un recurso estatico con control de version (cache busting).
 *
 * @param string $path Ruta relativa del archivo.
 * @return string|null Ruta con timestamp de modificacion o null si no existe.
 */
function asset($path)
{
    $realFile = $_SERVER['DOCUMENT_ROOT'] . '/' . $path;

    if (file_exists($realFile)) {
        return '/' . $path . '?v=' . filemtime($realFile);
    }
}


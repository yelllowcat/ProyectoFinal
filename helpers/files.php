<?php

function asset($path)
{
    $realFile = $_SERVER['DOCUMENT_ROOT'] . '/' . $path;

    if (file_exists($realFile)) {
        return $path . '?v=' . filemtime($realFile);
    }
}
?>
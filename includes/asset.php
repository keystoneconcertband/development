<?php
/**
 * Generate a script or stylesheet URL with cache-busting query string
 * based on file modification time
 */
function asset($path) {
    $file = $_SERVER['DOCUMENT_ROOT'] . $path;
    if (file_exists($file)) {
        $mtime = filemtime($file);
        return $path . '?v=' . $mtime;
    }
    return $path;
}
?>

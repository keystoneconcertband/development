<?php
/**
 * Generate a cache-busted local asset URL and its SHA-384 SRI value.
 */
function asset($path, &$integrity = null) {
    $integrity = null;
    $file = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . ltrim($path, '/');
    if (file_exists($file)) {
        $mtime = filemtime($file);
        $integrity = 'sha384-' . base64_encode(hash_file('sha384', $file, true));
        return $path . '?v=' . $mtime;
    }
    return $path;
}
?>

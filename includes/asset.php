<?php
/**
 * Generate a cache-busted local asset URL and its SHA-384 SRI value.
 */
function asset($path, &$integrity = null) {
    $integrity = null;
    $cleanPath = '/' . ltrim($path, '/');
    $docRoot = rtrim(!empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : dirname(__DIR__), '/');

    // Check direct path first
    $file = $docRoot . $cleanPath;

    // Fallback mapping for legacy asset paths
    if (!file_exists($file)) {
        if (strpos($cleanPath, '/css/') === 0) {
            $cleanPath = '/assets' . $cleanPath;
        } elseif (strpos($cleanPath, '/kcb-js/') === 0) {
            $cleanPath = '/assets/js/' . substr($cleanPath, 8);
        } elseif (strpos($cleanPath, '/images/') === 0) {
            $cleanPath = '/assets' . $cleanPath;
        } elseif (strpos($cleanPath, '/media/') === 0) {
            $cleanPath = '/assets' . $cleanPath;
        }
        $file = $docRoot . $cleanPath;
    }

    if (file_exists($file)) {
        $mtime = filemtime($file);
        $integrity = 'sha384-' . base64_encode(hash_file('sha384', $file, true));
        return $cleanPath . '?v=' . $mtime;
    }

    return $cleanPath;
}
?>

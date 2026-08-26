<?php

// Public pages can be cached only when the request is not tied to a session.
if (session_status() === PHP_SESSION_NONE && isset($_COOKIE[session_name()])) {
    session_start();
}

if (session_status() === PHP_SESSION_ACTIVE) {
    header('Cache-Control: private, no-store, no-cache, must-revalidate');
    return;
}

header('Cache-Control: public, max-age=3600, s-maxage=3600, stale-while-revalidate=86400');
header('Vary: Cookie');

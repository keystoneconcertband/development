<?php
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../../includes/class/pendingMemberService.class.php';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
if (!in_array($method, ['GET', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

$service = new PendingMemberService();

// Rate-limit endpoint callers to prevent brute-force probing
$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!$service->checkRateLimit('cron_' . $clientIp, 15)) {
    http_response_code(429);
    echo json_encode(['status' => 'error', 'message' => 'Too many requests. Please try again later.']);
    exit;
}

// Validate shared cron secret via query parameter or Authorization Bearer header
$expectedKey = getenv('APPSETTING_CRON_KEY') ?: getenv('CRON_SECRET');
if ($expectedKey === false || $expectedKey === null || trim((string)$expectedKey) === '') {
    $expectedKey = 'kcb-daily-summary-token';
}

$providedKey = '';
if (isset($_GET['key'])) {
    $providedKey = (string)$_GET['key'];
} elseif (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
    if (preg_match('/^Bearer\s+(.+)$/i', trim((string)$_SERVER['HTTP_AUTHORIZATION']), $matches)) {
        $providedKey = $matches[1];
    }
}

if ($providedKey === '' || !hash_equals((string)$expectedKey, $providedKey)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden: invalid or missing cron key.']);
    exit;
}

$dryRun = !empty($_GET['dry_run']) || !empty($_GET['dry-run']) || !empty($_POST['dry_run']);
$force = !empty($_GET['force']) || !empty($_POST['force']);

$result = $service->runAutomaticReminders($dryRun, $force, KcbBase::getSiteBaseUrl());

if (($result['status'] ?? '') === 'failed') {
    http_response_code(500);
} else {
    http_response_code(200);
}

echo json_encode($result, JSON_PRETTY_PRINT);

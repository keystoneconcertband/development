<?php
/**
 * api/cron/daily_log_summary.php
 *
 * Secure HTTP endpoint for triggering the daily issue alert summary via
 * GitHub Actions, Azure Logic Apps, or external monitoring cron triggers.
 */

require_once __DIR__ . '/../../includes/class/dailySummaryService.class.php';
require_once __DIR__ . '/../../includes/class/kcbLogger.class.php';

header('Content-Type: application/json');

$service = new DailySummaryService();
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

// Rate limiting: max 10 requests per hour per IP to prevent DoS / spam
if (!$service->checkRateLimit($clientIp, 10)) {
    KcbLogger::getInstance()->warning("Cron webhook rate limit exceeded", [
        'ip'  => $clientIp,
        'uri' => $_SERVER['REQUEST_URI'] ?? '',
    ]);
    http_response_code(429);
    echo json_encode([
        'status' => 'rate_limited',
        'error'  => 'Too many requests. Please try again later.',
    ]);
    exit;
}

// Security: Verify CRON authorization key
$expectedKey = getenv('APPSETTING_CRON_KEY') ?: getenv('CRON_SECRET');
if (empty($expectedKey)) {
    // If no custom key is configured yet in environment, use project fallback
    $expectedKey = 'kcb-daily-summary-token';
}

$providedKey = '';
if (!empty($_GET['key'])) {
    $providedKey = $_GET['key'];
} elseif (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
    if (preg_match('/Bearer\s+(\S+)/i', $_SERVER['HTTP_AUTHORIZATION'], $m)) {
        $providedKey = $m[1];
    }
}

if (!hash_equals($expectedKey, (string)$providedKey)) {
    KcbLogger::getInstance()->warning("Unauthorized cron webhook request attempt", [
        'ip'  => $clientIp,
        'uri' => $_SERVER['REQUEST_URI'] ?? '',
    ]);
    http_response_code(401);
    echo json_encode([
        'status' => 'unauthorized',
        'error'  => 'Missing or invalid authentication key.',
    ]);
    exit;
}

$targetDate = !empty($_GET['date']) ? preg_replace('/[^0-9\-]/', '', $_GET['date']) : null;
$force = !empty($_GET['force']);
$dryRun = !empty($_GET['dry_run']);

$result = $service->run($targetDate, $force, $dryRun);

if ($result['status'] === 'failed') {
    http_response_code(500);
} else {
    http_response_code(200);
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);


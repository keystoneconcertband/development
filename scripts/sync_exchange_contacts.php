<?php
/**
 * CLI & Web Administrative Runner for Microsoft 365 / Exchange Online Contact Sync
 *
 * CLI usage:
 *   php scripts/sync_exchange_contacts.php [--dry-run] [--uid=123] [--batch-size=25]
 */

require_once __DIR__ . '/../includes/class/member.class.php';
require_once __DIR__ . '/../includes/class/exchangeContactService.class.php';

$isCli = (php_sapi_name() === 'cli' || empty($_SERVER['REMOTE_ADDR']));
$dryRun = false;
$uid = null;
$batchSize = 25;

if ($isCli) {
    $options = getopt('', ['dry-run', 'uid::', 'batch-size::']);
    $dryRun = isset($options['dry-run']);
    if (!empty($options['uid'])) {
        $uid = (int)$options['uid'];
    }
    if (!empty($options['batch-size']) && (int)$options['batch-size'] > 0) {
        $batchSize = (int)$options['batch-size'];
    }

    $service = new ExchangeContactService();
    $result = $service->syncAllActiveContacts($uid, 'CLI', $dryRun, $batchSize);

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit(($result['status'] ?? '') === 'failed' ? 1 : 0);
}

// HTTP mode
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$service = new ExchangeContactService();
$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!$service->checkRateLimit('sync_' . $clientIp, 10)) {
    http_response_code(429);
    echo json_encode(['status' => 'error', 'message' => 'Too many requests. Please try again later.']);
    exit;
}

new Member(false, true);

$isAdmin = isset($_SESSION['accountType']) && in_array((int)$_SESSION['accountType'], [1, 2], true);

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
$hasValidKey = ($providedKey !== '' && hash_equals((string)$expectedKey, $providedKey));

$serverName = $_SERVER['SERVER_NAME'] ?? '';
$isLocalDev = ($serverName === 'localhost' || in_array($clientIp, ['127.0.0.1', '::1', '192.168.64.1'], true));

if (!$isAdmin && !$hasValidKey && !$isLocalDev) {
    KcbLogger::getInstance()->warning("Unauthorized attempt to access scripts/sync_exchange_contacts.php", [
        'ip'  => $clientIp,
        'uri' => $_SERVER['REQUEST_URI'] ?? '',
    ]);
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden.']);
    exit;
}

$dryRun = !empty($_GET['dry_run']) || !empty($_GET['dry-run']);
if (!empty($_GET['uid'])) {
    $uid = (int)$_GET['uid'];
}
if (!empty($_GET['batch_size']) && (int)$_GET['batch_size'] > 0) {
    $batchSize = (int)$_GET['batch_size'];
}

$actor = !empty($_SESSION['email']) ? (string)$_SESSION['email'] : 'HTTP_SYNC';
$result = $service->syncAllActiveContacts($uid, $actor, $dryRun, $batchSize);

http_response_code(($result['status'] ?? '') === 'failed' ? 500 : 200);
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

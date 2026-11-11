<?php
/**
 * CLI & Web Administrative Runner for Pending Member Onboarding Reminders
 *
 * CLI usage:
 *   php scripts/pending_member_reminders.php [--dry-run] [--force] [--base-url=https://...]
 */

require_once __DIR__ . '/../includes/class/member.class.php';
require_once __DIR__ . '/../includes/class/pendingMemberService.class.php';

$isCli = (php_sapi_name() === 'cli');
$dryRun = false;
$force = false;
$baseUrl = null;

if ($isCli) {
    $options = getopt('', ['dry-run', 'force', 'base-url::']);
    $dryRun = isset($options['dry-run']);
    $force = isset($options['force']);
    if (!empty($options['base-url'])) {
        $baseUrl = trim((string)$options['base-url']);
    }

    $service = new PendingMemberService();
    $result = $service->runAutomaticReminders($dryRun, $force, $baseUrl ?: KcbBase::getSiteBaseUrl());

    echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
    exit(($result['status'] ?? '') === 'failed' ? 1 : 0);
}

// HTTP mode
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

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
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
$isLocalDev = ($serverName === 'localhost' || in_array($clientIp, ['127.0.0.1', '::1', '192.168.64.1'], true));

if (!$isAdmin && !$hasValidKey && !$isLocalDev) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden.']);
    exit;
}

$dryRun = !empty($_GET['dry_run']) || !empty($_GET['dry-run']);
$force = !empty($_GET['force']);

$service = new PendingMemberService();
$result = $service->runAutomaticReminders($dryRun, $force, KcbBase::getSiteBaseUrl());

http_response_code(($result['status'] ?? '') === 'failed' ? 500 : 200);
echo json_encode($result, JSON_PRETTY_PRINT);

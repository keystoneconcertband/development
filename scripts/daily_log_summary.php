<?php
/**
 * scripts/daily_log_summary.php
 *
 * Standalone CLI script to trigger the daily log summary and issue alerting.
 *
 * Usage:
 *   php scripts/daily_log_summary.php
 *   php scripts/daily_log_summary.php --date=2026-10-27
 *   php scripts/daily_log_summary.php --force
 *   php scripts/daily_log_summary.php --dry-run
 */

require_once __DIR__ . '/../includes/class/dailySummaryService.class.php';
require_once __DIR__ . '/../includes/class/kcbLogger.class.php';

// Support execution via CLI or authorized HTTP request
$isCli = (php_sapi_name() === 'cli' || empty($_SERVER['REMOTE_ADDR']));

if (!$isCli) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $service = new DailySummaryService();
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $serverName = $_SERVER['SERVER_NAME'] ?? '';

    // Rate limiting: max 10 executions per hour per IP
    if (!$service->checkRateLimit($clientIp, 10)) {
        KcbLogger::getInstance()->warning("Daily summary script rate limit exceeded", [
            'ip'  => $clientIp,
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
        ]);
        http_response_code(429);
        header('Content-Type: text/plain; charset=utf-8');
        echo "429 Too Many Requests: Rate limit exceeded. Please try again later.\n";
        exit;
    }

    // Security check: verify admin session, cron key, or local development environment
    $expectedKey = getenv('APPSETTING_CRON_KEY') ?: getenv('CRON_SECRET');
    if (empty($expectedKey)) {
        $expectedKey = 'kcb-daily-summary-token';
    }

    $providedKey = $_GET['key'] ?? '';
    if (empty($providedKey) && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
        if (preg_match('/Bearer\s+(\S+)/i', $_SERVER['HTTP_AUTHORIZATION'], $m)) {
            $providedKey = $m[1];
        }
    }

    $isAdmin = isset($_SESSION['accountType']) && in_array($_SESSION['accountType'], [1, 2], true);
    $hasValidKey = (!empty($providedKey) && hash_equals($expectedKey, (string)$providedKey));
    $isLocalDev = ($serverName === 'localhost' || in_array($clientIp, ['127.0.0.1', '::1', '192.168.64.1'], true));

    if (!$isAdmin && !$hasValidKey && !$isLocalDev) {
        KcbLogger::getInstance()->warning("Unauthorized attempt to access scripts/daily_log_summary.php", [
            'ip'  => $clientIp,
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
        ]);
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Access Denied: You do not have permission to execute this script.\n";
        exit;
    }

    header('Content-Type: text/plain; charset=utf-8');
    $targetDate = !empty($_GET['date']) ? preg_replace('/[^0-9\-]/', '', $_GET['date']) : null;
    $force = isset($_GET['force']) && ($_GET['force'] === '1' || strtolower($_GET['force']) === 'true');
    $dryRun = isset($_GET['dry-run']) || isset($_GET['dry_run']);
} else {
    // Parse CLI options
    $options = getopt('', ['date:', 'force', 'dry-run']);
    $targetDate = isset($options['date']) ? $options['date'] : null;
    $force = isset($options['force']);
    $dryRun = isset($options['dry-run']);
}

echo "=== Keystone Concert Band: Daily Log Summary ===\n";

$service = new DailySummaryService();
$result = $service->run($targetDate, $force, $dryRun);

echo "Date:       " . ($result['date'] ?? 'N/A') . "\n";
echo "Status:     " . ($result['status'] ?? 'unknown') . "\n";
echo "Issues:     " . ($result['issue_count'] ?? 0) . "\n";

if (!empty($result['login_stats'])) {
    printf("Logins:     %d successful (%d unique members, %d total attempts)\n",
        $result['login_stats']['successful_logins'] ?? 0,
        $result['login_stats']['unique_users'] ?? 0,
        $result['login_stats']['total_attempts'] ?? 0
    );
}

if (isset($result['message'])) {
    echo "Message:    " . $result['message'] . "\n";
}
if (isset($result['recipient'])) {
    echo "Recipient:  " . $result['recipient'] . "\n";
}

if (!empty($result['stats'])) {
    echo "\nStats Breakdown:\n";
    foreach ($result['stats'] as $k => $v) {
        printf("  %-10s : %d\n", ucfirst($k), $v);
    }
}

if ($dryRun && !empty($result['login_stats']['recent_logins'])) {
    echo "\nMember Logins Today:\n";
    foreach ($result['login_stats']['recent_logins'] as $login) {
        printf("  [%s] %-30s - %s\n", $login['time'], $login['email'], $login['status']);
    }
}

if ($dryRun && !empty($result['issues'])) {
    echo "\nDetected Issues:\n";
    foreach ($result['issues'] as $issue) {
        printf("  [%s] [%s] %s - %s\n", $issue['timestamp'], $issue['level'], $issue['category'], $issue['message']);
    }
}

echo "===============================================\n";

exit($result['status'] === 'failed' ? 1 : 0);


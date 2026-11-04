<?php
/**
 * System Health Check Page & Endpoint
 *
 * Provides real-time health diagnostics for application services (PHP runtime,
 * MySQL database, storage permissions, and server environment).
 * Supports both responsive HTML presentation for administrators/browsers and
 * structured JSON output for monitoring probes (Azure Web Apps, UptimeRobot, etc.).
 */

$startTime = microtime(true);

// Determine environment
$serverName = $_SERVER['SERVER_NAME'] ?? '';
$appEnv = getenv('APP_ENV') ?: (getenv('APPSETTING_APP_ENV') ?: '');
$isDev = strpos($serverName, 'dev') !== false
    || strpos($serverName, 'localhost') !== false
    || strpos($serverName, '127.0.0.1') !== false
    || strtolower((string)$appEnv) === 'development'
    || strtolower((string)$appEnv) === 'dev';

// Ensure default timezone is set to Eastern Time
date_default_timezone_set('America/New_York');

// Populate session if session cookie exists so Member Area dropdown displays for authenticated users
if (session_status() === PHP_SESSION_NONE && isset($_COOKIE[session_name()])) {
    @session_start();
}

$checks = [];
$overallHealthy = true;

// 1. PHP Runtime Check
$phpMemoryLimit = ini_get('memory_limit') ?: 'unknown';
$phpMemoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB';
$phpMemoryPeak  = round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB';
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
$missingExtensions = [];
foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

$phpOk = empty($missingExtensions);
if (!$phpOk && (in_array('pdo', $missingExtensions) || in_array('pdo_mysql', $missingExtensions))) {
    $overallHealthy = false;
}

$checks['php'] = [
    'status'             => $phpOk ? 'ok' : 'warning',
    'version'            => PHP_VERSION,
    'memory_usage'       => $phpMemoryUsage,
    'memory_peak'        => $phpMemoryPeak,
    'memory_limit'       => $phpMemoryLimit,
    'max_execution_time' => (ini_get('max_execution_time') ?: '0') . 's',
    'missing_extensions' => $missingExtensions,
];

// 2. Database Connectivity Check
$settingsFile = __DIR__ . '/includes/class/settings.ini.php';
$settings = file_exists($settingsFile) ? @parse_ini_file($settingsFile) : [];
$dbHost = getenv('MYSQL_HOST') ?: (getenv('DB_HOST') ?: ($settings['host'] ?? 'localhost'));
$dbName = getenv('MYSQL_DATABASE') ?: (getenv('DB_NAME') ?: ($settings['dbname'] ?? 'kcb_prod'));
$dbUser = getenv('MYSQL_USER') ?: (getenv('DB_USER') ?: ($settings['user'] ?? ''));
$dbPass = getenv('MYSQL_PASSWORD') ?: (getenv('DB_PASSWORD') ?: ($settings['password'] ?? ''));

$dbLatencyMs = null;
$dbError = null;
$dbOk = false;

if (empty($dbHost) || empty($dbName)) {
    $dbError = 'Database configuration not found';
    $overallHealthy = false;
} else {
    try {
        $dsn = "mysql:dbname={$dbName};host={$dbHost}";
        $initCommand = defined('Pdo\Mysql::ATTR_INIT_COMMAND')
            ? \Pdo\Mysql::ATTR_INIT_COMMAND
            : PDO::MYSQL_ATTR_INIT_COMMAND;

        $dbStartTime = microtime(true);
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_TIMEOUT          => 3,
            PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
            $initCommand               => 'SET NAMES utf8',
        ]);
        $stmt = $pdo->query('SELECT 1');
        $stmt->fetch();
        $dbLatencyMs = round((microtime(true) - $dbStartTime) * 1000, 2);
        $dbOk = true;
    } catch (\Throwable $e) {
        $dbOk = false;
        $overallHealthy = false;
        $dbError = $isDev ? $e->getMessage() : 'Unable to connect to database';
    }
}

$checks['database'] = [
    'status'     => $dbOk ? 'ok' : 'error',
    'host'       => $dbHost,
    'database'   => $dbName,
    'latency_ms' => $dbLatencyMs,
    'error'      => $dbError,
];

// 3. Storage / Logging Check
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir) && is_writable(__DIR__)) {
    @mkdir($logDir, 0755, true);
}
$logsWritable = is_dir($logDir) && is_writable($logDir);
$tempWritable = is_writable(sys_get_temp_dir());

$checks['storage'] = [
    'status'        => $logsWritable ? 'ok' : 'warning',
    'logs_writable' => $logsWritable,
    'logs_path'     => 'logs/',
    'temp_writable' => $tempWritable,
];

// 4. Server & Environment Check
$checks['server'] = [
    'status'      => 'ok',
    'environment' => $isDev ? 'development' : 'production',
    'time'        => date('Y-m-d H:i:s T'),
    'iso_time'    => date('c'),
    'hostname'    => gethostname() ?: ($_SERVER['SERVER_NAME'] ?? 'unknown'),
    'sapi'        => PHP_SAPI,
    'os'          => PHP_OS,
    'https'       => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443),
];

$totalDurationMs = round((microtime(true) - $startTime) * 1000, 2);
$httpStatusCode = $overallHealthy ? 200 : 503;
$statusText = $overallHealthy ? 'healthy' : 'unhealthy';

// Prevent caching of health responses
http_response_code($httpStatusCode);
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Detect if JSON output was requested
$wantsJson = (isset($_GET['format']) && $_GET['format'] === 'json')
    || isset($_GET['json'])
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if ($wantsJson) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status'      => $statusText,
        'http_code'   => $httpStatusCode,
        'timestamp'   => date('c'),
        'duration_ms' => $totalDurationMs,
        'checks'      => $checks,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once 'includes/asset.php'; ?>
    <?php require_once 'includes/common_meta.php'; ?>
    <meta name="description" content="Keystone Concert Band System Health Status">
    <meta name="robots" content="noindex, nofollow">
    <title>System Health - Keystone Concert Band</title>
    <?php require_once 'includes/common_css.php'; ?>
    <style>
        body {
            padding-top: 70px;
            background-color: #f8f9fa;
        }
        .health-card {
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 0.6rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.04);
            transition: all 0.2s ease-in-out;
        }
        .health-card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }
        .health-icon-box {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            font-size: 1.35rem;
        }
        .metric-label {
            font-size: 0.82rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .metric-value {
            font-size: 1rem;
            font-weight: 500;
        }
    </style>
</head>
<body>

    <?php require_once 'includes/nav.php'; ?>

    <main class="container my-4">
        <!-- Page Title & Quick Actions -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
                <h1 class="h2 mb-1">
                    <i class="fa-solid fa-heart-pulse text-danger me-2" aria-hidden="true"></i>System Health
                </h1>
                <p class="text-muted mb-0">Real-time status diagnostics for Keystone Concert Band services.</p>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <a href="health.php" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-rotate-right me-1" aria-hidden="true"></i>Refresh
                </a>
                <a href="health.php?format=json" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-code me-1" aria-hidden="true"></i>JSON View
                </a>
            </div>
        </div>

        <!-- Overall Status Alert -->
        <?php if ($overallHealthy): ?>
            <div class="alert alert-success d-flex align-items-center p-3 mb-4 shadow-sm" role="alert">
                <i class="fa-solid fa-circle-check fs-2 text-success me-3" aria-hidden="true"></i>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2">
                        <strong class="fs-5">All Systems Operational</strong>
                        <span class="badge bg-success">HTTP 200</span>
                    </div>
                    <div class="text-secondary small mt-1">
                        All monitored components (database, PHP runtime, storage) passed health checks in <?= htmlspecialchars((string)$totalDurationMs) ?> ms.
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger d-flex align-items-center p-3 mb-4 shadow-sm" role="alert">
                <i class="fa-solid fa-triangle-exclamation fs-2 text-danger me-3" aria-hidden="true"></i>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2">
                        <strong class="fs-5">System Issues Detected</strong>
                        <span class="badge bg-danger">HTTP <?= htmlspecialchars((string)$httpStatusCode) ?></span>
                    </div>
                    <div class="text-danger small mt-1">
                        One or more critical services are not responding normally. Review component cards below.
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Diagnostics Cards Grid -->
        <div class="row g-4">
            <!-- 1. Database Check Card -->
            <div class="col-md-6 col-lg-6">
                <div class="card h-100 health-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="health-icon-box bg-primary-subtle text-primary">
                                    <i class="fa-solid fa-database" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">Database</h5>
                                    <small class="text-muted">MySQL / MariaDB Connection</small>
                                </div>
                            </div>
                            <?php if ($checks['database']['status'] === 'ok'): ?>
                                <span class="badge bg-success">Connected</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Error</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($checks['database']['status'] === 'ok'): ?>
                            <ul class="list-group list-group-flush mb-0">
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="metric-label">Host</span>
                                    <span class="metric-value font-monospace"><?= htmlspecialchars((string)$checks['database']['host']) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="metric-label">Database</span>
                                    <span class="metric-value font-monospace"><?= htmlspecialchars((string)$checks['database']['database']) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="metric-label">Ping Latency</span>
                                    <span class="metric-value text-success"><?= htmlspecialchars((string)$checks['database']['latency_ms']) ?> ms</span>
                                </li>
                            </ul>
                        <?php else: ?>
                            <div class="alert alert-danger py-2 px-3 small mb-0 mt-2">
                                <i class="fa-solid fa-circle-exclamation me-1" aria-hidden="true"></i>
                                <?= htmlspecialchars((string)($checks['database']['error'] ?? 'Connection failed')) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 2. Storage & File System Check Card -->
            <div class="col-md-6 col-lg-6">
                <div class="card h-100 health-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="health-icon-box bg-info-subtle text-info">
                                    <i class="fa-solid fa-hard-drive" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">Storage & Logs</h5>
                                    <small class="text-muted">Filesystem Write Permissions</small>
                                </div>
                            </div>
                            <?php if ($checks['storage']['logs_writable']): ?>
                                <span class="badge bg-success">Writable</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Permission Issue</span>
                            <?php endif; ?>
                        </div>

                        <ul class="list-group list-group-flush mb-0">
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="metric-label">Logs Directory</span>
                                <span class="metric-value font-monospace">
                                    logs/
                                    <?php if ($checks['storage']['logs_writable']): ?>
                                        <i class="fa-solid fa-check text-success ms-1" title="Writable" aria-hidden="true"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-xmark text-danger ms-1" title="Not writable" aria-hidden="true"></i>
                                    <?php endif; ?>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="metric-label">Temp Directory</span>
                                <span class="metric-value">
                                    <?= $checks['storage']['temp_writable'] ? 'Writable' : 'Read-Only' ?>
                                    <i class="fa-solid <?= $checks['storage']['temp_writable'] ? 'fa-check text-success' : 'fa-xmark text-danger' ?> ms-1" aria-hidden="true"></i>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="metric-label">Logging Driver</span>
                                <span class="metric-value">Monolog / Daily Rotate</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 3. PHP Runtime Check Card -->
            <div class="col-md-6 col-lg-6">
                <div class="card h-100 health-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="health-icon-box bg-secondary-subtle text-secondary">
                                    <i class="fa-brands fa-php fs-3" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">PHP Runtime</h5>
                                    <small class="text-muted">Engine & Memory Status</small>
                                </div>
                            </div>
                            <span class="badge bg-success">v<?= htmlspecialchars((string)PHP_VERSION) ?></span>
                        </div>

                        <ul class="list-group list-group-flush mb-0">
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="metric-label">Memory Usage</span>
                                <span class="metric-value"><?= htmlspecialchars((string)$checks['php']['memory_usage']) ?> (Peak: <?= htmlspecialchars((string)$checks['php']['memory_peak']) ?>)</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="metric-label">Memory Limit</span>
                                <span class="metric-value font-monospace"><?= htmlspecialchars((string)$checks['php']['memory_limit']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="metric-label">Max Execution</span>
                                <span class="metric-value font-monospace"><?= htmlspecialchars((string)$checks['php']['max_execution_time']) ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 4. Server Environment Card -->
            <div class="col-md-6 col-lg-6">
                <div class="card h-100 health-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="health-icon-box bg-dark-subtle text-dark">
                                    <i class="fa-solid fa-server" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">Environment</h5>
                                    <small class="text-muted">Hosting & Server Details</small>
                                </div>
                            </div>
                            <span class="badge <?= $isDev ? 'bg-info text-dark' : 'bg-primary' ?>">
                                <?= htmlspecialchars(ucfirst((string)$checks['server']['environment'])) ?>
                            </span>
                        </div>

                        <ul class="list-group list-group-flush mb-0">
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="metric-label">Server Time (EST/EDT)</span>
                                <span class="metric-value"><?= htmlspecialchars((string)$checks['server']['time']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="metric-label">Hostname</span>
                                <span class="metric-value font-monospace small"><?= htmlspecialchars((string)$checks['server']['hostname']) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="metric-label">Platform</span>
                                <span class="metric-value"><?= htmlspecialchars((string)PHP_OS) ?> (<?= htmlspecialchars((string)PHP_SAPI) ?>)</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monitoring Integration Guide -->
        <div class="card mt-4 health-card bg-light">
            <div class="card-body">
                <h6 class="card-title mb-2">
                    <i class="fa-solid fa-circle-info text-info me-1" aria-hidden="true"></i>Automated Monitoring Integration
                </h6>
                <p class="card-text small text-muted mb-2">
                    Automated uptime pingers (e.g., Azure Web Apps Health Check, Pingdom, UptimeRobot) can probe either:
                </p>
                <div class="row g-2">
                    <div class="col-md-6">
                        <code class="d-block p-2 rounded bg-white border">GET /health.php?format=json</code>
                    </div>
                    <div class="col-md-6">
                        <code class="d-block p-2 rounded bg-white border">GET /api/health.php</code>
                    </div>
                </div>
                <div class="mt-2 text-muted small">
                    Returns HTTP <strong>200 OK</strong> when healthy and HTTP <strong>503 Service Unavailable</strong> when degraded.
                </div>
                <div class="mt-2 pt-2 border-top text-muted small">
                    <i class="fa-solid fa-triangle-exclamation text-warning me-1" aria-hidden="true"></i>
                    <strong>Azure App Service Note:</strong> Azure Web Apps (Linux) runs on <strong>Nginx</strong> and does not evaluate Apache <code>.htaccess</code> rewrite rules. Accessing extensionless <code>/health</code> will return a 404 from Nginx; always use <code>/health.php</code> or <code>/api/health.php</code> directly.
                </div>
            </div>
        </div>
    </main>

    <div class="container">
        <?php require_once 'includes/footer.php'; ?>
    </div>

    <?php require_once 'includes/common_js.php'; ?>
</body>
</html>


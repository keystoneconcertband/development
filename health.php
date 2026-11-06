<?php
/**
 * System Health Check Page & Endpoint
 *
 * Provides real-time health diagnostics for application services (PHP runtime,
 * MySQL database, storage permissions, and server environment).
 * Supports both responsive HTML presentation for administrators/browsers and
 * structured JSON output for monitoring probes (Azure Web Apps, UptimeRobot, etc.).
 *
 * Security: Sensitive infrastructure parameters (database host, database name,
 * internal filesystem paths, exact PHP version, server machine hostname, and OS/SAPI)
 * are masked for unauthenticated public viewers, while authorized administrators
 * and authenticated monitoring probes have full telemetry access.
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

// Populate session if session cookie exists so Member Area dropdown and permissions are available
if (session_status() === PHP_SESSION_NONE && isset($_COOKIE[session_name()])) {
    @session_start();
}

// Authentication & privilege check for viewing sensitive infrastructure details
$isAuthenticated = !empty($_SESSION['email']);
$isAdmin = $isAuthenticated && isset($_SESSION['accountType']) && in_array((int)$_SESSION['accountType'], [1, 2], true);

// Check for authorized API key via GET parameter or Authorization Bearer header
$expectedKey = getenv('APPSETTING_CRON_KEY') ?: getenv('CRON_SECRET');
$providedKey = $_GET['key'] ?? '';
if (empty($providedKey) && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
    if (preg_match('/Bearer\s+(\S+)/i', $_SERVER['HTTP_AUTHORIZATION'], $m)) {
        $providedKey = $m[1];
    }
}
$hasValidKey = !empty($expectedKey) && !empty($providedKey) && hash_equals((string)$expectedKey, (string)$providedKey);

// Grant detailed view to authenticated members/admins, authorized monitoring keys, or local dev
$canViewDetails = $isDev || $isAuthenticated || $hasValidKey;

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
    'version'            => $canViewDetails ? PHP_VERSION : 'PHP 8.x',
    'memory_usage'       => $canViewDetails ? $phpMemoryUsage : null,
    'memory_peak'        => $canViewDetails ? $phpMemoryPeak : null,
    'memory_limit'       => $canViewDetails ? $phpMemoryLimit : null,
    'max_execution_time' => $canViewDetails ? ((ini_get('max_execution_time') ?: '0') . 's') : null,
    'missing_extensions' => $missingExtensions,
];

// 2. Database Connectivity Check
$settingsFile = __DIR__ . '/includes/class/settings.ini.php';
$settings = file_exists($settingsFile) ? @parse_ini_file($settingsFile) : [];
$dbHost = getenv('MYSQL_HOST') ?: (getenv('DB_HOST') ?: ($settings['host'] ?? 'localhost'));
$dbName = getenv('MYSQL_DATABASE') ?: (getenv('DB_NAME') ?: ($settings['dbname'] ?? 'kcb_prod'));
$dbUser = getenv('MYSQL_USER') ?: (getenv('DB_USER') ?: ($settings['user'] ?? ''));
$dbPass = getenv('MYSQL_PASSWORD') ?: (getenv('DB_PASSWORD') ?: ($settings['password'] ?? ''));
$dbPort = getenv('MYSQL_PORT') ?: (getenv('DB_PORT') ?: ($settings['port'] ?? null));

$dbLatencyMs = null;
$dbError = null;
$dbOk = false;
$dbSslActive = false;
$dbSslCipher = null;

if (empty($dbHost) || empty($dbName)) {
    $dbError = $canViewDetails ? 'Database configuration not found' : 'Database service unconfigured';
    $overallHealthy = false;
} else {
    try {
        $dsn = "mysql:dbname={$dbName};host={$dbHost}";
        if (!empty($dbPort)) {
            $dsn .= ";port={$dbPort}";
        }

        $initCommand = defined('Pdo\Mysql::ATTR_INIT_COMMAND')
            ? \Pdo\Mysql::ATTR_INIT_COMMAND
            : PDO::MYSQL_ATTR_INIT_COMMAND;

        $pdoOptions = [
            PDO::ATTR_TIMEOUT          => 3,
            PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
            $initCommand               => 'SET NAMES utf8',
        ];

        # SSL / TLS Configuration
        $sslCa = getenv('MYSQL_SSL_CA') ?: (getenv('DB_SSL_CA') ?: ($settings['ssl_ca'] ?? null));
        $sslCert = getenv('MYSQL_SSL_CERT') ?: (getenv('DB_SSL_CERT') ?: ($settings['ssl_cert'] ?? null));
        $sslKey = getenv('MYSQL_SSL_KEY') ?: (getenv('DB_SSL_KEY') ?: ($settings['ssl_key'] ?? null));
        $sslCipher = getenv('MYSQL_SSL_CIPHER') ?: (getenv('DB_SSL_CIPHER') ?: ($settings['ssl_cipher'] ?? null));

        $sslEnabled = getenv('MYSQL_SSL') ?: (getenv('DB_SSL') ?: ($settings['ssl'] ?? false));
        $sslEnabled = filter_var($sslEnabled, FILTER_VALIDATE_BOOLEAN) || !empty($sslCa) || !empty($sslCert);

        if ($sslEnabled) {
            $attrSslCa = defined('Pdo\Mysql::ATTR_SSL_CA') ? \Pdo\Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA;
            $attrSslVerify = defined('Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT') ? \Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT : PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT;
            $attrSslKey = defined('Pdo\Mysql::ATTR_SSL_KEY') ? \Pdo\Mysql::ATTR_SSL_KEY : PDO::MYSQL_ATTR_SSL_KEY;
            $attrSslCert = defined('Pdo\Mysql::ATTR_SSL_CERT') ? \Pdo\Mysql::ATTR_SSL_CERT : PDO::MYSQL_ATTR_SSL_CERT;
            $attrSslCipher = defined('Pdo\Mysql::ATTR_SSL_CIPHER') ? \Pdo\Mysql::ATTR_SSL_CIPHER : PDO::MYSQL_ATTR_SSL_CIPHER;

            if (!empty($sslCa)) {
                if (!file_exists($sslCa)) {
                    if (file_exists(__DIR__ . '/' . ltrim($sslCa, '/'))) {
                        $sslCa = __DIR__ . '/' . ltrim($sslCa, '/');
                    }
                }
            } else {
                $systemCaBundles = [
                    '/etc/ssl/certs/ca-certificates.crt', // Debian / Ubuntu (Azure Web Apps Linux)
                    '/etc/pki/tls/certs/ca-bundle.crt',     // RHEL / CentOS / AlmaLinux
                    '/etc/ssl/ca-bundle.pem',              // openSUSE
                    '/etc/ssl/cert.pem',                   // macOS / Alpine / FreeBSD
                ];
                foreach ($systemCaBundles as $caPath) {
                    if (file_exists($caPath)) {
                        $sslCa = $caPath;
                        break;
                    }
                }
            }

            if (!empty($sslCa)) {
                $pdoOptions[$attrSslCa] = $sslCa;
            }
            if (!empty($sslKey)) {
                $pdoOptions[$attrSslKey] = $sslKey;
            }
            if (!empty($sslCert)) {
                $pdoOptions[$attrSslCert] = $sslCert;
            }
            if (!empty($sslCipher)) {
                $pdoOptions[$attrSslCipher] = $sslCipher;
            }

            $sslVerify = getenv('MYSQL_SSL_VERIFY_SERVER_CERT');
            if ($sslVerify === false || $sslVerify === null) {
                $sslVerify = $settings['ssl_verify'] ?? true;
            }
            $pdoOptions[$attrSslVerify] = filter_var($sslVerify, FILTER_VALIDATE_BOOLEAN);
        }

        $dbStartTime = microtime(true);
        $pdo = new PDO($dsn, $dbUser, $dbPass, $pdoOptions);
        $stmt = $pdo->query('SELECT 1');
        $stmt->fetch();
        $dbLatencyMs = round((microtime(true) - $dbStartTime) * 1000, 2);
        $dbOk = true;

        try {
            $cipherStmt = $pdo->query("SHOW STATUS LIKE 'Ssl_cipher'");
            $cipherRow = $cipherStmt ? $cipherStmt->fetch(PDO::FETCH_NUM) : null;
            if ($cipherRow && !empty($cipherRow[1])) {
                $dbSslActive = true;
                $dbSslCipher = $cipherRow[1];
            }
        } catch (\Throwable $e) {
            // Ignore status query errors
        }
    } catch (\Throwable $e) {
        $dbOk = false;
        $overallHealthy = false;
        // Never expose raw database exceptions or passwords to public viewers
        $dbError = $canViewDetails ? $e->getMessage() : 'Unable to connect to database service';
    }
}

$checks['database'] = [
    'status'     => $dbOk ? 'ok' : 'error',
    'host'       => $canViewDetails ? $dbHost : 'Protected',
    'database'   => $canViewDetails ? $dbName : 'Protected',
    'ssl'        => $dbSslActive,
    'ssl_cipher' => $canViewDetails ? $dbSslCipher : ($dbSslActive ? 'Protected' : null),
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
    'logs_path'     => $canViewDetails ? 'logs/' : 'Protected',
    'temp_writable' => $tempWritable,
];

// 4. Server & Environment Check
$checks['server'] = [
    'status'      => 'ok',
    'environment' => $canViewDetails ? ($isDev ? 'development' : 'production') : 'Protected',
    'time'        => date('Y-m-d H:i:s T'),
    'iso_time'    => date('c'),
    'hostname'    => $canViewDetails ? (gethostname() ?: ($_SERVER['SERVER_NAME'] ?? 'unknown')) : 'Protected',
    'sapi'        => $canViewDetails ? PHP_SAPI : 'Protected',
    'os'          => $canViewDetails ? PHP_OS : 'Protected',
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

    $response = [
        'status'      => $statusText,
        'http_code'   => $httpStatusCode,
        'timestamp'   => date('c'),
        'duration_ms' => $totalDurationMs,
        'services'    => [
            'database' => $dbOk ? 'operational' : 'degraded',
            'storage'  => $logsWritable ? 'operational' : 'degraded',
            'runtime'  => $phpOk ? 'operational' : 'degraded',
        ],
    ];

    // Only include detailed telemetry and internal configurations if authorized
    if ($canViewDetails) {
        $response['checks'] = $checks;
    }

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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
                <h1 class="h2 mb-1 d-flex flex-wrap align-items-center gap-2">
                    <i class="fa-solid fa-heart-pulse text-danger me-1" aria-hidden="true"></i>System Health
                    <?php if ($canViewDetails): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle fs-6 align-middle">
                            <i class="fa-solid fa-unlock me-1" aria-hidden="true"></i>Admin View
                        </span>
                    <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fs-6 align-middle">
                            <i class="fa-solid fa-shield-halved me-1" aria-hidden="true"></i>Public View
                        </span>
                    <?php endif; ?>
                </h1>
                <p class="text-muted mb-0">Real-time status diagnostics for Keystone Concert Band services.</p>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <a href="health.php" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-rotate-right me-1" aria-hidden="true"></i>Refresh
                </a>
                <a href="health.php?format=json<?= (!empty($providedKey) && $hasValidKey) ? '&key=' . urlencode($providedKey) : '' ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">
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
                                    <h5 class="card-title mb-0">Database Service</h5>
                                    <small class="text-muted">MySQL / MariaDB Connectivity</small>
                                </div>
                            </div>
                            <?php if ($checks['database']['status'] === 'ok'): ?>
                                <span class="badge bg-success">Operational</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Degraded</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($checks['database']['status'] === 'ok'): ?>
                            <ul class="list-group list-group-flush mb-0">
                                <?php if ($canViewDetails): ?>
                                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                        <span class="metric-label">Host</span>
                                        <span class="metric-value font-monospace"><?= htmlspecialchars((string)$checks['database']['host']) ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                        <span class="metric-label">Database</span>
                                        <span class="metric-value font-monospace"><?= htmlspecialchars((string)$checks['database']['database']) ?></span>
                                    </li>
                                <?php else: ?>
                                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                        <span class="metric-label">Connection</span>
                                        <span class="metric-value text-success"><i class="fa-solid fa-circle-check me-1" aria-hidden="true"></i>Active</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                        <span class="metric-label">Target Details</span>
                                        <span class="metric-value text-muted small"><i class="fa-solid fa-lock me-1" aria-hidden="true"></i>Protected</span>
                                    </li>
                                <?php endif; ?>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="metric-label">SSL Transport</span>
                                    <span class="metric-value font-monospace">
                                        <?php if (!empty($checks['database']['ssl'])): ?>
                                            <span class="text-success"><i class="fa-solid fa-lock me-1" aria-hidden="true"></i>Encrypted</span>
                                            <?php if ($canViewDetails && !empty($checks['database']['ssl_cipher'])): ?>
                                                <span class="text-muted small ms-1">(<?= htmlspecialchars((string)$checks['database']['ssl_cipher']) ?>)</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fa-solid fa-lock-open me-1" aria-hidden="true"></i>Unencrypted</span>
                                        <?php endif; ?>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="metric-label">Ping Latency</span>
                                    <span class="metric-value text-success"><?= htmlspecialchars((string)$checks['database']['latency_ms']) ?> ms</span>
                                </li>
                            </ul>
                        <?php else: ?>
                            <div class="alert alert-danger py-2 px-3 small mb-0 mt-2">
                                <i class="fa-solid fa-circle-exclamation me-1" aria-hidden="true"></i>
                                <?= htmlspecialchars((string)($checks['database']['error'] ?? 'Connection unavailable')) ?>
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
                                    <h5 class="card-title mb-0">Storage & Filesystem</h5>
                                    <small class="text-muted">Application Write Subsystem</small>
                                </div>
                            </div>
                            <?php if ($checks['storage']['logs_writable']): ?>
                                <span class="badge bg-success">Operational</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Degraded</span>
                            <?php endif; ?>
                        </div>

                        <ul class="list-group list-group-flush mb-0">
                            <?php if ($canViewDetails): ?>
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
                            <?php else: ?>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="metric-label">Log Write State</span>
                                    <span class="metric-value text-success">
                                        <?= $checks['storage']['logs_writable'] ? 'Ready' : 'Unavailable' ?>
                                        <i class="fa-solid <?= $checks['storage']['logs_writable'] ? 'fa-check text-success' : 'fa-xmark text-danger' ?> ms-1" aria-hidden="true"></i>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="metric-label">Temp Subsystem</span>
                                    <span class="metric-value text-success">
                                        <?= $checks['storage']['temp_writable'] ? 'Ready' : 'Unavailable' ?>
                                        <i class="fa-solid <?= $checks['storage']['temp_writable'] ? 'fa-check text-success' : 'fa-xmark text-danger' ?> ms-1" aria-hidden="true"></i>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="metric-label">Storage Paths</span>
                                    <span class="metric-value text-muted small"><i class="fa-solid fa-lock me-1" aria-hidden="true"></i>Protected</span>
                                </li>
                            <?php endif; ?>
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
                                    <small class="text-muted">Application Engine State</small>
                                </div>
                            </div>
                            <span class="badge bg-success">
                                <?= $canViewDetails ? 'v' . htmlspecialchars((string)PHP_VERSION) : 'Operational' ?>
                            </span>
                        </div>

                        <ul class="list-group list-group-flush mb-0">
                            <?php if ($canViewDetails): ?>
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
                            <?php else: ?>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="metric-label">Engine State</span>
                                    <span class="metric-value text-success"><i class="fa-solid fa-circle-check me-1" aria-hidden="true"></i>Running</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="metric-label">Core Extensions</span>
                                    <span class="metric-value text-success">
                                        <?= $phpOk ? 'All Loaded' : 'Missing modules' ?>
                                        <i class="fa-solid <?= $phpOk ? 'fa-check text-success' : 'fa-triangle-exclamation text-warning' ?> ms-1" aria-hidden="true"></i>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="metric-label">Engine Config</span>
                                    <span class="metric-value text-muted small"><i class="fa-solid fa-lock me-1" aria-hidden="true"></i>Protected</span>
                                </li>
                            <?php endif; ?>
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
                                    <h5 class="card-title mb-0">Server & Environment</h5>
                                    <small class="text-muted">Host & System Telemetry</small>
                                </div>
                            </div>
                            <span class="badge <?= $isDev ? 'bg-info text-dark' : 'bg-primary' ?>">
                                <?= $canViewDetails ? htmlspecialchars(ucfirst((string)$checks['server']['environment'])) : 'Online' ?>
                            </span>
                        </div>

                        <ul class="list-group list-group-flush mb-0">
                            <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="metric-label">Server Time (EST/EDT)</span>
                                <span class="metric-value"><?= htmlspecialchars((string)$checks['server']['time']) ?></span>
                            </li>
                            <?php if ($canViewDetails): ?>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="metric-label">Hostname</span>
                                    <span class="metric-value font-monospace small"><?= htmlspecialchars((string)$checks['server']['hostname']) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="metric-label">Platform</span>
                                    <span class="metric-value"><?= htmlspecialchars((string)PHP_OS) ?> (<?= htmlspecialchars((string)PHP_SAPI) ?>)</span>
                                </li>
                            <?php else: ?>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="metric-label">Protocol Security</span>
                                    <span class="metric-value">
                                        <?= $checks['server']['https'] ? '<span class="text-success"><i class="fa-solid fa-shield-halved me-1" aria-hidden="true"></i>HTTPS (Encrypted)</span>' : '<span class="text-warning">HTTP (Standard)</span>' ?>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                    <span class="metric-label">Host & Platform OS</span>
                                    <span class="metric-value text-muted small"><i class="fa-solid fa-lock me-1" aria-hidden="true"></i>Protected</span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Privacy & Security Notice Banner -->
        <?php if (!$canViewDetails): ?>
            <div class="alert alert-light border small text-muted d-flex flex-wrap align-items-center justify-content-between p-3 mt-4 shadow-sm" role="status">
                <div class="d-flex align-items-center mb-2 mb-md-0">
                    <i class="fa-solid fa-shield-halved text-primary fs-5 me-2" aria-hidden="true"></i>
                    <div>
                        <strong>Privacy Protection:</strong> Sensitive infrastructure parameters (database hostnames, database names, PHP version strings, internal paths, and server telemetry) are masked for public security.
                    </div>
                </div>
                <div>
                    <a href="/members/index.php" class="btn btn-outline-primary btn-sm text-nowrap">
                        <i class="fa-solid fa-right-to-bracket me-1" aria-hidden="true"></i>Member Login
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info border small d-flex align-items-center p-3 mt-4 shadow-sm" role="status">
                <i class="fa-solid fa-circle-info text-info fs-5 me-2" aria-hidden="true"></i>
                <div>
                    <strong>Authorized Diagnostic View:</strong> Full infrastructure telemetry is active
                    <?= $isAdmin ? '(Administrator)' : ($isAuthenticated ? '(Member Session)' : '(Dev / Authorized Key)') ?>.
                </div>
            </div>
        <?php endif; ?>

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

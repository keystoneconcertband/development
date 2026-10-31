<?php
/**
 * KcbLogger
 *
 * Modern, PSR-3 compliant logging engine for the Keystone Concert Band application.
 * Supports Monolog when available, with a robust native daily rotating file fallback.
 * Uses atomic append locks (LOCK_EX) and enforces automatic log retention cleanup.
 */

if (!class_exists('Psr\Log\LogLevel')) {
    class LogLevel
    {
        const EMERGENCY = 'emergency';
        const ALERT     = 'alert';
        const CRITICAL  = 'critical';
        const ERROR     = 'error';
        const WARNING   = 'warning';
        const NOTICE    = 'notice';
        const INFO      = 'info';
        const DEBUG     = 'debug';
    }
}

class KcbLogger
{
    private static $instance = null;

    /** @var string Absolute directory path for log files */
    private $logDir;

    /** @var int Number of days to retain daily log files */
    private $retentionDays = 30;

    /** @var object|null Monolog instance if available */
    private $monolog = null;

    /** @var array Severity map for level normalization */
    private static $levelWeights = [
        'debug'     => 100,
        'info'      => 200,
        'notice'    => 250,
        'warning'   => 300,
        'error'     => 400,
        'critical'  => 500,
        'alert'     => 550,
        'emergency' => 600,
    ];

    /**
     * Private constructor for singleton
     */
    private function __construct()
    {
        $baseDir = !empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : dirname(__DIR__, 2);
        $this->logDir = rtrim($baseDir, '/') . '/logs/';

        if (!is_dir($this->logDir)) {
            @mkdir($this->logDir, 0755, true);
        }

        $this->initMonologIfAvailable();

        // 1 in 100 requests runs retention purge to prune old files
        if (mt_rand(1, 100) === 1) {
            $this->purgeOldLogs();
        }
    }

    /**
     * Singleton accessor
     *
     * @return KcbLogger
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Attempt to initialize Monolog if installed and available
     */
    private function initMonologIfAvailable()
    {
        $autoloadPath = dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
        }

        if (class_exists('Monolog\Logger') && class_exists('Monolog\Handler\RotatingFileHandler')) {
            try {
                $logger = new \Monolog\Logger('kcb');
                $logFile = $this->logDir . 'kcb.log';

                $handler = new \Monolog\Handler\RotatingFileHandler(
                    $logFile,
                    $this->retentionDays,
                    \Monolog\Logger::DEBUG,
                    true,
                    0644,
                    true
                );

                // Use a clean, machine-parseable line formatter
                $dateFormat = 'Y-m-d H:i:s';
                $output = "[%datetime%] [%channel%.%level_name%] %message% %context% %extra%\n";
                $formatter = new \Monolog\Formatter\LineFormatter($output, $dateFormat, true, true);
                $handler->setFormatter($formatter);

                $logger->pushHandler($handler);

                // Add request metadata processor
                $logger->pushProcessor(function ($record) {
                    $extra = [
                        'ip'   => $this->getClientIp(),
                        'uri'  => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'CLI',
                        'user' => isset($_SESSION['email']) ? $_SESSION['email'] : 'guest',
                    ];
                    if (is_array($record)) {
                        $record['extra'] = array_merge($record['extra'] ?? [], $extra);
                        return $record;
                    } elseif (is_object($record) && method_exists($record, 'with')) {
                        return $record->with(extra: array_merge($record->extra, $extra));
                    }
                    return $record;
                });

                $this->monolog = $logger;
            } catch (\Throwable $t) {
                // Monolog initialization failed; fallback to native logger
                $this->monolog = null;
            }
        }
    }

    /**
     * Core log dispatch method
     *
     * @param string $level PSR-3 log level
     * @param string|\Throwable $message
     * @param array $context
     */
    public function log($level, $message, array $context = [])
    {
        $level = strtolower((string)$level);
        if (!isset(self::$levelWeights[$level])) {
            $level = 'error';
        }

        // If message is a Throwable, extract details into context
        if ($message instanceof \Throwable) {
            $context['exception'] = [
                'class'   => get_class($message),
                'message' => $message->getMessage(),
                'file'    => $message->getFile(),
                'line'    => $message->getLine(),
                'trace'   => $message->getTraceAsString(),
            ];
            $message = $message->getMessage();
        } elseif (isset($context['exception']) && $context['exception'] instanceof \Throwable) {
            $ex = $context['exception'];
            $context['exception'] = [
                'class'   => get_class($ex),
                'message' => $ex->getMessage(),
                'file'    => $ex->getFile(),
                'line'    => $ex->getLine(),
                'trace'   => $ex->getTraceAsString(),
            ];
        }

        // Use Monolog if available
        if ($this->monolog !== null) {
            try {
                $this->monolog->log($level, (string)$message, $context);
                return;
            } catch (\Throwable $t) {
                // If Monolog fails, write via native fallback
            }
        }

        $this->writeNative($level, (string)$message, $context);
    }

    /**
     * Native daily rotating file write implementation
     * Thread-safe using atomic file append with LOCK_EX
     */
    private function writeNative($level, $message, array $context = [])
    {
        $date = new \DateTime('now', new \DateTimeZone('America/New_York'));
        $filePath = $this->logDir . 'kcb-' . $date->format('Y-m-d') . '.log';

        $timestamp = $date->format('Y-m-d H:i:s');
        $levelUpper = strtoupper($level);

        $extra = [
            'ip'   => $this->getClientIp(),
            'uri'  => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'CLI',
            'user' => isset($_SESSION['email']) ? $_SESSION['email'] : 'guest',
        ];

        $contextJson = !empty($context) ? json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '{}';
        $extraJson = json_encode($extra, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Standard format matching Monolog's LineFormatter:
        // [YYYY-MM-DD HH:MM:SS] [kcb.LEVEL] Message {context} {extra}
        $line = sprintf(
            "[%s] [kcb.%s] %s %s %s\n",
            $timestamp,
            $levelUpper,
            str_replace(["\r", "\n"], ' ', $message),
            $contextJson,
            $extraJson
        );

        @file_put_contents($filePath, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Convenience helpers
     */
    public function debug($message, array $context = [])    { $this->log('debug', $message, $context); }
    public function info($message, array $context = [])     { $this->log('info', $message, $context); }
    public function notice($message, array $context = [])   { $this->log('notice', $message, $context); }
    public function warning($message, array $context = [])  { $this->log('warning', $message, $context); }
    public function error($message, array $context = [])    { $this->log('error', $message, $context); }
    public function critical($message, array $context = []) { $this->log('critical', $message, $context); }
    public function alert($message, array $context = [])    { $this->log('alert', $message, $context); }
    public function emergency($message, array $context = []) { $this->log('emergency', $message, $context); }

    /**
     * Backward-compatible write method
     *
     * @param string $message
     */
    public function write($message)
    {
        $this->error($message);
    }

    /**
     * Clean up log files older than retention days
     */
    public function purgeOldLogs()
    {
        if (!is_dir($this->logDir)) {
            return;
        }

        $cutoff = time() - ($this->retentionDays * 86400);
        $files = glob($this->logDir . 'kcb-*.log');
        if (!$files) {
            $files = [];
        }

        // Also check legacy .txt log files
        $legacyFiles = glob($this->logDir . '20*.txt');
        if ($legacyFiles) {
            $files = array_merge($files, $legacyFiles);
        }

        // Clean up daily lock and rate limit marker files
        $lockFiles = glob($this->logDir . '.sent_*.lock');
        if ($lockFiles) {
            $files = array_merge($files, $lockFiles);
        }
        $rateFiles = glob($this->logDir . '.rate_*.json');
        if ($rateFiles) {
            $files = array_merge($files, $rateFiles);
        }

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }

    /**
     * Resolve client IP safely
     *
     * @return string
     */
    private function getClientIp()
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return '127.0.0.1';
    }

    /**
     * Return log directory path
     *
     * @return string
     */
    public function getLogDir()
    {
        return $this->logDir;
    }

    /**
     * Set retention days
     *
     * @param int $days
     */
    public function setRetentionDays($days)
    {
        $this->retentionDays = max(1, (int)$days);
    }
}


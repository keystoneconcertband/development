<?php
/**
 * DailySummaryService
 *
 * Scans daily application logs for issues (WARNING, ERROR, CRITICAL, ALERT, EMERGENCY),
 * categorizes detected incidents, generates a responsive HTML digest, and emails it
 * to administrators when issues occur.
 */

require_once __DIR__ . '/kcbBase.class.php';
require_once __DIR__ . '/kcbLogger.class.php';

class DailySummaryService
{
    private $kcb;
    private $logger;

    public function __construct()
    {
        $this->kcb = new KcbBase(false);
        $this->logger = KcbLogger::getInstance();
    }

    /**
     * Run daily log summary for a target date
     *
     * @param string|null $targetDate YYYY-MM-DD format (defaults to today in Eastern Time)
     * @param bool $forceSend Always send email even if 0 issues
     * @param bool $dryRun Output summary without sending email
     * @return array Result metrics and execution status
     */
    public function run($targetDate = null, $forceSend = false, $dryRun = false)
    {
        $tz = new \DateTimeZone('America/New_York');
        if (empty($targetDate)) {
            $now = new \DateTime('now', $tz);
            $targetDate = $now->format('Y-m-d');
        }

        $logDir = $this->logger->getLogDir();
        $logFile = $logDir . 'kcb-' . $targetDate . '.log';

        // Also check alternative naming or legacy file if kcb-YYYY-MM-DD.log is not found
        if (!file_exists($logFile)) {
            $legacyFile = $logDir . $targetDate . '.txt';
            if (file_exists($legacyFile)) {
                $logFile = $legacyFile;
            }
        }

        $issues = [];
        $stats = [
            'total'     => 0,
            'debug'     => 0,
            'info'      => 0,
            'notice'    => 0,
            'warnings'  => 0,
            'errors'    => 0,
            'critical'  => 0,
        ];

        if (file_exists($logFile)) {
            $parsed = $this->parseLogFile($logFile);
            $issues = $parsed['issues'];
            $stats = $parsed['stats'];
        }

        $issueCount = count($issues);

        // Retrieve site login statistics for the target date
        $loginStats = $this->getLoginStatsForDate($targetDate);

        $recipient = getenv('ALERT_EMAIL_RECIPIENT');
        if (empty($recipient)) {
            $recipient = 'webmaster@keystoneconcertband.com';
        }

        // Check if daily summary email is disabled via environment variable (e.g. in dev environment)
        if (!$this->isEmailEnabled() && !$forceSend && !$dryRun) {
            return [
                'status'       => 'disabled',
                'date'         => $targetDate,
                'recipient'    => $recipient,
                'subject'      => $subject ?? "KCB Daily Report: $targetDate",
                'issue_count'  => $issueCount,
                'login_stats'  => $loginStats,
                'stats'        => $stats,
                'email_sent'   => false,
                'message'      => "Daily summary email is disabled via environment variable (SEND_DAILY_SUMMARY_EMAIL=false).",
            ];
        }

        // Anti-spam protection: only send 1 summary email per day unless forceSend is explicitly true
        $sentMarkerFile = $logDir . '.sent_' . $targetDate . '.lock';
        if (file_exists($sentMarkerFile) && !$forceSend && !$dryRun) {
            $sentTime = @file_get_contents($sentMarkerFile);
            return [
                'status'       => 'already_sent',
                'date'         => $targetDate,
                'recipient'    => $recipient,
                'issue_count'  => $issueCount,
                'login_stats'  => $loginStats,
                'stats'        => $stats,
                'sent_at'      => trim((string)$sentTime),
                'message'      => "Daily summary email for $targetDate has already been sent (at " . trim((string)$sentTime) . "). Email skipped to prevent duplicate alerts.",
            ];
        }

        $htmlBody = $this->renderHtmlReport($targetDate, $issues, $stats, $loginStats, $recipient);

        $loginText = $loginStats['successful_logins'] . ' login' . ($loginStats['successful_logins'] === 1 ? '' : 's');
        if ($issueCount > 0) {
            $subject = sprintf(
                "[ALERT] KCB Daily Report: %d issue%s, %s on %s",
                $issueCount,
                $issueCount === 1 ? "" : "s",
                $loginText,
                $targetDate
            );
        } else {
            $subject = sprintf(
                "[OK] KCB Daily Report: 0 issues, %s on %s",
                $loginText,
                $targetDate
            );
        }

        if ($dryRun) {
            return [
                'status'       => 'dry_run',
                'date'         => $targetDate,
                'recipient'    => $recipient,
                'subject'      => $subject,
                'issue_count'  => $issueCount,
                'login_stats'  => $loginStats,
                'stats'        => $stats,
                'issues'       => $issues,
            ];
        }

        $emailSent = $this->kcb->sendEmail($recipient, $htmlBody, $subject, true);

        if ($emailSent) {
            $now = new \DateTime('now', new \DateTimeZone('America/New_York'));
            @file_put_contents($sentMarkerFile, $now->format('Y-m-d H:i:s'));
        }

        return [
            'status'       => $emailSent ? 'sent' : 'failed',
            'date'         => $targetDate,
            'recipient'    => $recipient,
            'subject'      => $subject,
            'issue_count'  => $issueCount,
            'login_stats'  => $loginStats,
            'email_sent'   => $emailSent,
            'stats'        => $stats,
        ];
    }

    /**
     * Rate limiter to prevent hammering or flooding
     *
     * @param string $identifier IP address or caller ID
     * @param int $maxPerHour Max allowed requests within a 1-hour window
     * @return bool True if allowed, false if rate limit exceeded
     */
    public function checkRateLimit($identifier, $maxPerHour = 10)
    {
        $logDir = $this->logger->getLogDir();
        $safeId = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $identifier);
        $rateFile = $logDir . '.rate_' . $safeId . '.json';
        $now = time();
        $window = 3600; // 1 hour

        $records = [];
        if (file_exists($rateFile)) {
            $data = json_decode(@file_get_contents($rateFile), true);
            if (is_array($data)) {
                $records = array_filter($data, function ($t) use ($now, $window) {
                    return is_int($t) && ($now - $t) < $window;
                });
            }
        }

        if (count($records) >= $maxPerHour) {
            return false;
        }

        $records[] = $now;
        @file_put_contents($rateFile, json_encode(array_values($records)));
        return true;
    }

    /**
     * Check if daily summary emails are enabled in the environment
     * Returns false if SEND_DAILY_SUMMARY_EMAIL or ENABLE_DAILY_SUMMARY_EMAIL is false/0/off/no/disabled
     * or if DISABLE_DAILY_SUMMARY_EMAIL is true.
     *
     * @return bool
     */
    public function isEmailEnabled()
    {
        $val = getenv('SEND_DAILY_SUMMARY_EMAIL');
        if ($val === false || $val === null || $val === '') {
            $val = getenv('APPSETTING_SEND_DAILY_SUMMARY_EMAIL');
        }
        if ($val === false || $val === null || $val === '') {
            $val = getenv('ENABLE_DAILY_SUMMARY_EMAIL');
        }

        if ($val !== false && $val !== null && $val !== '') {
            return !in_array(strtolower(trim((string)$val)), ['false', '0', 'off', 'no', 'disabled'], true);
        }

        if (strtolower((string)getenv('DISABLE_DAILY_SUMMARY_EMAIL')) === 'true') {
            return false;
        }

        return true;
    }

    /**
     * Retrieve login statistics for the target date
     *
     * @param string $date YYYY-MM-DD
     * @return array
     */
    public function getLoginStatsForDate($date)
    {
        $stats = [
            'total_attempts'    => 0,
            'successful_logins' => 0,
            'unique_users'      => 0,
            'failed_attempts'   => 0,
            'recent_logins'     => [],
        ];

        try {
            require_once __DIR__ . '/member.db.class.php';
            $memberDb = new MemberDB();
            $records = $memberDb->getDailyLoginStats($date);
            if (is_array($records)) {
                $uniqueSuccessful = [];
                foreach ($records as $row) {
                    $stats['total_attempts']++;
                    $isValid = ($row['valid'] === 'valid' || $row['valid'] === 1 || $row['valid'] === '1');
                    if ($isValid) {
                        $stats['successful_logins']++;
                        $uniqueSuccessful[$row['logonValue']] = true;
                    } else {
                        $stats['failed_attempts']++;
                    }

                    if (count($stats['recent_logins']) < 15) {
                        $stats['recent_logins'][] = [
                            'email'  => $row['logonValue'],
                            'status' => $isValid ? 'Success' : ($row['valid'] ?: 'Failed'),
                            'time'   => $row['estbd_dt_tm'],
                        ];
                    }
                }
                $stats['unique_users'] = count($uniqueSuccessful);
                return $stats;
            }
        } catch (\Throwable $t) {
            // Database might be unreachable or offline; extract from daily log
        }

        return $this->extractLoginsFromLogFile($date);
    }

    /**
     * Fallback login extraction from daily log file
     *
     * @param string $date YYYY-MM-DD
     * @return array
     */
    private function extractLoginsFromLogFile($date)
    {
        $stats = [
            'total_attempts'    => 0,
            'successful_logins' => 0,
            'unique_users'      => 0,
            'failed_attempts'   => 0,
            'recent_logins'     => [],
        ];

        $logFile = $this->logger->getLogDir() . 'kcb-' . $date . '.log';
        if (!file_exists($logFile)) {
            return $stats;
        }

        $lines = @file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return $stats;
        }

        $uniqueSuccessful = [];
        foreach ($lines as $line) {
            $isSuccess = (stripos($line, 'logged in') !== false || stripos($line, 'authenticated via') !== false);
            $isFailed = (stripos($line, 'Invalid auth code') !== false || stripos($line, 'auth verification failed') !== false);

            if ($isSuccess || $isFailed) {
                $stats['total_attempts']++;
                $email = 'unknown';
                if (preg_match('/\{.*?"email"\s*:\s*"([^"]+)".*?\}/', $line, $m)) {
                    $email = $m[1];
                } elseif (preg_match('/Email:\s*([^\s\)]+)/i', $line, $m)) {
                    $email = $m[1];
                }

                $time = 'Unknown';
                if (preg_match('/^\[(.*?)\]/', $line, $tm)) {
                    $time = $tm[1];
                }

                if ($isSuccess) {
                    $stats['successful_logins']++;
                    $uniqueSuccessful[$email] = true;
                } else {
                    $stats['failed_attempts']++;
                }

                if (count($stats['recent_logins']) < 15) {
                    $stats['recent_logins'][] = [
                        'email'  => $email,
                        'status' => $isSuccess ? 'Success' : 'Failed',
                        'time'   => $time,
                    ];
                }
            }
        }
        $stats['unique_users'] = count($uniqueSuccessful);
        return $stats;
    }

    /**
     * Parse log file into structured entries and filter for issues
     */
    private function parseLogFile($filePath)
    {
        $handle = @fopen($filePath, 'r');
        if (!$handle) {
            return ['issues' => [], 'stats' => ['total' => 0, 'warnings' => 0, 'errors' => 0, 'critical' => 0]];
        }

        $stats = [
            'total'     => 0,
            'debug'     => 0,
            'info'      => 0,
            'notice'    => 0,
            'warnings'  => 0,
            'errors'    => 0,
            'critical'  => 0,
        ];

        $issueLevels = ['WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];
        $issues = [];

        // Match standard format: [YYYY-MM-DD HH:MM:SS] [channel.LEVEL] Message {context} {extra}
        $pattern = '/^\[(?P<timestamp>[^\]]+)\] \[(?P<channel>[^\.]+)\.(?P<level>[^\]]+)\] (?P<rest>.*)$/';

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $stats['total']++;

            if (preg_match($pattern, $line, $matches)) {
                $timestamp = $matches['timestamp'];
                $level = strtoupper($matches['level']);
                $rest = $matches['rest'];

                $context = [];
                $extra = [];
                $message = $rest;

                // Extract context and extra JSON if present at tail
                if (preg_match('/^(?P<msg>.*?)\s*(?P<ctx>\{.*?\})\s*(?P<ext>\{.*?\})$/', $rest, $jsonMatches)) {
                    $message = trim($jsonMatches['msg']);
                    $context = json_decode($jsonMatches['ctx'], true) ?: [];
                    $extra = json_decode($jsonMatches['ext'], true) ?: [];
                }

                $this->incrementLevelStat($stats, $level);

                if (in_array($level, $issueLevels, true)) {
                    $category = $this->categorizeIssue($message, $context);
                    $issues[] = [
                        'timestamp' => $timestamp,
                        'level'     => $level,
                        'category'  => $category,
                        'message'   => $message,
                        'context'   => $context,
                        'extra'     => $extra,
                    ];
                }
            } else {
                // Fallback for non-standard / legacy lines
                if (stripos($line, 'SQLSTATE') !== false || stripos($line, 'Error') !== false || stripos($line, 'Exception') !== false) {
                    $stats['errors']++;
                    $issues[] = [
                        'timestamp' => 'Unknown',
                        'level'     => 'ERROR',
                        'category'  => 'Database / Runtime',
                        'message'   => $line,
                        'context'   => [],
                        'extra'     => [],
                    ];
                }
            }
        }

        fclose($handle);
        return ['issues' => $issues, 'stats' => $stats];
    }

    private function incrementLevelStat(&$stats, $level)
    {
        switch ($level) {
            case 'WARNING':
                $stats['warnings']++;
                break;
            case 'ERROR':
                $stats['errors']++;
                break;
            case 'CRITICAL':
            case 'ALERT':
            case 'EMERGENCY':
                $stats['critical']++;
                break;
            case 'INFO':
                $stats['info']++;
                break;
            case 'NOTICE':
                $stats['notice']++;
                break;
            case 'DEBUG':
                $stats['debug']++;
                break;
        }
    }

    private function categorizeIssue($message, array $context)
    {
        $m = strtolower($message);
        if (strpos($m, 'auth') !== false || strpos($m, 'login') !== false || strpos($m, 'cookie') !== false || strpos($m, 'access denied') !== false) {
            return 'Authentication & Security';
        }
        if (strpos($m, 'spam') !== false || strpos($m, 'honeypot') !== false || strpos($m, 'bot') !== false || strpos($m, 'csrf') !== false) {
            return 'Spam & Abuse Protection';
        }
        if (strpos($m, 'sql') !== false || strpos($m, 'pdo') !== false || strpos($m, 'database') !== false || strpos($m, 'query') !== false) {
            return 'Database';
        }
        if (strpos($m, 'mail') !== false || strpos($m, 'smtp') !== false || strpos($m, 'brevo') !== false) {
            return 'Email Delivery';
        }
        if (strpos($m, 'uncaught exception') !== false || strpos($m, 'fatal') !== false || strpos($m, 'php error') !== false) {
            return 'Unhandled Exceptions';
        }
        return 'General Application Issue';
    }

    /**
     * Render a responsive HTML email report
     */
    private function renderHtmlReport($date, array $issues, array $stats, array $loginStats = [], $recipient = 'webmaster@keystoneconcertband.com')
    {
        $issueCount = count($issues);
        $headerColor = $issueCount > 0 ? '#b91c1c' : '#15803d';
        $badgeText = $issueCount > 0 ? "$issueCount Issues Detected" : "All Systems Healthy";

        $rowsHtml = '';
        foreach ($issues as $idx => $issue) {
            $badgeBg = '#fef2f2';
            $badgeTextCol = '#991b1b';
            if ($issue['level'] === 'WARNING') {
                $badgeBg = '#fffbeb';
                $badgeTextCol = '#92400e';
            } elseif (in_array($issue['level'], ['CRITICAL', 'ALERT', 'EMERGENCY'], true)) {
                $badgeBg = '#450a0a';
                $badgeTextCol = '#fecaca';
            }

            $contextDetails = '';
            if (!empty($issue['context'])) {
                if (isset($issue['context']['exception'])) {
                    $ex = $issue['context']['exception'];
                    $contextDetails .= sprintf(
                        "<div style='margin-top:6px; font-family:monospace; font-size:12px; background:#f1f5f9; padding:6px; border-radius:4px;'><strong>%s:</strong> %s in %s:%s</div>",
                        htmlspecialchars($ex['class'] ?? 'Exception'),
                        htmlspecialchars($ex['message'] ?? ''),
                        htmlspecialchars(basename($ex['file'] ?? '')),
                        htmlspecialchars((string)($ex['line'] ?? ''))
                    );
                } else {
                    $json = json_encode($issue['context'], JSON_UNESCAPED_SLASHES);
                    $contextDetails .= "<div style='margin-top:4px; font-family:monospace; font-size:12px; color:#475569;'>Context: " . htmlspecialchars($json) . "</div>";
                }
            }

            $callerInfo = '';
            if (!empty($issue['extra'])) {
                $ip = $issue['extra']['ip'] ?? '';
                $uri = $issue['extra']['uri'] ?? '';
                $user = $issue['extra']['user'] ?? '';
                $callerInfo = "<div style='margin-top:4px; font-size:11px; color:#64748b;'>IP: " . htmlspecialchars($ip) . " | URI: " . htmlspecialchars($uri) . " | User: " . htmlspecialchars($user) . "</div>";
            }

            $rowsHtml .= sprintf(
                "<tr style='border-bottom: 1px solid #e2e8f0;'>
                    <td style='padding: 10px; font-family: monospace; font-size: 12px; white-space: nowrap; color: #334155;'>%s</td>
                    <td style='padding: 10px;'><span style='background:%s; color:%s; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:bold;'>%s</span></td>
                    <td style='padding: 10px; font-size: 12px; font-weight:600; color:#1e293b;'>%s</td>
                    <td style='padding: 10px; font-size: 13px; color:#0f172a;'>
                        <div>%s</div>
                        %s
                        %s
                    </td>
                </tr>",
                htmlspecialchars($issue['timestamp']),
                $badgeBg,
                $badgeTextCol,
                htmlspecialchars($issue['level']),
                htmlspecialchars($issue['category']),
                htmlspecialchars($issue['message']),
                $contextDetails,
                $callerInfo
            );
        }

        $loginRowsHtml = '';
        if (!empty($loginStats['recent_logins'])) {
            foreach ($loginStats['recent_logins'] as $login) {
                $isSuccess = ($login['status'] === 'Success' || $login['status'] === 'valid');
                $loginBadgeBg = $isSuccess ? '#f0fdf4' : '#fef2f2';
                $loginBadgeCol = $isSuccess ? '#16a34a' : '#b91c1c';
                $loginRowsHtml .= sprintf(
                    "<tr style='border-bottom: 1px solid #e2e8f0;'>
                        <td style='padding: 8px 10px; font-family: monospace; font-size: 12px; color: #334155; white-space: nowrap;'>%s</td>
                        <td style='padding: 8px 10px; font-size: 13px; color: #0f172a;'>%s</td>
                        <td style='padding: 8px 10px;'><span style='background:%s; color:%s; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:bold;'>%s</span></td>
                    </tr>",
                    htmlspecialchars($login['time'] ?? 'Unknown'),
                    htmlspecialchars($login['email'] ?? 'Unknown'),
                    $loginBadgeBg,
                    $loginBadgeCol,
                    htmlspecialchars($login['status'] ?? 'Unknown')
                );
            }
        }

        $errorCriticalCount = $stats['errors'] + $stats['critical'];
        $loginsCount = $loginStats['successful_logins'] ?? 0;
        $uniqueUsersCount = $loginStats['unique_users'] ?? 0;
        $failedLoginsCount = $loginStats['failed_attempts'] ?? 0;

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Keystone Concert Band - Daily Report</title>
        </head>
        <body style='font-family:-apple-system,BlinkMacSystemFont,\"Segoe UI\",Roboto,Helvetica,Arial,sans-serif; background-color:#f8fafc; margin:0; padding:20px; color:#0f172a;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='max-width:850px; margin:0 auto; background:#ffffff; border-radius:8px; overflow:hidden; border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.05);'>
                <!-- Header -->
                <tr>
                    <td style='background:$headerColor; padding:24px; color:#ffffff;'>
                        <h2 style='margin:0 0 6px 0; font-size:22px; font-weight:700;'>Keystone Concert Band</h2>
                        <div style='font-size:15px; opacity:0.95;'>Daily Status & System Report — $date (" . ($issueCount > 0 ? "$issueCount Issues Detected" : "All Systems Healthy") . ")</div>
                    </td>
                </tr>
                <!-- Metric Summary Cards -->
                <tr>
                    <td style='padding:20px;'>
                        <table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:20px;'>
                            <tr>
                                <td width='25%' style='padding:6px;'>
                                    <div style='background:#f1f5f9; padding:12px; border-radius:6px; text-align:center;'>
                                        <div style='font-size:11px; text-transform:uppercase; color:#64748b; font-weight:600;'>Total Events</div>
                                        <div style='font-size:22px; font-weight:bold; color:#0f172a;'>{$stats['total']}</div>
                                    </div>
                                </td>
                                <td width='25%' style='padding:6px;'>
                                    <div style='background:#f0fdf4; padding:12px; border-radius:6px; text-align:center;'>
                                        <div style='font-size:11px; text-transform:uppercase; color:#15803d; font-weight:600;'>Member Logins</div>
                                        <div style='font-size:22px; font-weight:bold; color:#16a34a;'>$loginsCount</div>
                                        <div style='font-size:10px; color:#64748b;'>$uniqueUsersCount unique</div>
                                    </div>
                                </td>
                                <td width='25%' style='padding:6px;'>
                                    <div style='background:#fffbeb; padding:12px; border-radius:6px; text-align:center;'>
                                        <div style='font-size:11px; text-transform:uppercase; color:#92400e; font-weight:600;'>Warnings</div>
                                        <div style='font-size:22px; font-weight:bold; color:#b45309;'>{$stats['warnings']}</div>
                                    </div>
                                </td>
                                <td width='25%' style='padding:6px;'>
                                    <div style='background:#fef2f2; padding:12px; border-radius:6px; text-align:center;'>
                                        <div style='font-size:11px; text-transform:uppercase; color:#991b1b; font-weight:600;'>Errors</div>
                                        <div style='font-size:22px; font-weight:bold; color:#dc2626;'>$errorCriticalCount</div>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <!-- Member Logins Section -->
                        <h3 style='margin:20px 0 10px 0; font-size:16px; color:#1e293b; border-bottom:2px solid #e2e8f0; padding-bottom:6px;'>
                            Member Site Logins ($loginsCount)
                        </h3>";

        if ($loginsCount === 0 && $failedLoginsCount === 0) {
            $html .= "<p style='color:#64748b; padding:12px; background:#f8fafc; border-radius:6px; font-size:13px; margin:0 0 20px 0;'>No member logins were recorded for $date.</p>";
        } else {
            $html .= "
                        <div style='margin-bottom:10px; font-size:13px; color:#334155;'>
                            <strong>$loginsCount</strong> successful login(s) from <strong>$uniqueUsersCount</strong> unique member(s)" . ($failedLoginsCount > 0 ? " — <span style='color:#dc2626; font-weight:600;'>$failedLoginsCount failed attempt(s)</span>" : "") . "
                        </div>
                        <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse; margin-bottom:24px;'>
                            <thead>
                                <tr style='background:#f8fafc; border-bottom:2px solid #cbd5e1; text-align:left;'>
                                    <th style='padding:8px 10px; font-size:12px; color:#475569;'>Time (EST)</th>
                                    <th style='padding:8px 10px; font-size:12px; color:#475569;'>Member Email</th>
                                    <th style='padding:8px 10px; font-size:12px; color:#475569;'>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                $loginRowsHtml
                            </tbody>
                        </table>";
        }

        $html .= "
                        <!-- Issue Table -->
                        <h3 style='margin:20px 0 12px 0; font-size:16px; color:#1e293b; border-bottom:2px solid #e2e8f0; padding-bottom:6px;'>
                            Detected Issues ({$issueCount})
                        </h3>";

        if ($issueCount === 0) {
            $html .= "<p style='color:#16a34a; font-weight:600; padding:12px; background:#f0fdf4; border-radius:6px; margin:0 0 10px 0;'>No errors or warnings were logged for $date.</p>";
        } else {
            $html .= "
                        <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;'>
                            <thead>
                                <tr style='background:#f8fafc; border-bottom:2px solid #cbd5e1; text-align:left;'>
                                    <th style='padding:8px 10px; font-size:12px; color:#475569;'>Time (EST)</th>
                                    <th style='padding:8px 10px; font-size:12px; color:#475569;'>Level</th>
                                    <th style='padding:8px 10px; font-size:12px; color:#475569;'>Category</th>
                                    <th style='padding:8px 10px; font-size:12px; color:#475569;'>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                $rowsHtml
                            </tbody>
                        </table>";
        }

        $html .= "
                    </td>
                </tr>
                <!-- Footer -->
                <tr>
                    <td style='background:#f8fafc; padding:16px 20px; font-size:12px; color:#64748b; border-top:1px solid #e2e8f0; text-align:center;'>
                        This automated summary was generated by the Keystone Concert Band monitoring system.<br>
                        Log directory: <code>logs/</code> | Recipient: <code>$recipient</code>
                    </td>
                </tr>
            </table>
        </body>
        </html>";

        return $html;
    }
}


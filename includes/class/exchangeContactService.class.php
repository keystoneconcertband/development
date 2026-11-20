<?php
/**
 * ExchangeContactService
 *
 * Synchronizes active member email addresses with Microsoft 365 / Exchange Online
 * Mail Contacts (visible at https://admin.cloud.microsoft/?#/Contact) by posting
 * signed/configured JSON payloads to an Azure Automation Runbook webhook (or
 * PowerShell Azure Function).
 */

require_once __DIR__ . '/kcbBase.class.php';
require_once __DIR__ . '/member.db.class.php';
require_once __DIR__ . '/kcbLogger.class.php';

class ExchangeContactService
{
    public const DEFAULT_CONNECT_TIMEOUT_SECONDS = 3;
    public const DEFAULT_TIMEOUT_SECONDS = 6;

    /** @var KcbBase */
    private $kcb;

    /** @var MemberDB */
    private $db;

    /** @var KcbLogger */
    private $logger;

    public function __construct(?KcbBase $kcb = null, ?MemberDB $db = null)
    {
        $this->kcb = $kcb ?: new KcbBase(false);
        $this->db = $db ?: new MemberDB();
        $this->logger = KcbLogger::getInstance();
    }

    /**
     * Returns true when Exchange contact sync is enabled and a valid webhook URL is configured.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isSyncFlagEnabled() && $this->getWebhookUrl() !== null;
    }

    /**
     * Checks whether a member row represents an active (non-pending, non-disabled) member.
     *
     * @param array|null $memberRecord
     * @return bool
     */
    public static function isMemberActive($memberRecord)
    {
        if (!is_array($memberRecord) || empty($memberRecord)) {
            return false;
        }

        return (int)($memberRecord['disabled'] ?? 1) === 0
            && (int)($memberRecord['accountType'] ?? -1) !== 3
            && (int)($memberRecord['pending_info'] ?? 0) === 0;
    }

    /**
     * Synchronizes added/removed email lists for a member, skipping removals if another
     * active member still owns the same email address.
     *
     * @param int $uid
     * @param array $addedEmails
     * @param array $removedEmails
     * @param string $firstName
     * @param string $lastName
     * @param string $actorEmail
     * @return array
     */
    public function syncMemberEmailsDiff(
        $uid,
        array $addedEmails,
        array $removedEmails,
        $firstName = '',
        $lastName = '',
        $actorEmail = ''
    ) {
        $uid = (int)$uid;
        $addedMap = $this->normalizeEmailMap($addedEmails);
        $removedMap = $this->normalizeEmailMap($removedEmails);

        // If an address only changed casing in the same edit, treat it as unchanged for Exchange.
        foreach (array_keys($addedMap) as $lower) {
            if (isset($removedMap[$lower])) {
                unset($addedMap[$lower], $removedMap[$lower]);
            }
        }

        $firstName = trim((string)$firstName);
        $lastName = trim((string)$lastName);
        $displayName = trim($firstName . ' ' . $lastName);

        $operations = [];
        foreach ($addedMap as $email) {
            $operations[] = [
                'action'      => 'add',
                'email'       => $email,
                'firstName'   => $firstName,
                'lastName'    => $lastName,
                'displayName' => $displayName !== '' ? $displayName : $email,
                'memberUid'   => $uid,
            ];
        }

        foreach ($removedMap as $email) {
            if ($uid > 0 && $this->db->emailUsedByOtherActiveMember($email, $uid)) {
                $this->logger->info("Skipping Exchange contact removal because email belongs to another active member", [
                    'uid'   => $uid,
                    'email' => $email,
                ]);
                continue;
            }

            $operations[] = [
                'action'    => 'remove',
                'email'     => $email,
                'memberUid' => $uid,
            ];
        }

        return $this->syncOperations($operations, $actorEmail);
    }

    /**
     * Syncs a member's email changes to Exchange and falls back to webmaster alert email(s)
     * if Exchange sync is not configured or fails.
     *
     * @param int $uid
     * @param array $addedEmails
     * @param array $removedEmails
     * @param string $firstName
     * @param string $lastName
     * @param string $actorEmail
     * @return array
     */
    public function syncOrNotifyWebmaster(
        $uid,
        array $addedEmails,
        array $removedEmails,
        $firstName = '',
        $lastName = '',
        $actorEmail = ''
    ) {
        $addedMap = $this->normalizeEmailMap($addedEmails);
        $removedMap = $this->normalizeEmailMap($removedEmails);

        $result = $this->syncMemberEmailsDiff($uid, $addedEmails, $removedEmails, $firstName, $lastName, $actorEmail);

        if (!empty($result['success']) || (int)($result['operations_count'] ?? 0) === 0) {
            return $result;
        }

        $webmaster = KcbBase::getWebmasterEmail();
        $failureNote = !empty($result['configured'])
            ? "\n\nNote: Automated Microsoft 365 contact sync failed (" . ($result['message'] ?? 'unknown error') . ').'
            : '';

        foreach ($addedMap as $lower => $email) {
            if (isset($removedMap[$lower])) {
                continue;
            }
            $this->kcb->sendEmail(
                $webmaster,
                'Add email: ' . $email . $failureNote,
                'KCB Email Update [Add]',
                false
            );
        }

        foreach ($removedMap as $lower => $email) {
            if (isset($addedMap[$lower])) {
                continue;
            }
            if ($uid > 0 && $this->db->emailUsedByOtherActiveMember($email, $uid)) {
                continue;
            }
            $this->kcb->sendEmail(
                $webmaster,
                'Remove email: ' . $email . $failureNote,
                'KCB Email Update [Remove]',
                false
            );
        }

        return $result;
    }

    /**
     * Synchronizes all active emails for a member UID using either 'add' or 'remove'.
     *
     * @param int $uid
     * @param string $action 'add' or 'remove'
     * @param string $actorEmail
     * @param array $emailsOverride
     * @return array
     */
    public function syncMemberByUid($uid, $action, $actorEmail = '', array $emailsOverride = [])
    {
        $uid = (int)$uid;
        if ($uid <= 0) {
            return ['configured' => $this->isEnabled(), 'success' => false, 'operations_count' => 0, 'message' => 'Invalid member UID.'];
        }

        $record = $this->db->getMemberRecord($uid);
        if (!$record) {
            return ['configured' => $this->isEnabled(), 'success' => false, 'operations_count' => 0, 'message' => 'Member record not found.'];
        }

        $emails = $emailsOverride;
        if (empty($emails)) {
            $emails = $this->db->getActiveEmailList($uid);
        }

        $firstName = (string)($record['firstName'] ?? '');
        $lastName = (string)($record['lastName'] ?? '');

        if ($action === 'add') {
            if (!self::isMemberActive($record)) {
                return ['configured' => $this->isEnabled(), 'success' => true, 'skipped' => true, 'operations_count' => 0, 'message' => 'Member is not active; skipped Exchange contact add.'];
            }
            return $this->syncMemberEmailsDiff($uid, $emails, [], $firstName, $lastName, $actorEmail);
        }

        if ($action === 'remove') {
            return $this->syncMemberEmailsDiff($uid, [], $emails, $firstName, $lastName, $actorEmail);
        }

        return ['configured' => $this->isEnabled(), 'success' => false, 'operations_count' => 0, 'message' => 'Unsupported sync action.'];
    }

    /**
     * Sends a normalized batch of add/remove contact operations to the configured Azure webhook.
     *
     * @param array $operations
     * @param string $actorEmail
     * @param bool $dryRun
     * @return array
     */
    public function syncOperations(array $operations, $actorEmail = '', $dryRun = false)
    {
        $validOps = $this->normalizeOperations($operations);
        if (empty($validOps)) {
            return [
                'configured'       => $this->isEnabled(),
                'success'          => true,
                'skipped'          => true,
                'operations_count' => 0,
                'message'          => 'No valid contact operations to sync.',
            ];
        }

        if ($dryRun) {
            return [
                'configured'       => $this->isEnabled(),
                'success'          => true,
                'dry_run'          => true,
                'operations_count' => count($validOps),
                'operations'       => $validOps,
                'message'          => 'Dry run completed; no webhook called.',
            ];
        }

        $webhookUrl = $this->getWebhookUrl();
        if (!$this->isSyncFlagEnabled() || $webhookUrl === null) {
            $this->logger->info("Exchange contact sync skipped: webhook is not configured or disabled", [
                'operations_count' => count($validOps),
            ]);
            return [
                'configured'       => false,
                'success'          => false,
                'skipped'          => true,
                'operations_count' => count($validOps),
                'message'          => 'Exchange contact webhook is not configured.',
            ];
        }

        $isLocal = self::isLocalEnvironment();
        $syncOnLocalhost = $this->isTruthy($this->getSetting('APPSETTING_EXO_CONTACT_SYNC_ON_LOCALHOST', 'EXO_CONTACT_SYNC_ON_LOCALHOST'));
        if ($isLocal && !$syncOnLocalhost) {
            $this->logger->info("Exchange contact sync simulated on localhost", [
                'operations' => array_map(function ($op) {
                    return ['action' => $op['action'], 'email' => $op['email']];
                }, $validOps),
            ]);
            return [
                'configured'       => true,
                'success'          => true,
                'simulated'        => true,
                'operations_count' => count($validOps),
                'message'          => 'Simulated on localhost.',
            ];
        }

        $payload = [
            'source'      => 'kcb-website',
            'triggeredBy' => trim((string)$actorEmail),
            'timestamp'   => date('c'),
            'operations'  => $validOps,
        ];

        $organization = trim((string)$this->getSetting('APPSETTING_EXO_ORGANIZATION', 'EXO_ORGANIZATION'));
        if ($organization !== '') {
            $payload['organization'] = $organization;
        }

        $webhookSecret = (string)$this->getSetting('APPSETTING_EXO_CONTACT_WEBHOOK_SECRET', 'EXO_CONTACT_WEBHOOK_SECRET');
        $response = $this->postJson($webhookUrl, $payload, $webhookSecret);
        $safeTarget = $this->sanitizeUrlForLog($webhookUrl);

        if ($response['success']) {
            $this->logger->info("Exchange contact sync webhook triggered", [
                'target'           => $safeTarget,
                'http_status'      => $response['http_status'],
                'operations_count' => count($validOps),
                'actor'            => $actorEmail,
            ]);
            return [
                'configured'       => true,
                'success'          => true,
                'http_status'      => $response['http_status'],
                'operations_count' => count($validOps),
                'message'          => 'Exchange contact sync triggered.',
            ];
        }

        $this->logger->error("Exchange contact sync webhook failed", [
            'target'           => $safeTarget,
            'http_status'      => $response['http_status'],
            'error'            => $response['error'],
            'operations_count' => count($validOps),
            'actor'            => $actorEmail,
        ]);

        return [
            'configured'       => true,
            'success'          => false,
            'http_status'      => $response['http_status'],
            'operations_count' => count($validOps),
            'message'          => $response['error'] ?: ('HTTP ' . $response['http_status']),
        ];
    }

    /**
     * Backfills or reconciles all active members' emails into Exchange Contacts in batches.
     *
     * @param int|null $uid Optional single member UID
     * @param string $actorEmail
     * @param bool $dryRun
     * @param int $batchSize
     * @return array
     */
    public function syncAllActiveContacts($uid = null, $actorEmail = '', $dryRun = false, $batchSize = 25)
    {
        $rows = $this->db->getAllActiveMemberContactRows($uid);
        $operations = [];

        foreach ((array)$rows as $row) {
            $email = trim((string)($row['email_address'] ?? ''));
            if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                continue;
            }
            $firstName = trim((string)($row['firstName'] ?? ''));
            $lastName = trim((string)($row['lastName'] ?? ''));
            $displayName = trim($firstName . ' ' . $lastName);

            $operations[] = [
                'action'      => 'add',
                'email'       => $email,
                'firstName'   => $firstName,
                'lastName'    => $lastName,
                'displayName' => $displayName !== '' ? $displayName : $email,
                'memberUid'   => (int)($row['uid'] ?? 0),
            ];
        }

        $validOps = $this->normalizeOperations($operations);
        if (empty($validOps)) {
            return [
                'status'           => 'success',
                'configured'       => $this->isEnabled(),
                'dry_run'          => (bool)$dryRun,
                'operations_count' => 0,
                'batches'          => 0,
                'message'          => 'No active member contacts found to sync.',
            ];
        }

        if ($dryRun) {
            return [
                'status'           => 'success',
                'configured'       => $this->isEnabled(),
                'dry_run'          => true,
                'operations_count' => count($validOps),
                'batches'          => (int)ceil(count($validOps) / max(1, (int)$batchSize)),
                'operations'       => $validOps,
                'message'          => 'Dry run completed; no webhook called.',
            ];
        }

        $batchSize = max(1, (int)$batchSize);
        $chunks = array_chunk($validOps, $batchSize);
        $batchResults = [];
        $allSucceeded = true;

        foreach ($chunks as $chunk) {
            $res = $this->syncOperations($chunk, $actorEmail, false);
            $batchResults[] = $res;
            if (empty($res['success'])) {
                $allSucceeded = false;
            }
        }

        return [
            'status'           => $allSucceeded ? 'success' : 'failed',
            'configured'       => $this->isEnabled(),
            'dry_run'          => false,
            'operations_count' => count($validOps),
            'batches'          => count($chunks),
            'batch_results'    => $batchResults,
        ];
    }

    /**
     * File-based hourly rate limiter for administrative sync triggers.
     *
     * @param string $identifier
     * @param int $maxPerHour
     * @return bool
     */
    public function checkRateLimit($identifier, $maxPerHour = 10)
    {
        $logDir = $this->logger->getLogDir();
        $safeId = preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string)$identifier);
        $rateFile = $logDir . '.rate_exchange_sync_' . $safeId . '.json';
        $now = time();
        $window = 3600;

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
        @file_put_contents($rateFile, json_encode(array_values($records)), LOCK_EX);
        return true;
    }

    /**
     * Normalizes an email array into a lowercase-keyed map of valid unique addresses.
     *
     * @param array $emails
     * @return array
     */
    private function normalizeEmailMap(array $emails)
    {
        $map = [];
        foreach ($emails as $raw) {
            $email = trim((string)$raw);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
                $map[strtolower($email)] = $email;
            }
        }
        return $map;
    }

    /**
     * Validates and deduplicates sync operations.
     *
     * @param array $operations
     * @return array
     */
    private function normalizeOperations(array $operations)
    {
        $deduped = [];
        foreach ($operations as $op) {
            if (!is_array($op)) {
                continue;
            }
            $action = strtolower(trim((string)($op['action'] ?? '')));
            $email = trim((string)($op['email'] ?? ''));
            if (!in_array($action, ['add', 'remove'], true)) {
                continue;
            }
            if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                continue;
            }

            $key = $action . '|' . strtolower($email);
            $item = [
                'action' => $action,
                'email'  => $email,
            ];
            if ($action === 'add') {
                $firstName = trim((string)($op['firstName'] ?? ''));
                $lastName = trim((string)($op['lastName'] ?? ''));
                $displayName = trim((string)($op['displayName'] ?? ($firstName . ' ' . $lastName)));
                $item['firstName'] = $firstName;
                $item['lastName'] = $lastName;
                $item['displayName'] = $displayName !== '' ? $displayName : $email;
            }
            if (!empty($op['memberUid'])) {
                $item['memberUid'] = (int)$op['memberUid'];
            }
            $deduped[$key] = $item;
        }

        return array_values($deduped);
    }

    /**
     * Sends an HTTP POST JSON payload using cURL with a stream-context fallback.
     * Enforces SSRF protection by strictly validating the destination URL and restricting protocols.
     *
     * @param string $url
     * @param array $payload
     * @param string $secret
     * @return array
     */
    private function postJson($url, array $payload, $secret = '')
    {
        $safeUrl = self::validateSafeWebhookUrl($url, $this->isLocalEnvironment(), true);
        if ($safeUrl === null) {
            $this->logger->error("Exchange contact sync blocked: webhook URL failed SSRF validation", [
                'target' => $this->sanitizeUrlForLog($url),
            ]);
            return [
                'success'     => false,
                'http_status' => 0,
                'error'       => 'SSRF validation failed: destination URL is unsafe, invalid, or targets a private/reserved address.',
            ];
        }
        $url = $safeUrl;

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return ['success' => false, 'http_status' => 0, 'error' => 'Failed to encode JSON payload.'];
        }

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];
        if ($secret !== '') {
            $headers[] = 'X-KCB-Webhook-Secret: ' . $secret;
        }

        $isLocalDev = $this->isLocalEnvironment();

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                return ['success' => false, 'http_status' => 0, 'error' => 'Unable to initialize cURL.'];
            }

            $curlOptions = [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $json,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => self::DEFAULT_CONNECT_TIMEOUT_SECONDS,
                CURLOPT_TIMEOUT        => self::DEFAULT_TIMEOUT_SECONDS,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_MAXREDIRS      => 0,
            ];

            if (defined('CURLOPT_PROTOCOLS_STR')) {
                $curlOptions[CURLOPT_PROTOCOLS_STR] = $isLocalDev ? 'https,http' : 'https';
                if (defined('CURLOPT_REDIR_PROTOCOLS_STR')) {
                    $curlOptions[CURLOPT_REDIR_PROTOCOLS_STR] = '';
                }
            } else {
                if (defined('CURLOPT_PROTOCOLS')) {
                    $curlOptions[CURLOPT_PROTOCOLS] = CURLPROTO_HTTPS | ($isLocalDev ? CURLPROTO_HTTP : 0);
                }
                if (defined('CURLOPT_REDIR_PROTOCOLS')) {
                    $curlOptions[CURLOPT_REDIR_PROTOCOLS] = 0;
                }
            }

            curl_setopt_array($ch, $curlOptions);

            $body = curl_exec($ch);
            $errno = curl_errno($ch);
            $curlErr = curl_error($ch);
            $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($errno !== 0 || $body === false) {
                return ['success' => false, 'http_status' => $statusCode, 'error' => $curlErr ?: ('cURL error #' . $errno)];
            }

            $ok = ($statusCode >= 200 && $statusCode < 300);
            return [
                'success'     => $ok,
                'http_status' => $statusCode,
                'error'       => $ok ? '' : ('Webhook returned HTTP ' . $statusCode),
            ];
        }

        if (!preg_match('#^https?://#i', $url)) {
            return ['success' => false, 'http_status' => 0, 'error' => 'Invalid URL protocol for stream request.'];
        }

        $context = stream_context_create([
            'http' => [
                'method'          => 'POST',
                'header'          => implode("\r\n", $headers) . "\r\n",
                'content'         => $json,
                'timeout'         => self::DEFAULT_TIMEOUT_SECONDS,
                'ignore_errors'   => true,
                'follow_location' => 0,
                'max_redirects'   => 0,
            ],
            'ssl' => [
                'verify_peer'      => true,
                'verify_peer_name' => true,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        $statusCode = 0;
        $responseHeaders = function_exists('http_get_last_response_headers')
            ? http_get_last_response_headers()
            : ($http_response_header ?? null);

        if (is_array($responseHeaders)) {
            foreach ($responseHeaders as $headerLine) {
                if (preg_match('/^HTTP\/\S+\s+(\d{3})/i', $headerLine, $m)) {
                    $statusCode = (int)$m[1];
                }
            }
        }

        if ($body === false && $statusCode === 0) {
            return ['success' => false, 'http_status' => 0, 'error' => 'HTTP stream request failed.'];
        }

        $ok = ($statusCode >= 200 && $statusCode < 300);
        return [
            'success'     => $ok,
            'http_status' => $statusCode,
            'error'       => $ok ? '' : ('Webhook returned HTTP ' . $statusCode),
        ];
    }

    /**
     * Validates and sanitizes a webhook URL against Server-Side Request Forgery (SSRF).
     *
     * Enforces:
     * 1. Valid URL syntax.
     * 2. Scheme must be strictly HTTPS (HTTP allowed only for localhost/127.0.0.1 in local development).
     * 3. No embedded credentials (username/password in URL).
     * 4. Port restricted to 443 for HTTPS (and 80/custom dev port for localhost).
     * 5. Destination host cannot be private, reserved, loopback, or cloud-metadata IP
     *    (RFC 1918, RFC 3927, 169.254.169.254, 127.0.0.0/8, etc.).
     * 6. When $resolveDns is true, resolves host IPs and validates every resolved address.
     * 7. Optional domain allowlist via EXO_CONTACT_WEBHOOK_ALLOWED_DOMAINS.
     *
     * @param mixed $url
     * @param bool|null $allowLocalDev
     * @param bool $resolveDns
     * @return string|null Returns the sanitized/validated URL, or null if unsafe.
     */
    public static function validateSafeWebhookUrl($url, ?bool $allowLocalDev = null, bool $resolveDns = true)
    {
        if (!is_string($url) || trim($url) === '') {
            return null;
        }

        $url = trim($url);
        if (!preg_match('#^https?://#i', $url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $parts = parse_url($url);
        if (!is_array($parts)) {
            return null;
        }

        $scheme = strtolower((string)($parts['scheme'] ?? ''));
        if ($scheme !== 'https' && $scheme !== 'http') {
            return null;
        }

        // Forbid embedded credentials
        if (!empty($parts['user']) || !empty($parts['pass'])) {
            return null;
        }

        $host = strtolower(trim((string)($parts['host'] ?? '')));
        if ($host === '') {
            return null;
        }

        $isLocal = ($allowLocalDev !== null) ? $allowLocalDev : self::isLocalEnvironment();

        // HTTP is only permitted for localhost or loopback in local dev mode
        if ($scheme === 'http') {
            if (!$isLocal || !in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
                return null;
            }
        }

        // Validate port
        if (isset($parts['port'])) {
            $port = (int)$parts['port'];
            if ($scheme === 'https' && $port !== 443) {
                // If local dev, allow custom ports (e.g. 8443) on localhost
                if (!$isLocal || !in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
                    return null;
                }
            } elseif ($scheme === 'http' && $port !== 80) {
                if (!$isLocal || !in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
                    return null;
                }
            }
        }

        // Check local loopback in dev
        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return $isLocal ? $url : null;
        }

        // Optional domain allowlist check (if configured)
        $allowedDomainsSetting = getenv('APPSETTING_EXO_CONTACT_WEBHOOK_ALLOWED_DOMAINS')
            ?: (getenv('EXO_CONTACT_WEBHOOK_ALLOWED_DOMAINS') ?: ($_ENV['EXO_CONTACT_WEBHOOK_ALLOWED_DOMAINS'] ?? ''));
        if (trim((string)$allowedDomainsSetting) !== '') {
            $allowedList = array_filter(array_map('trim', explode(',', strtolower((string)$allowedDomainsSetting))));
            $matched = false;
            foreach ($allowedList as $domain) {
                if ($domain === '') {
                    continue;
                }
                if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                return null;
            }
        }

        // If host is an IP literal
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            // Must not be private or reserved (blocks 10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16, 127.0.0.0/8, 169.254.0.0/16, ::1, etc.)
            if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return null;
            }
            return $url;
        }

        // Host is a domain name: validate FQDN syntax
        if (!preg_match('/^[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?)+$/i', $host)) {
            return null;
        }

        if (!$resolveDns) {
            return $url;
        }

        // Resolve DNS records and verify all destination IPs
        $resolvedIps = @gethostbynamel($host);
        if ($resolvedIps === false || empty($resolvedIps)) {
            return null;
        }

        $allIps = (array)$resolvedIps;
        if (function_exists('dns_get_record')) {
            $aaaa = @dns_get_record($host, DNS_AAAA);
            if (is_array($aaaa)) {
                foreach ($aaaa as $rec) {
                    if (!empty($rec['ipv6'])) {
                        $allIps[] = $rec['ipv6'];
                    }
                }
            }
        }

        foreach ($allIps as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return null;
            }
        }

        return $url;
    }

    /**
     * Checks if runtime environment is local development.
     *
     * @return bool
     */
    public static function isLocalEnvironment()
    {
        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        $appEnv = strtolower((string)(getenv('APP_ENV') ?: (getenv('APPSETTING_APP_ENV') ?: ($_ENV['APP_ENV'] ?? ''))));
        return $serverName === 'localhost'
            || $appEnv === 'local'
            || $appEnv === 'development';
    }

    private function isSyncFlagEnabled()
    {
        $raw = $this->getSetting('APPSETTING_EXO_CONTACT_SYNC_ENABLED', 'EXO_CONTACT_SYNC_ENABLED');
        if ($raw === null || trim((string)$raw) === '') {
            return true;
        }

        return !in_array(strtolower(trim((string)$raw)), ['0', 'false', 'no', 'off'], true);
    }

    private function getWebhookUrl()
    {
        $raw = trim((string)$this->getSetting('APPSETTING_EXO_CONTACT_WEBHOOK_URL', 'EXO_CONTACT_WEBHOOK_URL'));
        if ($raw === '') {
            return null;
        }

        return self::validateSafeWebhookUrl($raw, $this->isLocalEnvironment(), false);
    }

    private function getSetting($primary, $fallback = null)
    {
        $candidates = [$primary];
        if ($fallback !== null && $fallback !== '') {
            $candidates[] = $fallback;
        }

        foreach ($candidates as $key) {
            $val = getenv($key);
            if ($val !== false && $val !== '') {
                return $val;
            }
            if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
                return $_ENV[$key];
            }
        }

        return null;
    }

    private function isTruthy($value)
    {
        return in_array(strtolower(trim((string)$value)), ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * Strips query string and credentials so webhook ?token= secrets are never written to logs.
     *
     * @param string $url
     * @return string
     */
    private function sanitizeUrlForLog($url)
    {
        $parts = parse_url($url);
        if (!is_array($parts)) {
            return 'invalid-url';
        }
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? 'unknown';
        $path = $parts['path'] ?? '/';
        return $scheme . '://' . $host . $path;
    }
}

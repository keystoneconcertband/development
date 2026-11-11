<?php
/**
 * PendingMemberService
 *
 * Encapsulates the pending member approval, onboarding profile completion,
 * webmaster + band director email notification, and reminder workflows.
 */

require_once __DIR__ . '/kcbBase.class.php';
require_once __DIR__ . '/member.db.class.php';
require_once __DIR__ . '/kcbLogger.class.php';

class PendingMemberService
{
    public const FIRST_REMINDER_DAYS = 3;
    public const REPEAT_REMINDER_DAYS = 7;
    public const MAX_AUTO_REMINDERS = 3;

    public const INSTRUMENTS = [
        'baritone'     => 'Baritone',
        'bassClarinet' => 'Bass Clarinet',
        'bassoon'      => 'Bassoon',
        'clarinet'     => 'Clarinet',
        'flute'        => 'Flute',
        'frenchHorn'   => 'French Horn',
        'oboe'         => 'Oboe',
        'saxophone'    => 'Saxophone',
        'trombone'     => 'Trombone',
        'trumpet'      => 'Trumpet',
        'tuba'         => 'Tuba',
        'percussion'   => 'Percussion',
    ];

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
     * Returns canonical instrument options mapped code => label.
     *
     * @return array
     */
    public function getInstrumentOptions()
    {
        $dbMap = $this->db->getInstrumentDisplayMap();
        $options = self::INSTRUMENTS;
        foreach ($options as $key => $defaultLabel) {
            if (!empty($dbMap[$key])) {
                $options[$key] = $dbMap[$key];
            }
        }
        return $options;
    }

    /**
     * Validates and normalizes submitted member profile completion fields.
     *
     * @param array $data
     * @return array ['valid' => bool, 'error' => string, 'data' => array]
     */
    public function validatePendingCompletionInput(array $data)
    {
        $firstName = trim((string)($data['firstName'] ?? ''));
        $lastName = trim((string)($data['lastName'] ?? ''));
        $address1 = trim((string)($data['address1'] ?? ''));
        $address2 = trim((string)($data['address2'] ?? ''));
        $city = trim((string)($data['city'] ?? ''));
        $zip = trim((string)($data['zip'] ?? ''));
        $textRaw = trim((string)($data['text'] ?? ''));
        $emergencyName = trim((string)($data['emergency_contact_name'] ?? ''));
        $emergencyPhoneRaw = trim((string)($data['emergency_contact_phone'] ?? ''));
        $displayFullName = !empty($data['displayFullName']) ? '1' : '';

        if ($firstName === '' || mb_strlen($firstName) > 50) {
            return ['valid' => false, 'error' => 'First name is required (max 50 characters).', 'data' => []];
        }
        if ($lastName === '' || mb_strlen($lastName) > 50) {
            return ['valid' => false, 'error' => 'Last name is required (max 50 characters).', 'data' => []];
        }
        if ($address1 === '' || mb_strlen($address1) > 255) {
            return ['valid' => false, 'error' => 'Street address is required (max 255 characters).', 'data' => []];
        }
        if (mb_strlen($address2) > 255) {
            return ['valid' => false, 'error' => 'Address line 2 must be 255 characters or less.', 'data' => []];
        }
        if ($city === '' || mb_strlen($city) > 100) {
            return ['valid' => false, 'error' => 'City is required (max 100 characters).', 'data' => []];
        }
        if (!preg_match('/^\d{5}$/', $zip)) {
            return ['valid' => false, 'error' => 'A valid 5-digit Zip Code is required.', 'data' => []];
        }

        $textPhone = '';
        if ($textRaw !== '') {
            $textPhone = $this->normalizeUsPhone($textRaw);
            if ($textPhone === null) {
                return ['valid' => false, 'error' => 'Cell phone number must be a valid 10-digit phone number.', 'data' => []];
            }
        }

        if ($emergencyName === '' || mb_strlen($emergencyName) > 255) {
            return ['valid' => false, 'error' => 'Emergency contact name is required (max 255 characters).', 'data' => []];
        }

        $emergencyPhone = $this->normalizeUsPhone($emergencyPhoneRaw);
        if ($emergencyPhone === null) {
            return ['valid' => false, 'error' => 'Emergency contact phone must be a valid 10-digit phone number.', 'data' => []];
        }

        $rawEmails = isset($data['email']) ? (array)$data['email'] : [];
        $emails = [];
        foreach ($rawEmails as $rawEmail) {
            $email = trim((string)$rawEmail);
            if ($email === '') {
                continue;
            }
            if (mb_strlen($email) > 100 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                return ['valid' => false, 'error' => 'Please enter valid email address(es) (max 100 characters).', 'data' => []];
            }
            $emails[strtolower($email)] = $email;
        }
        $emails = array_values($emails);

        if (empty($emails)) {
            return ['valid' => false, 'error' => 'At least one email address is required.', 'data' => []];
        }

        $rawInstruments = isset($data['instrument']) ? (array)$data['instrument'] : [];
        $instruments = [];
        foreach ($rawInstruments as $instr) {
            $instr = trim((string)$instr);
            if ($instr !== '' && array_key_exists($instr, self::INSTRUMENTS)) {
                $instruments[$instr] = $instr;
            }
        }
        $instruments = array_values($instruments);

        if (empty($instruments)) {
            return ['valid' => false, 'error' => 'Please select at least one instrument that you play.', 'data' => []];
        }

        $normalized = [
            'firstName'               => $firstName,
            'lastName'                => $lastName,
            'address1'                => $address1,
            'address2'                => $address2,
            'city'                    => $city,
            'zip'                     => $zip,
            'text'                    => $textPhone,
            'emergency_contact_name'  => $emergencyName,
            'emergency_contact_phone' => $emergencyPhone,
            'email'                   => $emails,
            'instrument'              => $instruments,
        ];
        if ($displayFullName === '1') {
            $normalized['displayFullName'] = '1';
        }

        return ['valid' => true, 'error' => '', 'data' => $normalized];
    }

    /**
     * Activates a pending member so they can visit the pending member page to complete missing info.
     *
     * @param int $uid
     * @param string $adminEmail
     * @param string|null $baseUrl
     * @return array
     */
    public function activatePendingMember($uid, $adminEmail, $baseUrl = null)
    {
        $uid = (int)$uid;
        if ($uid <= 0) {
            return ['status' => 'error', 'message' => 'Invalid member identifier.'];
        }

        if (!$this->db->ensurePendingOnboardingSchema()) {
            return ['status' => 'error', 'message' => 'Database onboarding columns are missing. Please run the schema migration script.'];
        }

        $member = $this->db->getMemberRecord($uid);
        if (!$member || (int)($member['disabled'] ?? 1) !== 0) {
            return ['status' => 'error', 'message' => 'Pending member record was not found.'];
        }

        if ((int)$member['accountType'] !== 3 && (int)($member['pending_info'] ?? 0) !== 1) {
            return ['status' => 'error', 'message' => 'This member is already active and has completed onboarding.'];
        }

        $emails = $this->splitCsv($member['email'] ?? '');
        if (empty($emails)) {
            return ['status' => 'error', 'message' => 'Cannot activate member because they have no email address on file.'];
        }

        $token = bin2hex(random_bytes(32));

        try {
            $this->db->beginTransaction();
            if (!$this->db->activatePendingMemberForOnboarding($uid, $token, $adminEmail)) {
                $this->db->rollBackTransaction();
                $this->logger->error("Failed to activate pending member in database", ['uid' => $uid, 'admin' => $adminEmail]);
                return ['status' => 'error', 'message' => 'Unable to activate pending member in database.'];
            }
            $this->db->executeTransaction();
        } catch (Exception $e) {
            $this->db->rollBackTransaction();
            $this->logger->error("Exception activating pending member", ['uid' => $uid, 'exception' => $e]);
            return ['status' => 'error', 'message' => 'A database error occurred while activating the member.'];
        }

        $completionUrl = $this->buildCompletionUrl($token, $baseUrl);
        $emailSent = $this->sendActivationEmail($member, $emails, $completionUrl, $baseUrl);

        $this->logger->notice("Admin activated pending member for onboarding", [
            'uid'        => $uid,
            'member'     => trim(($member['firstName'] ?? '') . ' ' . ($member['lastName'] ?? '')),
            'emails'     => $emails,
            'admin'      => $adminEmail,
            'email_sent' => $emailSent,
        ]);

        if (!$emailSent) {
            return [
                'status'         => 'warning',
                'email_sent'     => false,
                'activation_url' => $completionUrl,
                'message'        => 'Member activated, but the welcome email could not be sent. You can copy their onboarding link directly.',
            ];
        }

        return [
            'status'         => 'success',
            'email_sent'     => true,
            'activation_url' => $completionUrl,
            'message'        => 'Pending member activated and emailed a link to complete their missing information.',
        ];
    }

    /**
     * Resolves an onboarding member record from a URL token using constant-time verification.
     *
     * @param string $token
     * @return array|null
     */
    public function getPendingMemberForToken($token)
    {
        $token = trim((string)$token);
        if (!preg_match('/^[a-f0-9]{64}$/i', $token)) {
            return null;
        }

        $record = $this->db->getPendingMemberByToken($token);
        if (!$record || empty($record['activation_token'])) {
            return null;
        }

        if (!hash_equals((string)$record['activation_token'], $token)) {
            return null;
        }

        return $record;
    }

    /**
     * Resolves an onboarding member record for a currently logged-in session UID.
     *
     * @param int $uid
     * @return array|null
     */
    public function getPendingMemberForSessionUid($uid)
    {
        $uid = (int)$uid;
        if ($uid <= 0) {
            return null;
        }

        return $this->db->getPendingMemberForOnboarding($uid);
    }

    /**
     * Completes missing information for a pending/onboarding member and emails webmaster + CC band director.
     *
     * @param int $uid
     * @param array $inputData
     * @param string $actorEmail
     * @param bool $sendNotification
     * @return array
     */
    public function completePendingMemberInfo($uid, array $inputData, $actorEmail, $sendNotification = true)
    {
        $uid = (int)$uid;
        if ($uid <= 0) {
            return ['status' => 'error', 'message' => 'Invalid member identifier.'];
        }

        $validation = $this->validatePendingCompletionInput($inputData);
        if (!$validation['valid']) {
            return ['status' => 'error', 'message' => $validation['error']];
        }
        $normalized = $validation['data'];

        $existingMember = $this->db->getMemberRecord($uid);
        if (!$existingMember || (int)($existingMember['disabled'] ?? 1) !== 0) {
            return ['status' => 'error', 'message' => 'Member record could not be found or is inactive.'];
        }

        foreach ($normalized['email'] as $emailAddr) {
            if ($this->db->emailBelongsToOtherMember($emailAddr, $uid)) {
                return [
                    'status'  => 'error',
                    'message' => 'The email address "' . $emailAddr . '" is already associated with another member account.',
                ];
            }
        }

        $auditUser = trim((string)$actorEmail) !== '' ? $actorEmail : $normalized['email'][0];

        try {
            $this->db->beginTransaction();

            if ($this->db->updateMember($uid, $normalized, $auditUser) === false) {
                $this->db->rollBackTransaction();
                return ['status' => 'error', 'message' => 'Failed to save member personal information.'];
            }

            if ($this->db->upsertAddress($uid, $normalized, $auditUser) === false) {
                $this->db->rollBackTransaction();
                return ['status' => 'error', 'message' => 'Failed to save member address.'];
            }

            if (!$this->syncMemberEmails($uid, $normalized['email'], $auditUser)) {
                $this->db->rollBackTransaction();
                return ['status' => 'error', 'message' => 'Failed to save member email addresses.'];
            }

            if (!$this->syncMemberInstruments($uid, $normalized['instrument'], $auditUser)) {
                $this->db->rollBackTransaction();
                return ['status' => 'error', 'message' => 'Failed to save member instruments.'];
            }

            if (!$this->db->markPendingInfoCompleted($uid, $auditUser)) {
                $this->db->rollBackTransaction();
                return ['status' => 'error', 'message' => 'Failed to finalize member activation.'];
            }

            $this->db->executeTransaction();
            MemberDB::clearPublicMembersCache();
        } catch (Exception $e) {
            $this->db->rollBackTransaction();
            $this->logger->error("Exception completing pending member info", ['uid' => $uid, 'exception' => $e]);
            return ['status' => 'error', 'message' => 'A database error occurred while saving your information.'];
        }

        $emailSent = true;
        if ($sendNotification) {
            $emailSent = $this->sendCompletedInfoNotification($uid, $normalized, $auditUser);
            if (!$emailSent) {
                $this->logger->error("Member info saved, but notification email to webmaster/director failed", [
                    'uid'    => $uid,
                    'emails' => $normalized['email'],
                ]);
            }
        }

        $this->logger->notice("Pending member completed onboarding profile", [
            'uid'        => $uid,
            'name'       => $normalized['firstName'] . ' ' . $normalized['lastName'],
            'emails'     => $normalized['email'],
            'actor'      => $auditUser,
            'email_sent' => $emailSent,
        ]);

        return [
            'status'     => 'success',
            'email_sent' => $emailSent,
            'firstName'  => $normalized['firstName'],
            'lastName'   => $normalized['lastName'],
            'emails'     => $normalized['email'],
            'message'    => $emailSent
                ? 'Thank you! Your member information has been submitted and sent to our webmaster and band director.'
                : 'Your member information was saved, though the confirmation email to the webmaster could not be dispatched.',
        ];
    }

    /**
     * Sends a reminder email to a specific activated pending member who has not yet submitted missing info.
     *
     * @param int $uid
     * @param string $actorEmail
     * @param string|null $baseUrl
     * @param bool $isAutomatic
     * @return array
     */
    public function sendReminderToMember($uid, $actorEmail, $baseUrl = null, $isAutomatic = false)
    {
        $uid = (int)$uid;
        if ($uid <= 0) {
            return ['status' => 'error', 'message' => 'Invalid member identifier.'];
        }

        if (!$this->db->ensurePendingOnboardingSchema()) {
            return ['status' => 'error', 'message' => 'Database onboarding columns are missing.'];
        }

        $member = $this->db->getMemberRecord($uid);
        if (!$member || (int)($member['disabled'] ?? 1) !== 0 || (int)($member['pending_info'] ?? 0) !== 1) {
            return ['status' => 'error', 'message' => 'Member is not currently awaiting profile information.'];
        }

        $emails = $this->splitCsv($member['email'] ?? '');
        if (empty($emails)) {
            return ['status' => 'error', 'message' => 'No email address is on file for this member.'];
        }

        $token = trim((string)($member['activation_token'] ?? ''));
        if ($token === '') {
            $token = bin2hex(random_bytes(32));
            $this->db->setActivationTokenIfMissing($uid, $token, $actorEmail);
        }

        $completionUrl = $this->buildCompletionUrl($token, $baseUrl);
        $sent = $this->sendReminderEmail($member, $emails, $completionUrl, $baseUrl);

        if (!$sent) {
            $this->logger->error("Failed to dispatch pending member reminder email", [
                'uid'       => $uid,
                'emails'    => $emails,
                'automatic' => $isAutomatic,
            ]);
            return ['status' => 'failed', 'message' => 'Unable to send reminder email. Please check mail settings.'];
        }

        $this->db->recordReminderSent($uid, $actorEmail);
        $this->logger->notice("Pending member reminder email sent", [
            'uid'            => $uid,
            'member'         => trim(($member['firstName'] ?? '') . ' ' . ($member['lastName'] ?? '')),
            'emails'         => $emails,
            'reminder_count' => (int)($member['reminder_count'] ?? 0) + 1,
            'automatic'      => $isAutomatic,
            'actor'          => $actorEmail,
        ]);

        return [
            'status'  => 'success',
            'message' => 'Reminder email sent to ' . implode(', ', $emails) . '.',
        ];
    }

    /**
     * Evaluates and sends automated reminders for activated members who have not filled out their info.
     *
     * @param bool $dryRun
     * @param bool $force
     * @param string|null $baseUrl
     * @return array
     */
    public function runAutomaticReminders($dryRun = false, $force = false, $baseUrl = null)
    {
        if (!$this->db->ensurePendingOnboardingSchema()) {
            return [
                'status'       => 'failed',
                'message'      => 'Onboarding schema columns are not available on kcb_members.',
                'due_count'    => 0,
                'sent_count'   => 0,
                'failed_count' => 0,
                'members'      => [],
            ];
        }

        $tz = new \DateTimeZone('America/New_York');
        $now = new \DateTimeImmutable('now', $tz);
        $firstCutoff = $now->modify('-' . self::FIRST_REMINDER_DAYS . ' days')->format('Y-m-d H:i:s');
        $repeatCutoff = $now->modify('-' . self::REPEAT_REMINDER_DAYS . ' days')->format('Y-m-d H:i:s');

        $dueMembers = $this->db->getPendingMembersDueForReminder(
            $firstCutoff,
            $repeatCutoff,
            self::MAX_AUTO_REMINDERS
        );
        if (!is_array($dueMembers)) {
            $dueMembers = [];
        }

        if (!$this->isReminderEmailEnabled() && !$force && !$dryRun) {
            return [
                'status'       => 'disabled',
                'message'      => 'Pending member reminder emails are disabled via SEND_PENDING_REMINDER_EMAIL.',
                'due_count'    => count($dueMembers),
                'sent_count'   => 0,
                'failed_count' => 0,
                'members'      => [],
            ];
        }

        $results = [];
        $sentCount = 0;
        $failedCount = 0;

        foreach ($dueMembers as $row) {
            $uid = (int)$row['uid'];
            $name = trim(($row['firstName'] ?? '') . ' ' . ($row['lastName'] ?? ''));
            $emails = $this->splitCsv($row['email'] ?? '');

            if ($dryRun) {
                $results[] = [
                    'uid'               => $uid,
                    'name'              => $name,
                    'emails'            => $emails,
                    'activated_dt_tm'   => $row['activated_dt_tm'],
                    'reminder_count'    => (int)$row['reminder_count'],
                    'last_reminder'     => $row['last_reminder_dt_tm'],
                    'action'            => 'would_send',
                ];
                continue;
            }

            $res = $this->sendReminderToMember($uid, 'SYSTEM_REMINDER_CRON', $baseUrl, true);
            if ($res['status'] === 'success') {
                $sentCount++;
            } else {
                $failedCount++;
            }

            $results[] = [
                'uid'            => $uid,
                'name'           => $name,
                'emails'         => $emails,
                'reminder_count' => (int)$row['reminder_count'] + ($res['status'] === 'success' ? 1 : 0),
                'action'         => $res['status'],
                'message'        => $res['message'],
            ];
        }

        if ($dryRun) {
            return [
                'status'       => 'dry_run',
                'due_count'    => count($dueMembers),
                'sent_count'   => 0,
                'failed_count' => 0,
                'members'      => $results,
            ];
        }

        return [
            'status'       => $failedCount > 0 && $sentCount === 0 ? 'failed' : 'completed',
            'due_count'    => count($dueMembers),
            'sent_count'   => $sentCount,
            'failed_count' => $failedCount,
            'members'      => $results,
            'message'      => count($dueMembers) === 0
                ? 'No pending members are currently due for a reminder.'
                : "Sent {$sentCount} reminder(s)" . ($failedCount > 0 ? " ({$failedCount} failed)." : '.'),
        ];
    }

    /**
     * Checks whether automated reminder emails are enabled via environment flags.
     *
     * @return bool
     */
    public function isReminderEmailEnabled()
    {
        $val = getenv('SEND_PENDING_REMINDER_EMAIL');
        if ($val === false || $val === null || $val === '') {
            $val = getenv('APPSETTING_SEND_PENDING_REMINDER_EMAIL');
        }
        if ($val !== false && $val !== null && $val !== '') {
            return !in_array(strtolower(trim((string)$val)), ['false', '0', 'off', 'no', 'disabled'], true);
        }

        return true;
    }

    /**
     * Rate limiter for cron/script endpoints to prevent abuse.
     *
     * @param string $identifier
     * @param int $maxPerHour
     * @return bool
     */
    public function checkRateLimit($identifier, $maxPerHour = 10)
    {
        $logDir = $this->logger->getLogDir();
        $safeId = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $identifier);
        $rateFile = $logDir . '.rate_pending_reminders_' . $safeId . '.json';
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
     * Builds the completion URL with explicit .php path (required for Azure Nginx).
     *
     * @param string $token
     * @param string|null $baseUrl
     * @return string
     */
    public function buildCompletionUrl($token, $baseUrl = null)
    {
        $base = !empty($baseUrl) ? rtrim($baseUrl, '/') : KcbBase::getSiteBaseUrl();
        return $base . '/members/pendingMembers.php?token=' . rawurlencode($token);
    }

    /* PRIVATE HELPERS */

    private function sendActivationEmail(array $member, array $emails, $completionUrl, $baseUrl = null)
    {
        $firstName = htmlspecialchars((string)($member['firstName'] ?? 'Musician'), ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($completionUrl, ENT_QUOTES, 'UTF-8');
        $base = !empty($baseUrl) ? rtrim((string)$baseUrl, '/') : KcbBase::getSiteBaseUrl();
        $loginUrl = htmlspecialchars($base . '/members/reauth.php', ENT_QUOTES, 'UTF-8');
        $siteGuideHtml = $this->buildSiteOverviewHtml($base);

        $subject = 'Welcome to Keystone Concert Band - Please Complete Your Member Info';
        $body = "<p>Hi <strong>{$firstName}</strong>,</p>"
            . "<p>Welcome to the Keystone Concert Band! An officer has activated your membership account.</p>"
            . "<p>Before we add you to the band roster and mailing list, please visit the page below to fill in your remaining member details (mailing address, emergency contact, roster display preference, and confirm your contact &amp; instrument info):</p>"
            . "<p style='margin: 20px 0;'>"
            . "<a href='{$safeUrl}' style='display:inline-block;padding:12px 22px;background-color:#008cba;color:#ffffff;text-decoration:none;border-radius:4px;font-weight:bold;'>Complete Your Member Information</a>"
            . "</p>"
            . "<p>Or copy and paste this link into your browser:<br><a href='{$safeUrl}'>{$safeUrl}</a></p>"
            . "<p>You can also sign in anytime at <a href='{$loginUrl}'>Member Login</a> using your email address on file to access the completion form.</p>"
            . $siteGuideHtml
            . "<p>Thanks,<br>Keystone Concert Band</p>";

        return $this->kcb->sendEmail($emails, $body, $subject, true);
    }

    private function buildSiteOverviewHtml($baseUrl)
    {
        $base = rtrim((string)$baseUrl, '/');
        $homeUrl = htmlspecialchars($base . '/index.php', ENT_QUOTES, 'UTF-8');
        $loginUrl = htmlspecialchars($base . '/members/reauth.php', ENT_QUOTES, 'UTF-8');
        $overviewUrl = htmlspecialchars($base . '/members/index.php', ENT_QUOTES, 'UTF-8');
        $myInfoUrl = htmlspecialchars($base . '/members/myInfo.php', ENT_QUOTES, 'UTF-8');
        $rosterUrl = htmlspecialchars($base . '/members/currentMembers.php', ENT_QUOTES, 'UTF-8');
        $musicUrl = htmlspecialchars($base . '/members/music.php', ENT_QUOTES, 'UTF-8');
        $docsUrl = htmlspecialchars($base . '/members/documents.php', ENT_QUOTES, 'UTF-8');
        $bylawsUrl = htmlspecialchars($base . '/assets/docs/KCB_Bylaws_2021.pdf', ENT_QUOTES, 'UTF-8');
        $policiesUrl = htmlspecialchars($base . '/assets/docs/KCB_Policy_March_2023.pdf', ENT_QUOTES, 'UTF-8');
        $concertsUrl = htmlspecialchars($base . '/concerts.php', ENT_QUOTES, 'UTF-8');

        return "<div style='margin:24px 0;padding:16px 20px;background-color:#f8f9fa;border:1px solid #dee2e6;border-left:4px solid #008cba;border-radius:4px;font-family:Arial,sans-serif;font-size:14px;line-height:1.5;'>"
            . "<h3 style='margin:0 0 10px 0;font-size:16px;color:#008cba;'>About the Keystone Concert Band Website</h3>"
            . "<p style='margin:0 0 10px 0;'>Once your account is active, you can explore everything in our Member Area on <a href='{$homeUrl}'>keystoneconcertband.com</a>:</p>"
            . "<p style='margin:0 0 10px 0;'><strong>How Member Login Works:</strong> There is no password to remember—click <strong>Member Login</strong> in the top menu (or visit <a href='{$loginUrl}'>Member Login</a>) and enter your email address on file to receive a one-time login code.</p>"
            . "<ul style='margin:0 0 12px 18px;padding:0;'>"
            . "<li style='margin-bottom:6px;'><a href='{$overviewUrl}'><strong>Member Overview</strong></a>: Quick dashboard with member announcements, links, and band resources.</li>"
            . "<li style='margin-bottom:6px;'><a href='{$myInfoUrl}'><strong>My Info</strong></a>: View or update your phone number, mailing address, email addresses, emergency contact, instruments, and public roster display preference anytime.</li>"
            . "<li style='margin-bottom:6px;'><a href='{$rosterUrl}'><strong>Current Members</strong></a>: Member directory showing fellow musicians across every instrument section.</li>"
            . "<li style='margin-bottom:6px;'><a href='{$musicUrl}'><strong>Music Library</strong></a>: Browse pieces in the band library along with notes and reference links.</li>"
            . "<li style='margin-bottom:6px;'><a href='{$docsUrl}'><strong>Band Documents</strong></a>: Access band files, including the <a href='{$bylawsUrl}'>KCB Bylaws</a> and <a href='{$policiesUrl}'>Band Policies</a>.</li>"
            . "<li style='margin-bottom:0;'><a href='{$concertsUrl}'><strong>Concerts &amp; Calendar</strong></a>: See upcoming concert dates, venues, uniform notes, and the band Google Calendar.</li>"
            . "</ul>"
            . "<p style='margin:0;color:#555;'><strong>Rehearsals:</strong> Wednesdays from 7:00 PM to 9:00 PM (early February through mid-December) in the community room at Good Shepherd Lutheran Church, 3700 Rutherford St., Harrisburg (Paxtang).</p>"
            . "</div>";
    }

    private function sendReminderEmail(array $member, array $emails, $completionUrl, $baseUrl = null)
    {
        $firstName = htmlspecialchars((string)($member['firstName'] ?? 'Musician'), ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($completionUrl, ENT_QUOTES, 'UTF-8');
        $base = !empty($baseUrl) ? rtrim((string)$baseUrl, '/') : KcbBase::getSiteBaseUrl();
        $loginUrl = htmlspecialchars($base . '/members/reauth.php', ENT_QUOTES, 'UTF-8');

        $activatedDateText = '';
        if (!empty($member['activated_dt_tm'])) {
            $ts = strtotime((string)$member['activated_dt_tm']);
            if ($ts !== false) {
                $activatedDateText = ' (activated on ' . htmlspecialchars(date('M j, Y', $ts), ENT_QUOTES, 'UTF-8') . ')';
            }
        }

        $subject = 'Reminder: Complete Your Keystone Concert Band Member Information';
        $body = "<p>Hi <strong>{$firstName}</strong>,</p>"
            . "<p>This is a friendly reminder that your Keystone Concert Band membership account{$activatedDateText} is still missing some required details (mailing address, emergency contact, and roster display preference).</p>"
            . "<p>Please take a minute to submit your missing information using the link below:</p>"
            . "<p style='margin: 20px 0;'>"
            . "<a href='{$safeUrl}' style='display:inline-block;padding:12px 22px;background-color:#008cba;color:#ffffff;text-decoration:none;border-radius:4px;font-weight:bold;'>Complete Your Member Information</a>"
            . "</p>"
            . "<p>Or copy and paste this link into your browser:<br><a href='{$safeUrl}'>{$safeUrl}</a></p>"
            . "<p>Alternatively, you can sign in via <a href='{$loginUrl}'>Member Login</a> with your email address on file.</p>"
            . "<p>Thanks,<br>Keystone Concert Band</p>";

        return $this->kcb->sendEmail($emails, $body, $subject, true);
    }

    private function sendCompletedInfoNotification($uid, array $normalized, $actorEmail)
    {
        $instrumentMap = $this->getInstrumentOptions();
        $instrumentLabels = [];
        foreach ($normalized['instrument'] as $code) {
            $instrumentLabels[] = $instrumentMap[$code] ?? $code;
        }

        $fullName = htmlspecialchars($normalized['firstName'] . ' ' . $normalized['lastName'], ENT_QUOTES, 'UTF-8');
        $displayPref = !empty($normalized['displayFullName'])
            ? 'Full Name (' . $fullName . ')'
            : 'First Name + Last Initial (' . htmlspecialchars($normalized['firstName'] . ' ' . substr($normalized['lastName'], 0, 1) . '.', ENT_QUOTES, 'UTF-8') . ')';

        $addressLines = htmlspecialchars($normalized['address1'], ENT_QUOTES, 'UTF-8');
        if ($normalized['address2'] !== '') {
            $addressLines .= '<br>' . htmlspecialchars($normalized['address2'], ENT_QUOTES, 'UTF-8');
        }
        $addressLines .= '<br>' . htmlspecialchars($normalized['city'], ENT_QUOTES, 'UTF-8') . ', PA ' . htmlspecialchars($normalized['zip'], ENT_QUOTES, 'UTF-8');

        $cellFormatted = $normalized['text'] !== ''
            ? htmlspecialchars($this->formatPhone($normalized['text']), ENT_QUOTES, 'UTF-8')
            : '<em>Not provided</em>';
        $emergName = htmlspecialchars($normalized['emergency_contact_name'], ENT_QUOTES, 'UTF-8');
        $emergPhone = htmlspecialchars($this->formatPhone($normalized['emergency_contact_phone']), ENT_QUOTES, 'UTF-8');
        $emailHtml = implode('<br>', array_map(function ($e) {
            $safe = htmlspecialchars($e, ENT_QUOTES, 'UTF-8');
            return "<a href='mailto:{$safe}'>{$safe}</a>";
        }, $normalized['email']));
        $instrHtml = htmlspecialchars(implode(', ', $instrumentLabels), ENT_QUOTES, 'UTF-8');
        $submittedBy = htmlspecialchars((string)$actorEmail, ENT_QUOTES, 'UTF-8');
        $submittedAt = htmlspecialchars(date('M j, Y g:i A T'), ENT_QUOTES, 'UTF-8');

        $subject = 'KCB Pending Member Info Submitted - ' . $normalized['firstName'] . ' ' . $normalized['lastName'];
        $body = "<p>A pending member has completed their onboarding information on the Keystone Concert Band website:</p>"
            . "<table cellpadding='8' cellspacing='0' border='1' style='border-collapse:collapse;border-color:#dee2e6;font-family:Arial,sans-serif;font-size:14px;'>"
            . "<tr><th align='left' style='background:#f8f9fa;'>Member UID</th><td>" . (int)$uid . "</td></tr>"
            . "<tr><th align='left' style='background:#f8f9fa;'>Name</th><td>{$fullName}</td></tr>"
            . "<tr><th align='left' style='background:#f8f9fa;'>Roster Display Name</th><td>{$displayPref}</td></tr>"
            . "<tr><th align='left' style='background:#f8f9fa;'>Email Address(es)</th><td>{$emailHtml}</td></tr>"
            . "<tr><th align='left' style='background:#f8f9fa;'>Cell Phone</th><td>{$cellFormatted}</td></tr>"
            . "<tr><th align='left' style='background:#f8f9fa;'>Mailing Address</th><td>{$addressLines}</td></tr>"
            . "<tr><th align='left' style='background:#f8f9fa;'>Emergency Contact</th><td>{$emergName}<br>{$emergPhone}</td></tr>"
            . "<tr><th align='left' style='background:#f8f9fa;'>Instrument(s)</th><td>{$instrHtml}</td></tr>"
            . "<tr><th align='left' style='background:#f8f9fa;'>Submitted By</th><td>{$submittedBy} on {$submittedAt}</td></tr>"
            . "</table>"
            . "<p style='margin-top:16px;color:#555;'>Please add the member's email address(es) to the band mailing list if not already added.</p>";

        return $this->kcb->sendEmail(
            KcbBase::getWebmasterEmail(),
            $body,
            $subject,
            true,
            KcbBase::getBandDirectorEmail()
        );
    }

    private function syncMemberEmails($uid, array $newEmails, $updateUser)
    {
        $rows = $this->db->getEmailAddresses($uid);
        $currEmails = [];
        foreach ((array)$rows as $row) {
            if (!empty($row['email_address'])) {
                $currEmails[] = $row['email_address'];
            }
        }

        $currLower = array_map('strtolower', $currEmails);
        $newLower = array_map('strtolower', $newEmails);

        foreach ($newEmails as $email) {
            if (!in_array(strtolower($email), $currLower, true)) {
                if (!$this->db->addEmail($email, $uid, $updateUser)) {
                    return false;
                }
            }
        }

        foreach ($currEmails as $email) {
            if (!in_array(strtolower($email), $newLower, true)) {
                if (!$this->db->delEmail($email, $uid)) {
                    return false;
                }
            }
        }

        return $this->db->activateEmail($uid, $updateUser) !== false;
    }

    private function syncMemberInstruments($uid, array $newInstruments, $updateUser)
    {
        $rows = $this->db->getMemberInstruments($uid);
        $currInstruments = [];
        foreach ((array)$rows as $row) {
            if (!empty($row['instrument'])) {
                $currInstruments[] = $row['instrument'];
            }
        }

        foreach (array_diff($newInstruments, $currInstruments) as $instr) {
            if (!$this->db->addInstrument($instr, $uid, $updateUser)) {
                return false;
            }
        }

        foreach (array_diff($currInstruments, $newInstruments) as $instr) {
            if (!$this->db->delInstrument($instr, $uid)) {
                return false;
            }
        }

        return $this->db->updateLastUpdateOnInstrument($uid, $updateUser) !== false;
    }

    private function normalizeUsPhone($raw)
    {
        $digits = preg_replace('/\D/', '', (string)$raw);
        if (strlen($digits) === 11 && $digits[0] === '1') {
            $digits = substr($digits, 1);
        }
        return strlen($digits) === 10 ? $digits : null;
    }

    private function formatPhone($digits)
    {
        $clean = preg_replace('/\D/', '', (string)$digits);
        if (strlen($clean) === 10) {
            return '(' . substr($clean, 0, 3) . ') ' . substr($clean, 3, 3) . '-' . substr($clean, 6, 4);
        }
        return (string)$digits;
    }

    private function splitCsv($value)
    {
        if (empty($value)) {
            return [];
        }
        $items = array_map('trim', explode(',', (string)$value));
        return array_values(array_filter($items, function ($item) {
            return $item !== '';
        }));
    }
}

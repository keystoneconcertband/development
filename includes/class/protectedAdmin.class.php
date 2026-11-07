<?php
// This class is for methods which must be protected, so use must have a valid session to run these queries
// member is its parent
require_once "member.class.php";
require_once "kcbPublic.db.class.php";

class ProtectedAdmin
{
    private $db;
    private $kcb;
    private $log;

    /* PUBLIC FUNCTIONS */
    public function __construct()
    {
        new Member(true);
        $this->setKcb(new KcbBase());
        $this->setDB(new MemberDB());
        $this->log = new Log();

        if (!$this->validAdmin()) {
            header('Location: /members/adminAccess.php');
            exit;
        }
    }

    // Gets the current member by uid
    public function getMemberRecord($uid)
    {
        if (!$this->validAdmin()) {
            return "access denied.";
        }

        return $this->getDb()->getMemberRecord($uid);
    }

    // Gets the login stats for the website with human-readable labels
    public function getLoginStats()
    {
        if (!$this->validAdmin()) {
            return "access denied.";
        }

        $records = $this->getDb()->getLoginStats();
        if (!is_array($records)) {
            return [];
        }

        foreach ($records as &$row) {
            $formatted = self::formatLoginStatus($row['valid'] ?? '');
            $row['status_label'] = $formatted['label'];
            $row['status_badge'] = $formatted['badge'];
            $row['status_desc']  = $formatted['description'];
        }
        unset($row);

        return $records;
    }

    public static function formatLoginStatus($raw)
    {
        $raw = is_string($raw) ? trim($raw) : '';

        if ($raw === 'valid' || $raw === '1' || $raw === 'Success') {
            return ['label' => 'Success', 'badge' => 'success', 'description' => 'Successful login'];
        }
        if ($raw === 'auth_required_no_cookie') {
            return ['label' => 'Code Sent', 'badge' => 'info', 'description' => 'Verification code emailed'];
        }
        if ($raw === 'auth_cd_not_expired') {
            return ['label' => 'Active Code Pending', 'badge' => 'info', 'description' => 'Active code still valid'];
        }
        if ($raw === 'auth_failed_invalid_cookie') {
            return ['label' => 'Saved Cookie Expired', 'badge' => 'warning', 'description' => 'Remember-me session expired; new code emailed'];
        }
        if (str_starts_with($raw, 'resend_throttle__')) {
            $remaining = substr($raw, strlen('resend_throttle__'));
            return ['label' => 'Resend Throttled', 'badge' => 'secondary', 'description' => "Requested code too quickly ({$remaining}s remaining)"];
        }
        if (str_starts_with($raw, 'over_max_requests__')) {
            $timeout = substr($raw, strlen('over_max_requests__'));
            return ['label' => 'Account Locked', 'badge' => 'danger', 'description' => "Locked due to 3+ failed attempts (until {$timeout})"];
        }
        if ($raw === 'auth_invalid') {
            return ['label' => 'Incorrect Code', 'badge' => 'danger', 'description' => 'Incorrect 6-digit code entered'];
        }
        if ($raw === 'auth_old') {
            return ['label' => 'Code Expired', 'badge' => 'warning', 'description' => 'Code expired; new code emailed'];
        }
        if ($raw === 'invalid') {
            return ['label' => 'Unregistered / Inactive', 'badge' => 'danger', 'description' => 'Email not registered or account disabled'];
        }
        if ($raw === 'invalid_pending') {
            return ['label' => 'Pending Approval', 'badge' => 'warning', 'description' => 'Member application pending officer approval'];
        }
        if ($raw === 'invalid_request') {
            return ['label' => 'Invalid Request', 'badge' => 'secondary', 'description' => 'Missing or invalid email address'];
        }
        if ($raw === 'db_error') {
            return ['label' => 'Database Error', 'badge' => 'danger', 'description' => 'Database connection or query failure'];
        }

        return ['label' => $raw ?: 'Unknown', 'badge' => 'secondary', 'description' => $raw ?: 'Unknown status'];
    }

    public function addHomepageMessage($title, $message, $message_type, $start_dt, $end_dt)
    {
        if (!$this->validAdmin()) {
            return "access denied.";
        }

        if ($this->getDb()->addHomepageMessage($title, $message, $message_type, $start_dt, $end_dt, $_SESSION["email"])) {
            KCBPublicDb::clearHomepageMessagesCache();
            $this->getKcb()->logNotice("Admin added homepage message", ['title' => $title, 'admin' => $_SESSION['email'] ?? '']);
            return "success";
        }
        $this->getKcb()->logError("Failed to add homepage message", ['title' => $title]);
    }

    public function editHomepageMessage($uid, $title, $message, $message_type, $start_dt, $end_dt)
    {
        if (!$this->validAdmin()) {
            return "access denied.";
        }

        if ($this->getDb()->editHomepageMessage($uid, $title, $message, $message_type, $start_dt, $end_dt, $_SESSION["email"])) {
            KCBPublicDb::clearHomepageMessagesCache();
            $this->getKcb()->logNotice("Admin updated homepage message", ['uid' => $uid, 'title' => $title, 'admin' => $_SESSION['email'] ?? '']);
            return "success";
        }
        $this->getKcb()->logError("Failed to update homepage message", ['uid' => $uid, 'title' => $title]);
    }

    public function getHomepageMessages()
    {
        if (!$this->validAdmin()) {
            return "access denied.";
        }

        return $this->getDb()->getHomepageMessages();
    }

    public function getHomepageMessageRecord($uid)
    {
        if (!$this->validAdmin()) {
            return "access denied.";
        }

        return $this->getDb()->getHomepageMessageRecord($uid);
    }

    public function homepageMessageDateConflictCheck($date)
    {
        if (!$this->validAdmin()) {
            return "access denied.";
        }

        return $this->getDb()->homepageMessageDateConflictCheck($date);
    }

    public function getSchedules()
    {
        if (!$this->validAdmin()) {
            return "access denied.";
        }

        return $this->getDb()->getSchedules();
    }

    public function getScheduleRecord($uid)
    {
        if (!$this->validAdmin()) {
            return "access denied.";
        }

        return $this->getDb()->getScheduleRecord($uid);
    }

    public function addSchedule($title, $concertBegin, $pants, $chair, $address)
    {
        if (!$this->validAdmin()) {
            return "access denied.";
        }

        if ($this->getDb()->addSchedule($title, $concertBegin, $pants, $chair, $address, $_SESSION['email'] ?? '')) {
            KCBPublicDb::clearScheduleCache();
            $this->getKcb()->logNotice("Admin added concert schedule", ['title' => $title, 'date' => $concertBegin, 'admin' => $_SESSION['email'] ?? '']);
            return "success";
        }
        $this->getKcb()->logError("Failed to add concert schedule", ['title' => $title]);
    }

    public function editSchedule($uid, $title, $concertBegin, $pants, $chair, $address)
    {
        if (!$this->validAdmin()) {
            return "access denied.";
        }

        if ($this->getDb()->editSchedule($uid, $title, $concertBegin, $pants, $chair, $address, $_SESSION['email'] ?? '')) {
            KCBPublicDb::clearScheduleCache();
            $this->getKcb()->logNotice("Admin updated concert schedule", ['uid' => $uid, 'title' => $title, 'admin' => $_SESSION['email'] ?? '']);
            return "success";
        }
        $this->getKcb()->logError("Failed to update concert schedule", ['uid' => $uid, 'title' => $title]);
    }

    // Gets the current active members
    public function getPendingMembers()
    {
        if (!$this->validAdmin()) {
            return "access denied.";
        }

        return $this->getDb()->getPendingMembers();
    }

    public function addPendingMember($uid, $mbrArray)
    {
        if (!$this->validAdmin()) {
            return "access denied.";
        }

        $retValue = "success";
        $updateUser = $_SESSION["email"];
        $instrument = "";
        $email = "";

        if (isset($_POST['instrument'])) {
            $instrument = $mbrArray['instrument'];
        }

        if (isset($_POST['email'])) {
            $email = $mbrArray['email'];
        }

        try {
            $this->getDb()->beginTransaction();

            if ($this->getDb()->updatePendingMember($uid, $mbrArray, $updateUser)) {
                if ($this->getDb()->insertAddress($uid, $mbrArray, $updateUser)) {
                    if ($this->updateEmails($uid, $email, true)) {
                        if ($this->updateInstruments($uid, $instrument)) {
                            $this->getDb()->executeTransaction();
                            MemberDB::clearPublicMembersCache();
                        } else {
                            $this->getDb()->rollBackTransaction();
                            $retValue = "update_instrument_error";
                        }
                    } else {
                        $this->getDb()->rollBackTransaction();
                        $retValue = "update_email_error";
                    }
                } else {
                    $this->getDb()->rollBackTransaction();
                    $retValue = "update_address_error";
                }
            } else {
                $this->getDb()->rollBackTransaction();
                $retValue = "activate_member_error";
            }
        } catch (Exception $e) {
            $this->getKcb()->logMessage($e->getMessage());
            $this->getDb()->rollBackTransaction();
            $retValue = "db_error";
        }

        return $retValue;
    }

	public function deleteMember($uid, $deleteEmailAddress) {
		if ($this->validAdmin()) {
			$updateUser = $_SESSION["email"];
			
			try {
				$this->getDb()->beginTransaction();

				if($this->getDb()->delAllEmails($uid)) {
                    if($this->getDb()->deleteMember($uid, $updateUser)) {
                        $this->getDb()->executeTransaction();
                        MemberDB::clearPublicMembersCache();
                        $this->getKcb()->logNotice("Admin deleted member record", ['uid' => $uid, 'admin' => $updateUser]);
                        $retValue = "success";
					}
					else {
						$this->getDb()->rollBackTransaction();
						$this->getKcb()->logError("Failed to delete member record", ['uid' => $uid, 'admin' => $updateUser]);
						$retValue = "delete_member_error";
					}
				}
				else {
					$this->getDb()->rollBackTransaction();
					$this->getKcb()->logError("Failed to delete member emails during deletion", ['uid' => $uid, 'admin' => $updateUser]);
					$retValue = "delete_email_error";
				}
			}
			catch(Exception $e) {
				$this->getKcb()->logError("Exception during member deletion", ['exception' => $e, 'uid' => $uid]);
				$this->getDb()->rollBackTransaction();
				$retValue = "db_error";
			}
			
			return $retValue;
		}
		else {
			return "Access Denied";
		}
	}
	
    /* PRIVATE FUNCTIONS */
    private function validAdmin()
    {
        $validSession = false;
        if (isset($_SESSION['accountType']) && $_SESSION['accountType'] !== "") {
            if($_SESSION['accountType'] === 1 || $_SESSION['accountType'] === 2) {
                $validSession = true;
            }
        }

        if (!$validSession) {
            $this->getKcb()->logWarning("Unauthorized administrative access attempt", [
                'user'         => $_SESSION['email'] ?? 'anonymous',
                'account_type' => $_SESSION['accountType'] ?? 'none',
                'uri'          => $_SERVER['REQUEST_URI'] ?? 'unknown',
            ]);
        }

        return $validSession;
    }

    private function updateEmails($uid, $emailArray, $delEmail)
    {
        $result = true;
        $emails = $this->getDb()->getEmailAddresses($uid);

        // Convert array of arrays to single array this can handle
        $currEmails = array();
        foreach ($emails as $email) {
            if ($email['email_address'] !== '') {
                $currEmails[] = $email['email_address'];
            }
        }

        $newEmails = array();
        foreach ($emailArray as $eml) {
            if ($eml !== '') {
                $newEmails[] = $eml;
            }
        }

        // Populate arrays with differences
        $emailsToAdd = array_diff($newEmails, $currEmails);
        $emailsToDel = array_diff($currEmails, $newEmails);

        foreach ($emailsToAdd as $value) {
            if ($value !== "") {
                try {
                    $this->kcb->sendEmail('webmaster@keystoneconcertband.com','Add email: ' . $value, 'KCB Email Update [Add]');
                    $result = $this->getDb()->addEmail($value, $uid, $_SESSION["email"]);
                } catch (Exception $e) {
                    $this->getKcb()->logMessage($e->getMessage());
                    $result = false;
                }
            }
        }

        // No need to run if we had a failure above...
        if ($result) {
            foreach ($emailsToDel as $value) {
                if ($value !== "") {
                    try {
                        $this->kcb->sendEmail('webmaster@keystoneconcertband.com','Delete email: ' . $value, 'KCB Email Update [Delete]');
                        if ($delEmail) {
                            $result = $this->getDb()->delEmail($value, $uid);
                        } else {
                            $result = $this->getDb()->deactivateEmail($value, $uid, $_SESSION["email"]);
                        }
                    } catch (Exception $e) {
                        $this->getKcb()->logMessage($e->getMessage());
                        $result = false;
                    }
                }
            }
        }

        return $result;
    }

    private function updateInstruments($uid, $instrumentArray)
    {
        $result = true;
        $instruments = $this->getDb()->getMemberInstruments($uid);

        // Convert array of arrays to single array this can handle
        $currInstruments = array();
        foreach ((array)$instruments as $instr) {
            if ($instr['instrument'] !== '') {
                $currInstruments[] = $instr['instrument'];
            }
        }

        $newInstruments = array();
        foreach ((array)$instrumentArray as $instr) {
            if ($instr !== '') {
                $newInstruments[] = $instr;
            }
        }

        // Populate arrays with differences
        $instrumentsToAdd = array_diff($newInstruments, $currInstruments);
        $instrumentsToDel = array_diff($currInstruments, $newInstruments);

        foreach ($instrumentsToAdd as $value) {
            if ($value !== "") {
                try {
                    $result = $this->getDb()->addInstrument($value, $uid, $_SESSION["email"]);
                } catch (Exception $e) {
                    $this->getKcb()->logMessage($e->getMessage());
                    $result = false;
                }
            }
        }

        // No need to run if we had a failure above...
        if ($result) {
            foreach ($instrumentsToDel as $value) {
                if ($value !== "") {
                    try {
                        $result = $this->getDb()->delInstrument($value, $uid);
                    } catch (Exception $e) {
                        $this->getKcb()->logMessage($e->getMessage());
                        $result = false;
                    }
                }
            }
        }

        return $result;
    }

    private function getDb()
    {
        return $this->db;
    }

    private function setDb($db)
    {
        $this->db = $db;
    }
    private function getKcb()
    {
        return $this->kcb;
    }

    private function setKcb($kcb)
    {
        $this->kcb = $kcb;
    }
}

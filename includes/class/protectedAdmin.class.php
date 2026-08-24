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
            header('Location: adminAccess.php');
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

    // Gets the login stats for the website
    public function getLoginStats()
    {
        if (!$this->validAdmin()) {
            return "access denied.";
        }

        return $this->getDb()->getLoginStats();
    }

    public function addHomepageMessage($title, $message, $message_type, $start_dt, $end_dt)
    {
        if (!$this->validAdmin()) {
            return "access denied.";
        }

        if ($this->getDb()->addHomepageMessage($title, $message, $message_type, $start_dt, $end_dt, $_SESSION["email"])) {
            KCBPublicDb::clearHomepageMessagesCache();
            return "success";
        }
    }

    public function editHomepageMessage($uid, $title, $message, $message_type, $start_dt, $end_dt)
    {
        if (!$this->validAdmin()) {
            return "access denied.";
        }

        if ($this->getDb()->editHomepageMessage($uid, $title, $message, $message_type, $start_dt, $end_dt, $_SESSION["email"])) {
            KCBPublicDb::clearHomepageMessagesCache();
            return "success";
        }
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
            return "success";
        }
    }

    public function editSchedule($uid, $title, $concertBegin, $pants, $chair, $address)
    {
        if (!$this->validAdmin()) {
            return "access denied.";
        }

        if ($this->getDb()->editSchedule($uid, $title, $concertBegin, $pants, $chair, $address, $_SESSION['email'] ?? '')) {
            KCBPublicDb::clearScheduleCache();
            return "success";
        }
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
                        $retValue = "success";
					}
					else {
						$this->getDb()->rollBackTransaction();
						$retValue = "delete_member_error";
					}
				}
				else {
					$this->getDb()->rollBackTransaction();
					$retValue = "delete_email_error";
				}
			}
			catch(Exception $e) {
				$this->getKcb()->logMessage($e->getMessage());
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

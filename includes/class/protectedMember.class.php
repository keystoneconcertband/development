<?php
// This class is for methods which must be protected, so use must have a valid session to run these queries
// member is its parent
require_once "member.class.php";
require_once "exchangeContactService.class.php";
	
class ProtectedMember {
	private $db;
	private $kcb;
	private $exchangeContactService;
	
	/* PUBLIC FUNCTIONS */
	public function __construct() {			
		new Member(true);
		$this->setKcb(new KcbBase());
		$this->setDB(new MemberDB());
	}
	
	// Gets the current member by email
	public function getMember($email) {
		if(isset($_SESSION['email']) && $_SESSION['email'] !== '') {
			return $this->getDb()->getMember($email);
		}
		else {
			return "Access Denied";
		}
	}
	
	// Gets the current member by uid
	public function getMemberRecord($uid) {
		if(isset($_SESSION['email']) && $_SESSION['email'] !== '') {
			return $this->getDb()->getMemberRecord($uid);
		}
		else {
			return "Access Denied";
		}
	}
	
	// Gets all the email addresses for the user
	public function getEmailAddresses($uid) {
		if(isset($_SESSION['email']) && $_SESSION['email'] !== '') {
			return $this->getDb()->getEmailAddresses($uid);
		}
		else {
			return "Access Denied";
		}
	}
	
	// Gets all the instruments for the user
	public function getMemberInstruments($uid) {
		if(isset($_SESSION['email']) && $_SESSION['email'] !== '') {
			return $this->getDb()->getMemberInstruments($uid);
		}
		else {
			return "Access Denied";
		}
	}
	
	// Gets the current active members
	public function getActiveMembers() {
		if(isset($_SESSION['email']) && $_SESSION['email'] !== '') {
			return $this->getDb()->getActiveMembers();
		}
		else {
			return "Access Denied";
		}
	}
	
	// Gets the current active members
	public function getInactiveMembers() {
		if ($this->validAdmin()) {
			return $this->getDb()->getInactiveMembers();
		}
		else {
			return "Access Denied";
		}
	}
	
	// Gets the current active members
	public function getPendingMembers() {
		if(isset($_SESSION['email']) && $_SESSION['email'] !== '') {
			return $this->getDb()->getPendingMembers();
		}
		else {
			return "Access Denied";
		}
	}
	
	public function addMember($mbrArray) {
		if ($this->validAdmin()) {
			$retValue = "success";
			$updateUser = $_SESSION["email"];
			$instrument = "";
			$email = "";
			
			if(isset($_POST['instrument'])) {
				$instrument = $mbrArray['instrument'];
			}
			
			if(isset($_POST['email'])) {
				$email = $mbrArray['email'];
			}

			try {
				$this->getDb()->beginTransaction();
				$emailChanges = ['added' => [], 'removed' => []];
				
				$uid = $this->getDb()->insertMember($mbrArray, $updateUser);
				
				if($uid !== 0) {
					if($this->upsertAddress($uid, $mbrArray, $updateUser)) {
						if($this->updateEmails($uid, $email, true, false, $emailChanges, false)) {
							if($this->updateInstruments($uid, $instrument)) {
								$this->getDb()->executeTransaction();
								MemberDB::clearPublicMembersCache();
								$this->getKcb()->logNotice("New band member profile created", ['uid' => $uid, 'user' => $updateUser]);
								$this->getExchangeContactService()->syncOrNotifyWebmaster(
									$uid,
									$emailChanges['added'],
									[],
									$mbrArray['firstName'] ?? '',
									$mbrArray['lastName'] ?? '',
									$updateUser
								);
							}
							else {
								$this->getDb()->rollBackTransaction();
								$retValue = "add_instrument_error";								
								$this->getKcb()->logError("Failed to add instruments for member", ['uid' => $uid]);
							}
						}
						else {
							$this->getDb()->rollBackTransaction();
							$retValue = "add_email_error";
							$this->getKcb()->logError("Failed to add email for member", ['uid' => $uid]);
						}
					}
					else {
						$this->getDb()->rollBackTransaction();
						$retValue = "add_address_error";
						$this->getKcb()->logError("Failed to add address for member", ['uid' => $uid]);
					}
				}
				else {
					$this->getDb()->rollBackTransaction();
					$retValue = "add_member_error";
					$this->getKcb()->logError("Failed to insert member base record");
				}
			}
			catch(Exception $e) {
				$this->getKcb()->logError("Exception while adding member", ['exception' => $e]);
				$this->getDb()->rollBackTransaction();
				$retValue = "db_error";
			}
			
			return $retValue;
		}
		else {
			return "Access Denied";
		}
	}
	
	// Update members
	public function updateMember($uid, $mbrArray) {
		if(isset($_SESSION['email']) && $_SESSION['email'] !== '') {
			$retValue = "success";
			$updateUser = $_SESSION["email"];
			$instrument = "";
			$email = "";
			
			if(isset($_POST['instrument'])) {
				$instrument = $mbrArray['instrument'];
			}
			
			if(isset($_POST['email'])) {
				$email = $mbrArray['email'];
			}
			
			try {
				$this->getDb()->beginTransaction();
				$emailChanges = ['added' => [], 'removed' => []];
								
				if($this->getDb()->updateMember($uid, $mbrArray, $updateUser)) {
					if($this->upsertAddress($uid, $mbrArray, $updateUser)) {
						if($this->updateEmails($uid, $email, true, false, $emailChanges, false)) {
							if($this->updateInstruments($uid, $instrument)) {
								$this->getDb()->executeTransaction();
								MemberDB::clearPublicMembersCache();
								$this->getKcb()->logNotice("Band member profile updated", ['uid' => $uid, 'user' => $updateUser]);
								$memberRecord = $this->getDb()->getMemberRecord($uid);
								if (ExchangeContactService::isMemberActive($memberRecord)
									&& (!empty($emailChanges['added']) || !empty($emailChanges['removed']))) {
									$this->getExchangeContactService()->syncOrNotifyWebmaster(
										$uid,
										$emailChanges['added'],
										$emailChanges['removed'],
										$memberRecord['firstName'] ?? ($mbrArray['firstName'] ?? ''),
										$memberRecord['lastName'] ?? ($mbrArray['lastName'] ?? ''),
										$updateUser
									);
								}
							}
							else {
								$this->getDb()->rollBackTransaction();
								$retValue = "update_instrument_error";								
								$this->getKcb()->logError("Failed to update instruments for member", ['uid' => $uid]);
							}
						}
						else {
							$this->getDb()->rollBackTransaction();
							$retValue = "update_email_error";
							$this->getKcb()->logError("Failed to update email for member", ['uid' => $uid]);
						}
					}
					else {
						$this->getDb()->rollBackTransaction();
						$retValue = "update_address_error";
						$this->getKcb()->logError("Failed to update address for member", ['uid' => $uid]);
					}
				}
				else {
					$this->getDb()->rollBackTransaction();
					$retValue = "update_member_error";
					$this->getKcb()->logError("Failed to update base member record", ['uid' => $uid]);
				}
			}
			catch(Exception $e) {
				$this->getKcb()->logError("Exception while updating member", ['exception' => $e, 'uid' => $uid]);
				$this->getDb()->rollBackTransaction();
				$retValue = "db_error";
			}
			
			return $retValue;
		}
		else {
			return "Access Denied";
		}
	}
		
	public function removeMember($uid, $deleteEmailAddress) {
		if ($this->validAdmin()) {
			$retValue = "success";
			$updateUser = $_SESSION["email"];
			$memberRecord = $this->getDb()->getMemberRecord($uid);
			$wasActive = ExchangeContactService::isMemberActive($memberRecord);
			$activeEmails = $wasActive ? $this->getDb()->getActiveEmailList($uid) : [];
			
			try {
				$this->getDb()->beginTransaction();
				$emailChanges = ['added' => [], 'removed' => []];
				
				if($this->getDb()->removeMember($uid, $updateUser)) {
					if($this->updateEmails($uid, array(), $deleteEmailAddress, false, $emailChanges, false)) {
						$this->getDb()->executeTransaction();
						MemberDB::clearPublicMembersCache();
						$emailsToSync = $wasActive ? $activeEmails : [];
						if (!empty($emailsToSync)) {
							$this->getExchangeContactService()->syncOrNotifyWebmaster(
								$uid,
								[],
								$emailsToSync,
								$memberRecord['firstName'] ?? '',
								$memberRecord['lastName'] ?? '',
								$updateUser
							);
						}
					}
					else {
						$this->getDb()->rollBackTransaction();
						$retValue = "remove_email_error";
					}
				}
				else {
					$this->getDb()->rollBackTransaction();
					$retValue = "remove_member_error";
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
	
	public function reactivateMember($uid, $mbrArray) {
		$updateUser = $_SESSION["email"];
		$retVal = $this->getDb()->reactivateMember($uid, $mbrArray, $updateUser);
		
		if($retVal) {
			MemberDB::clearPublicMembersCache();
			$memberRecord = $this->getDb()->getMemberRecord($uid);
			$emailsToSync = $this->getDb()->getActiveEmailList($uid);
			if (!empty($emailsToSync)) {
				$this->getExchangeContactService()->syncOrNotifyWebmaster(
					$uid,
					$emailsToSync,
					[],
					$memberRecord['firstName'] ?? ($mbrArray['firstName'] ?? ''),
					$memberRecord['lastName'] ?? ($mbrArray['lastName'] ?? ''),
					$updateUser
				);
			}
			return "success";
		}
		else {
			return "reactivate_member_error";
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
	
	// Determines whether to add or update an email address
	private function upsertAddress($uid, $mbrArray, $updateUser) {
		// check whether address exists
		$mbrAddressCount = $this->getDb()->getMemberAddressCount($uid);
		$upsertValue = false;
		
		if($mbrAddressCount > 0) {
			$upsertValue = $this->getDb()->updateAddress($uid, $mbrArray, $updateUser);
		}
		else {
			$upsertValue = $this->getDb()->insertAddress($uid, $mbrArray, $updateUser);
		}
		
		return $upsertValue;
	}
	
	// TEST PENDING MEMBERS
	private function updateEmails($uid, $emailArray, $delEmail, $pendingUser, &$emailChanges = null, $sendNotificationEmails = true) {
		$result = true;
		$emailChanges = ['added' => [], 'removed' => []];
		$emails = $this->getDb()->getEmailAddresses($uid);

		// Convert array of arrays to single array this can handle	
		$currEmails = array();
		foreach((array)$emails as $email) {
			$addr = trim((string)($email['email_address'] ?? ''));
			if($addr !== '') {
				$currEmails[] = $addr;
			}
		}
		
		$newEmails = array();
		foreach((array)$emailArray as $value) {
			$value = trim((string)$value);
			if($value !== '') {
				// Store email address in array		
				$newEmails[] = $value;

				// Whatever is passed in for pending users needs to be added to the email list.
				// Otherwise if the user changed nothing, it wouldn't be added since it was already in the 
				// database from their initial inquiry.					
				if($pendingUser) {
					$emailChanges['added'][] = $value;
					if($sendNotificationEmails) {
						try {
							$this->kcb->sendEmail("webmaster@keystoneconcertband.com", "Add email: " . $value, "KCB Email Update [Pending]");
						}
						catch(Exception $e) {
							$this->getKcb()->logMessage($e->getMessage());
							return false;
						}
					}
				}
			}
		}

		// No need to run if we had a failure above...
		if($result) {
			// Populate arrays with differences
			$emailsToAdd = array_diff($newEmails, $currEmails);
			$emailsToDel = array_diff($currEmails, $newEmails);
				
			foreach ($emailsToAdd as $value) {
				if($value !== "") {					
					try {
						// Pending users were added above, so no need to re-add again.
						if(!$pendingUser) {
							$emailChanges['added'][] = $value;
							if($sendNotificationEmails) {
								$this->kcb->sendEmail('webmaster@keystoneconcertband.com','Add email: ' . $value, 'KCB Email Update [Add]');
							}
						}
						$result = $this->getDb()->addEmail($value, $uid, $_SESSION["email"]);						
					}
					catch(Exception $e) {
						$this->getKcb()->logMessage($e->getMessage());
						return false;
					}
				}
			}
		}

		// No need to run if we had a failure above...
		if($result) {
			foreach ($emailsToDel as $value) {
				if($value !== "") {			            
					try {							
						if($delEmail) {
							$result = $this->getDb()->delEmail($value, $uid);
						}
						else {
							$result = $this->getDb()->deactivateEmail($value, $uid, $_SESSION["email"]);
						}

						$emailChanges['removed'][] = $value;
						if($sendNotificationEmails) {
							$this->kcb->sendEmail("webmaster@keystoneconcertband.com", "Remove email: " . $value, "KCB Email Update [Remove]");
						}
					}
					catch(Exception $e) {
						$this->getKcb()->logMessage($e->getMessage());
						return false;
					}
				}
			}
		}
		
		return $result;
	}
	
	private function updateInstruments($uid, $instrumentArray) {
		$result = true;					
		$instruments = $this->getDb()->getMemberInstruments($uid);
				
		// Convert array of arrays to single array this can handle	
		$currInstruments = array();
		foreach((array)$instruments as $instr) {
			if($instr['instrument'] !== '') {
				$currInstruments[] = $instr['instrument'];
			}
		}
		
		$newInstruments = array();
		foreach((array)$instrumentArray as $instr) {
			if($instr !== '') {				
				$newInstruments[] = $instr;
			}
		}
					
		// Populate arrays with differences
		$instrumentsToAdd = array_diff($newInstruments, $currInstruments);
		$instrumentsToDel = array_diff($currInstruments, $newInstruments);
			
		foreach ($instrumentsToAdd as $value) {
			if($value !== "") {
				try {
					$result = $this->getDb()->addInstrument($value, $uid, $_SESSION["email"]);						
				}
				catch(Exception $e) {
					$this->getKcb()->logMessage($e->getMessage());
					$result = false;
				}
			}
		}
		
		// No need to run if we had a failure above...
		if($result) {
			foreach ($instrumentsToDel as $value) {
				if($value !== "") {
					try {
						$result = $this->getDb()->delInstrument($value, $uid);	
					}
					catch(Exception $e) {
						$this->getKcb()->logMessage($e->getMessage());
						$result = false;
					}
				}
			}
		}
		
		return $result;
	}
	
	private function getDb() {
		return $this->db;
	}
	
	private function setDb($db) {
		$this->db = $db;
	}
	private function getKcb() {
		return $this->kcb;
	}
	
	private function setKcb($kcb) {
		$this->kcb = $kcb;
	}

	private function getExchangeContactService() {
		if ($this->exchangeContactService === null) {
			$this->exchangeContactService = new ExchangeContactService($this->getKcb(), $this->getDb());
		}
		return $this->exchangeContactService;
	}
}
?>

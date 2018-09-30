<?
	// This class is for methods which must be protected, so use must have a valid session to run these queries
	// member is its parent
 	include_once("member.class.php");
 	include_once("member.db.class.php");
 	 	
	class ProtectedAdmin {
		private $db;
		private $kcb;
		
		/* PUBLIC FUNCTIONS */
		public function __construct() {			
			new Member(true);
			$this->setKcb(new KcbBase());
			$this->setDB(new MemberDB());
			
			if(!$this->validAdmin()) {
				header('Location: adminAccess.php');
			}
		}
		
		// Gets the current member by uid
		public function getMemberRecord($uid) {
			if(!$this->validAdmin()) {
				return "access denied.";
			}

			return $this->getDb()->getMemberRecord($uid);
		}
		
		// Gets the login stats for the website
		public function getLoginStats() {
			if(!$this->validAdmin()) {
				return "access denied.";
			}

			return $this->getDb()->getLoginStats();
		}
		
		// Send text message to the band
		public function sendTextMessages($message) {
			if(!$this->validAdmin()) {
				return "access denied.";
			}

			// Get list of members
			$activeMembers = $this->getDb()->getActiveMembers();
			$emailList = "";
			
			// Take the array of members and set the text list so it can email the phone as a text message.
			foreach($activeMembers as $activeMember) {
				if(isset($activeMember['text']) && $activeMember['text'] !== "") {
					if($emailList === "") {
						$emailList .= $activeMember['text'] . "@" . $activeMember['carrier'];
					}
					else {
						$emailList .= ", " . $activeMember['text'] . "@" . $activeMember['carrier'];			
					}
				}
			}
			
			// Send email to the phone as a text message.
			try {
				$title = "KCB Msg";
				$headers[] = 'From: KCB<web@keystoneconcertband.com>';
						
				return mail($emailList, $title, $message, implode("\r\n", $headers));	
			}
			catch(Exception $e) {
				$this->getKcb()->LogError($e->getMessage());
				return false;
			}
		}

		public function addHomepageMessage($title, $message, $message_type, $start_dt, $end_dt) {
			if(!$this->validAdmin()) {
				return "access denied.";
			}
			
			if($this->getDb()->addHomepageMessage($title, $message, $message_type, $start_dt, $end_dt, $_SESSION["email"])) {
				return "success";
			}
		}
		
		public function editHomepageMessage($uid, $title, $message, $message_type, $start_dt, $end_dt) {
			if(!$this->validAdmin()) {
				return "access denied.";
			}
			
			if($this->getDb()->editHomepageMessage($uid, $title, $message, $message_type, $start_dt, $end_dt, $_SESSION["email"])) {
				return "success";
			}
		}
				
		public function getHomepageMessages() {
			if(!$this->validAdmin()) {
				return "access denied.";
			}
			
			return $this->getDb()->getHomepageMessages();
		}
		
		public function getHomepageMessageRecord($uid) {
			if(!$this->validAdmin()) {
				return "access denied.";
			}
			
			return $this->getDb()->getHomepageMessageRecord($uid);
		}
		
		public function homepageMessageDateConflictCheck($date) {
			if(!$this->validAdmin()) {
				return "access denied.";
			}
			
			return $this->getDb()->homepageMessageDateConflictCheck($date);
		}
		
		// Gets the current active members
		public function getPendingMembers() {
			if(!$this->validAdmin()) {
				return "access denied.";
			}

			return $this->getDb()->getPendingMembers();
		}
		
		public function addPendingMember($uid, $mbrArray) {
			if(!$this->validAdmin()) {
				return "access denied.";
			}

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
								
				if($this->getDb()->updatePendingMember($uid, $mbrArray, $updateUser)) {
					if($this->getDb()->insertAddress($uid, $mbrArray, $updateUser)) {
						if($this->updateEmails($uid, $email, true)) {
							if($this->updateInstruments($uid, $instrument)) {
								$this->getDb()->executeTransaction();							
							}
							else {
								$this->getDb()->rollBackTransaction();
								$retValue = "update_instrument_error";								
							}
						}
						else {
							$this->getDb()->rollBackTransaction();
							$retValue = "update_email_error";
						}
					}
					else {
						$this->getDb()->rollBackTransaction();
						$retValue = "update_address_error";
					}
				}
				else {
					$this->getDb()->rollBackTransaction();
					$retValue = "update_member_error";
				}
			}
			catch(Exception $e) {
				$this->getKcb()->LogError($e->getMessage());
				$this->getDb()->rollBackTransaction();
				$retValue = "db_error";
			}
			
			return $retValue;
		}
		
		public function removeMember($uid, $deleteEmailAddress) {
			if(!$this->validAdmin()) {
				return "access denied.";
			}

			$retValue = "success";
			$updateUser = $_SESSION["email"];
			
			try {
				$this->getDb()->beginTransaction();
				
				if($this->getDb()->removeMember($uid, $updateUser)) {
					if($this->updateEmails($uid, array(), $deleteEmailAddress)) {
						$this->getDb()->executeTransaction();							
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
				$this->getKcb()->LogError($e->getMessage());
				$this->getDb()->rollBackTransaction();
				$retValue = "db_error";
			}
			
			return $retValue;
		}
		
		/* PRIVATE FUNCTIONS */
		private function validAdmin() {
			$validSession = false;
			if(isset($_SESSION['office']) && $_SESSION['office'] !== ""){
				$validSession = true;
			}
			
			return $validSession;			
		}
		
    	private function updateEmails($uid, $emailArray, $delEmail) {
			$result = true;					
			$emails = $this->getDb()->getEmailAddresses($uid);
					
			// Convert array of arrays to single array this can handle	
			$currEmails = array();
			foreach($emails as $email) {
				if($email['email_address'] !== '') {
					$currEmails[] = $email['email_address'];
				}
			}
			
			$newEmails = array();
			foreach($emailArray as $eml) {
				if($eml !== '') {				
					$newEmails[] = $eml;
				}
			}
						
			// Populate arrays with differences
			$emailsToAdd = array_diff($newEmails, $currEmails);
			$emailsToDel = array_diff($currEmails, $newEmails);
				
			foreach ($emailsToAdd as $value) {
				if($value !== "") {
					$header = 'From: '. $value . "\r\n" .
						'X-Mailer: PHP/' . phpversion();
					try {
						mail('member-request@keystoneconcertband.com', '', 'subscribe nodigest address=' . $value, $header);
				    	$result = $this->getDb()->addEmail($value, $uid, $_SESSION["email"]);						
					}
					catch(Exception $e) {
						$this->getKcb()->LogError($e->getMessage());
						$result = false;
					}
			    }
			}
			
			// No need to run if we had a failure above...
			if($result) {
				foreach ($emailsToDel as $value) {
					if($value !== "") {
						$headers = 'From: ' . $value . "\r\n" .
							'X-Mailer: PHP/' . phpversion();
						try {
							mail('member-request@keystoneconcertband.com', '', 'unsubscribe address=' . $value, $header);
							
							if($delEmail) {
								$result = $this->getDb()->delEmail($value, $uid);
							}
							else {
								$result = $this->getDb()->deactivateEmail($value, $uid, $_SESSION["email"]);
							}
						}
						catch(Exception $e) {
							$this->getKcb()->LogError($e->getMessage());
							$result = false;
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
						$this->getKcb()->LogError($e->getMessage());
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
							$this->getKcb()->LogError($e->getMessage());
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
	}
?>
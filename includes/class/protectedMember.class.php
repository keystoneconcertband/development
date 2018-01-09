<?
	// This class is for methods which must be protected, so use must have a valid session to run these queries
	// member is its parent
 	include_once("member.class.php");
 	include_once("member.db.class.php");
 	 	
	class ProtectedMember {
		private $db;
		
		/* PUBLIC FUNCTIONS */
		public function __construct() {			
			new Member(true);
			$this->setDB(new MemberDB());
		}
		
		// Gets the current member by email
		public function getMember($email) {
			return $this->getDb()->getMember($email);
		}
		
		// Gets the current member by uid
		public function getMemberRecord($uid) {
			return $this->getDb()->getMemberRecord($uid);
		}
		
		// Gets all the email addresses for the user
		public function getEmailAddresses($uid) {
			return $this->getDb()->getEmailAddresses($uid);
		}
		
		// Gets all the instruments for the user
		public function getMemberInstruments($uid) {
			return $this->getDb()->getMemberInstruments($uid);
		}
		
		// Gets the current active members
		public function getActiveMembers() {
			return $this->getDb()->getActiveMembers();
		}
		
		public function updateMember($mbrArray) {
			$retValue = "success";
			$uid = $_SESSION["uid"];
			$updateUser = $_SESSION["email"];
			
			try {
				$this->getDb()->beginTransaction();
				
				if($this->getDb()->updateMember($uid, $mbrArray, $updateUser)) {
					if($this->getDb()->updateAddress($uid, $mbrArray, $updateUser)) {
						if($this->updateEmails($uid, $mbrArray['txtEmail'])) {
							if($this->updateInstruments($uid, $mbrArray['chkInstrument'])) {
								$this->getDb()->executeTransaction();							
							}
							else {
								$this->getDb()->rollBackTransaction();
								$retValue = "instrument_update_error";								
							}
						}
						else {
							$this->getDb()->rollBackTransaction();
							$retValue = "email_update_error";
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
				$this->LogError($e->getMessage());
				$this->getDb()->rollBackTransaction();
				$retValue = "db_error";
			}
			
			return $retValue;
		}
		
    	private function updateEmails($uid, $emailArray) {
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
						//mail('majordomo@keystoneconcertband.com', '', 'subscribe members@keystoneconcertband.com ' . $value, $header);
				    	$result = $this->getDb()->addEmail($value, $uid, $_SESSION["email"]);						
					}
					catch(Exception $e) {
						$this->LogError($e->getMessage());
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
							//mail('majordomo@keystoneconcertband.com', '', 'unsubscribe members@keystoneconcertband.com ' . $value, $headers);		
							$result = $this->getDb()->delEmail($value, $uid);	
						}
						catch(Exception $e) {
							$this->LogError($e->getMessage());
							$result = false;
						}
				    }
				}
			}
			
			return $result;
		}
		
		public function updateInstruments($uid, $instrumentArray) {
			$result = true;					
			$instruments = $this->getDb()->getMemberInstruments($uid);
					
			// Convert array of arrays to single array this can handle	
			$currInstruments = array();
			foreach($instruments as $instr) {
				if($instr['instrument'] !== '') {
					$currInstruments[] = $instr['instrument'];
				}
			}
			
			$newInstruments = array();
			foreach($instrumentArray as $instr) {
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
						$this->LogError($e->getMessage());
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
							$this->LogError($e->getMessage());
							$result = false;
						}
				    }
				}
			}
			
			return $result;
		}
		
		public function in_multiarray($elem, $array)
		{
		    while (current($array) !== false) {
		        if (current($array) == $elem) {
		            return true;
		        } elseif (is_array(current($array))) {
		            if ($this->in_multiarray($elem, current($array))) {
		                return true;
		            }
		        }
		        next($array);
		    }
		    return false;
		}

		/* PRIVATE FUNCTIONS */
		private function getDb() {
			return $this->db;
		}
		
		private function setDb($db) {
        	$this->db = $db;
    	}
	}
?>
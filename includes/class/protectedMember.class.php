<?
				ini_set('display_errors',1);
				ini_set('display_startup_errors',1);
				error_reporting(-1);
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
			$retValue = true;
			try {
				$this->getDb()->beginTransaction();
				
				if($this->getDb()->updateMember($mbrArray)) {
					if($updateEmails()) {
						$this->getDb()->executeTransaction();
					}
					else {
						$this->getDb()->rollBack();
						$retValue = false;
					}
				}
				else {
					$this->getDb()->rollBack();
					$retValue = false;
				}
			}
			catch(Exception $e) {
				$this->LogError($e->getMessage());
				$this->getDb()->rollBack();
				$retValue = false;
			}
			
			return $retValue;
		}
		
    	public function updateEmails($emailArray) {
	    	$uid = $_SESSION["uid"];
			$result = false;					
			$emails = getEmailAddresses($uid);
			
			$currEmails = array();
			foreach($emails as $email) {
				$currEmails[] = $email;
			}
		
			// Populate arrays with differences
			$emailsToAdd = array_diff($emailArray, $currEmails);
			$emailsToDel = array_diff($currEmails, $emailArray);
		
			foreach ($emailsToAdd as $value) {
				if($value !== "") {
					$header = 'From: '. $value . "\r\n" .
						'X-Mailer: PHP/' . phpversion();
					try {
						mail('majordomo@keystoneconcertband.com', '', 'subscribe members@keystoneconcertband.com ' . $value, $header);
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
							mail('majordomo@keystoneconcertband.com', '', 'unsubscribe members@keystoneconcertband.com ' . $value, $headers);								    $result = $this->getDb()->delEmail($value, $uid);	
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
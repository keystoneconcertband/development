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
		public function getActiveMembers() {
			return $this->getDb()->getActiveMembers();
		}
		
		public function updateMember($mbrArray) {
			if($this->getDb()->updateMember($mbrArray)) {
				$updateEmails();
			}
			else {
				return false;
			}
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
					mail('majordomo@keystoneconcertband.com', '', 'subscribe members@keystoneconcertband.com ' . $value, $header);

			    	$result = $this->getDb()->addEmail($value, $uid, $_SESSION["email"]);
			    }
			}
			
			foreach ($emailsToDel as $value) {
				if($value !== "") {
					$headers = 'From: ' . $value . "\r\n" .
						'X-Mailer: PHP/' . phpversion();
					mail('majordomo@keystoneconcertband.com', '', 'unsubscribe members@keystoneconcertband.com ' . $value, $headers);

				    $result = $this->getDb()->delEmail($value, $uid);
			    }
			}
			
			return $result;
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
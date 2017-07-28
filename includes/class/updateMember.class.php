<?
	// member is its parent
 	include_once("member.class.php");
 	include_once("member.db.class.php");
 	 	
	class UpdateMember {
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
		
		public function updateMemberInformation() {
			
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
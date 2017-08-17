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
		
		/* PRIVATE FUNCTIONS */
		private function getDb() {
			return $this->db;
		}
		
		private function setDb($db) {
        	$this->db = $db;
    	}
	}
?>
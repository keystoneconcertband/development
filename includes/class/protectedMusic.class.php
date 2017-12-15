<?
	// This class is for methods which must be protected, so use must have a valid session to run these queries
	// member is its parent
 	include_once("member.class.php");
 	include_once("music.db.class.php");
 	 	
	class ProtectedMusic {
		private $db;
		
		/* PUBLIC FUNCTIONS */
		public function __construct() {			
			new Member(true);
			$this->setDB(new MusicDB());
		}
		
		// Gets the current member by email
		public function getMusic() {
			return $this->getDb()->getMusic();
		}
		
		public function getMusicRecord($uid) {
			return $this->getDb()->getMusicRecord($uid);
		}
		
		public function deleteMusic($uid) {
			if(isset($_SESSION['office']) && $_SESSION['office'] !== '') {
				if($this->getDb()->deleteMusic($uid, $_SESSION['email'])) {
					return "success";
				}
				else {
					return "Unable to delete. Was this item already deleted?";
				}
			}
			else {
				return "Access Denied";
			}
		}

		public function editMusic($title, $notes, $link, $last_played, $nbr_plays) {
			
		}
		
		public function addMusic($title, $notes, $link, $last_played) {
			// TODO: need to handle reactivating deleted titles
			if($this->getDb()->checkDupMusic($title) > 0) {
				return "This title already exists.";
			}
			else {
				$retValue = $this->getDb()->addMusic($title, $notes, $link, $last_played, $_SESSION['email']);
				if($retValue === 1) {
					return "success";
				}
				else {
					if($retValue == "db_error") {
						return "Database error. Please try again later.";
					}
					elseif($retValue == "insert_music_error") {
						return "Error inserting values into the music table. Please try again later.";
					}
					elseif($retValue == "insert_music_last_played_error") {
						return "Error inserting values into the music last played table. Please try again later.";
					}
					else {
						return "Unknown error.";
					}
				}
			}
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
<?
 	include_once("db.class.php");
 	 	
	class MusicDB {
		private $db;
		
		/* PUBLIC FUNCTIONS */
		public function __construct() {
			$this->setDB(new Db());
		}
		
		/* Transactions */
		public function beginTransaction() {
			$this->getDb()->beginTransaction();
		}
		
		public function executeTransaction() {
			$this->getDb()->executeTransaction();
		}
		
		public function rollBackTransaction() {
			$this->getDb()->rollBack();
		}
		
		/* SELECT ONLY QUERIES */
		
		// Gets the members by instrument
		public function getMusic() {
			return $this->getDb()->query("SELECT m.title, m.notes, m.music_link, DATE(lp.last_played) as `last_played`, lp.number_plays FROM KCB_music m LEFT OUTER JOIN KCB_music_last_played lp ON m.uid = lp.music_uid");
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
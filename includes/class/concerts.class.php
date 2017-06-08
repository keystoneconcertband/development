<?
 	include_once("kcbBase.class.php");
 	include_once("concerts.db.class.php");
 	
	class Concerts {
		private $db;

		public function __construct() {
			new kcbBase();
			$this->db = new ConcertDb();
		}
				
		public function getCurrentConcert() {
			return $this->db->getCurrentConcert();
		}
		
		public function getConcertSchedule() {
			return $this->db->getConcertSchedule();
		}
	}
?>
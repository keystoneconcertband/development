<?
 	include_once("kcbBase.class.php");
 	include_once("concerts.db.class.php");
 	
	class Concert {
		private $db;

		public function __construct() {
			new KcbBase();
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
<?
 	include_once("db.class.php");
 	
	class concerts {
		private $db;
		
		public function getDb() {
			return $this->db;
		}
		
		public function setDb($db) {
        	$this->db = $db;
    	}
		
		public function __construct() {
			$this->setDB(new Db());
		}
		
		public function getCurrentConcert() {
			$concert = $this->getDb()->row("SELECT concertBegin, concertEnd, Title, pants, chair, address FROM KCB_Schedule WHERE concertBegin >= CURDATE() AND deleted IS NULL ORDER BY concertBegin");
			//$concert = $this->getDb()->row("SELECT concertBegin, concertEnd, Title, pants, chair, address FROM KCB_Schedule WHERE concertBegin >= '2017-01-01' AND deleted IS NULL ORDER BY concertBegin");
			return $concert;
		}
		
		public function getConcertSchedule() {
			$concert = $this->getDb()->query("SELECT concertBegin, concertEnd, Title, pants, chair, address FROM KCB_Schedule WHERE deleted IS NULL AND year(concertBegin) = year(CURRENT_TIMESTAMP) ORDER BY concertBegin");
			//$concert = $this->getDb()->query("SELECT concertBegin, concertEnd, Title, pants, chair, address FROM KCB_Schedule WHERE deleted IS NULL AND year(concertBegin) = year(2017) ORDER BY concertBegin");
			return $concert;
		}
	}
?>
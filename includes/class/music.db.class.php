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
			return $this->getDb()->query("SELECT m.uid, m.title, m.notes, m.music_link, DATE(lp.last_played) as last_played, lp.number_plays FROM KCB_music m LEFT OUTER JOIN KCB_music_last_played lp ON m.uid = lp.music_uid WHERE m.actv_flg = 1");
		}

		public function getMusicRecord($uid) {
			$this->getDb()->bind('uid', $uid);
			return $this->getDb()->row("SELECT m.title, m.notes, m.music_link, DATE(lp.last_played) as last_played, lp.number_plays FROM KCB_music m LEFT OUTER JOIN KCB_music_last_played lp ON m.uid = lp.music_uid WHERE m.uid = :uid");
		}

		public function deleteMusic($uid, $user_id) {
			$this->getDb()->bind('uid', $uid);
			$this->getDb()->bind('user_id', $user_id);
			
			return $this->getDb()->query("UPDATE KCB_music SET actv_flg = 0, lst_tran_dt_tm=now(), lst_updtd_by=:user_id WHERE uid = :uid");
		}

		/* UPDATE QUERIES */

		public function addMusic($title, $notes, $link, $last_played, $user_id) {
			$retValue = "add_music_error";
						
			try {
				$this->beginTransaction();

				$this->getDb()->bind('title', $title);
				$this->getDb()->bind('notes', $notes);
				$this->getDb()->bind('link', $link);
				$this->getDb()->bind('user_id', $user_id);
				$this->getDb()->bind('user_id2', $user_id);
	
				$this->getDb()->query("INSERT INTO KCB_music (title, notes, music_link, estbd_by, estbd_dt_tm, lst_updtd_by, lst_tran_dt_tm) VALUES(:title, :notes, :link, :user_id, now(), :user_id2, now())");
				
				$uid = $this->getDb()->lastInsertId();
				
				if($uid > 0) {
					if($last_played !== "") {
						$this->getDb()->bind('last_played', date("Y-m-d H:i:s", strtotime($last_played)));					
						$this->getDb()->bind('number_plays', '1');					
					}
					else {
						$this->getDb()->bind('last_played', NULL);					
						$this->getDb()->bind('number_plays', '0');					
					}
					
					$this->getDb()->bind('uid', $uid);
					$this->getDb()->bind('user_id3', $user_id);
					$this->getDb()->bind('user_id4', $user_id);

					$retValue = $this->getDb()->query("INSERT INTO KCB_music_last_played (music_uid, last_played, number_plays, estbd_by, estbd_dt_tm, lst_updtd_by, lst_tran_dt_tm) VALUES(:uid, :last_played, :number_plays, :user_id3, now(), :user_id4, now())");
					
					if($retValue) {
						$this->executeTransaction();
					}
					else {
						$this->rollBackTransaction();
						$retValue = "insert_music_last_played_error";
					}
				}
				else {
					$this->rollBackTransaction();
					$retValue = "insert_music_error";
				}
			}
			catch(Exception $e) {
				$this->getDb()->ExceptionLog($e->getMessage());
				$this->rollBackTransaction();
				$retValue = "db_error";
			}
			
			return $retValue;
		}
		
		public function checkDupMusic($title) {
			$this->getDb()->bind("title", $title);

			return $this->getDb()->resultCount("SELECT title FROM KCB_music where title=:title");
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
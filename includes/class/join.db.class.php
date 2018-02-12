<?
 	include_once("db.class.php");
 	
	class JoinDb {
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

		public function checkDupPendingUser($email) {
			$this->getDb()->bind("email", $email);
			return $this->getDb()->query("SELECT email_address from KCB_email_address where email_address = :email");
		}

		public function addPendingUser($joinArray, $updateUser) {
			$this->getDb()->bind("firstName", $this->getFirstName($joinArray["txtName"]));
			$this->getDb()->bind("lastName", $this->getLastName($joinArray["txtName"]));
			$this->getDb()->bind("phone", $joinArray["txtPhone"]);
			$this->getDb()->bind("updateUser1", $updateUser);
			$this->getDb()->bind("updateUser2", $updateUser);
			$retVal = $this->getDb()->query("INSERT INTO KCB_Members(accountType, firstName, lastName, text, doNotDisplay, estbd_by, estbd_dt_tm, lst_updtd_by, lst_tran_dt_tm, disabled) VALUES(3, :firstName, :lastName, :phone, 1, :updateUser1, now(), :updateUser2, now(), 0)");

			// Return key value
			if($retVal) {
				return $this->getDb()->lastInsertId();
			}
			else {
				return -1;
			}
		}
				
		public function addInstrument($instrument, $uid, $updateUser) {
			$this->getDb()->bind("instrument", $instrument);
			$this->getDb()->bind("uid", $uid);
			$this->getDb()->bind("updateUser1", $updateUser);
			$this->getDb()->bind("updateUser2", $updateUser);
			$retVal = $this->getDb()->query("INSERT INTO KCB_instrument(member_uid, instrument, estbd_dt_tm, estbd_by, lst_tran_dt_tm, lst_updtd_by) VALUES(:uid, :instrument, now(), :updateUser1, now(), :updateUser2)");
		
			return $retVal;
		}

		public function addEmail($email, $uid, $updateUser) {
			$this->getDb()->bind("email", $email);
			$this->getDb()->bind("uid", $uid);
			$this->getDb()->bind("updateUser1", $updateUser);
			$this->getDb()->bind("updateUser2", $updateUser);
			$retVal = $this->getDb()->query("INSERT INTO KCB_email_address(member_uid, email_address, actv_flg, estbd_dt_tm, estbd_by, lst_tran_dt_tm, lst_updtd_by) VALUES(:uid, :email, 0, now(), :updateUser1, now(), :updateUser2)");
		
			return $retVal;
		}

		private function getFirstName($name) {
			$name_parts = explode(' ', $name);
			return $name_parts[0];
		}
		
		private function getLastName($name) {
			$name_parts = explode(' ', $name);
			return $name_parts[sizeof($name_parts) - 1];
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
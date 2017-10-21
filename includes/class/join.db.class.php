<?
 	include_once("db.class.php");
 	
	class JoinDb {
		private $db;

		public function __construct() {
			$this->setDB(new Db());
		}

		public function addPendingUser($joinArray) {
			$firstName = $this->getFirstName($joinArray["txtName"]);
			$lastName = $this->getLastName($joinArray["txtName"]);
			$phone = $joinArray["txtPhone"];
			$email = $joinArray["txtEmail"];
			$instruments = implode(', ', $joinArray['chkInstrument']);

			$this->getDb()->bind("auth_cd_guid", $guid);
			$this->getDb()->bind("email", $email);
			$this->getDb()->bind("email2", $email);
			$this->getDb()->bind("email3", $email);
			$retVal = $this->getDb()->query("INSERT INTO KCB_member_auth (member_uid, auth_cd_guid, estbd_by, estbd_dt_tm, lst_updtd_by, lst_tran_dt_tm) select m.uid, :auth_cd_guid, :email, now(), :email2, now() from KCB_Members m INNER JOIN KCB_email_address e ON e.member_uid=m.uid WHERE e.email_address = :email3");

			if($retVal) {
				// addInstrument
				// addEmail
			}

			return $retVal;
		}
				
		private function addInstrument($instrument, $uid, $updateUser) {
			$this->getDb()->bind("instrument", $instrument);
			$this->getDb()->bind("uid", $uid);
			$this->getDb()->bind("updateUser1", $updateUser);
			$this->getDb()->bind("updateUser2", $updateUser);
			$retVal = $this->getDb()->query("INSERT INTO KCB_instrument(member_uid, instrument, estbd_dt_tm, estbd_by, lst_tran_dt_tm, lst_updtd_by) VALUES(:uid, :instrument, now(), :updateUser1, now(), :updateUser2)");
		
			return $retVal;
		}

		private function addEmail($email, $uid, $updateUser) {
			$this->getDb()->bind("email", $email);
			$this->getDb()->bind("uid", $uid);
			$this->getDb()->bind("updateUser1", $updateUser);
			$this->getDb()->bind("updateUser2", $updateUser);
			$retVal = $this->getDb()->query("INSERT INTO KCB_email_address(member_uid, email_address, estbd_dt_tm, estbd_by, lst_tran_dt_tm, lst_updtd_by) VALUES(:uid, :email, now(), :updateUser1, now(), :updateUser2)");
		
			return $retVal;
		}

		private function getDb() {
			return $this->db;
		}
		
		private function setDb($db) {
        	$this->db = $db;
    	}

		private function getFirstName($name) {
			
		}
		
		private function getLastName($name) {
			
		}
	}
?>
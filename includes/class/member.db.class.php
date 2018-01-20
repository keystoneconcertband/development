<?
 	include_once("db.class.php");
 	 	
	class MemberDB {
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
		public function getMembers($instrument) {
			$this->getDb()->bind("instrument", $instrument);
			return $this->getDb()->query("SELECT firstName, lastName, displayFullName FROM KCB_Members m JOIN KCB_instrument i on m.UID = i.member_uid WHERE i.instrument = :instrument AND disabled = 0 ORDER BY lastName, firstName");
		}
		
		// Gets the member information
		public function getMember($email) {
			$this->getDb()->bind("email", $email);
			return $this->getDb()->row("SELECT m.UID, m.accountType, m.office, m.firstName, m.lastName, m.text, m.carrier, m.displayFullName, m.doNotDisplay, m.lastLogon, m.logonCount, m.disabled_dt_tm, m.disabled, a.address1, a.address2, a.city, a.state, a.zip, a.home_phone FROM KCB_Members m INNER JOIN KCB_email_address e ON e.member_uid=m.uid LEFT OUTER JOIN KCB_Address a ON a.member_uid=m.uid WHERE e.email_address = :email");
		}

		public function getMemberRecord($uid) {
			$this->getDb()->bind("uid", $uid);
			return $this->getDb()->row("SELECT m.uid, m.lastName, m.firstName, GROUP_CONCAT(DISTINCT email_address) AS `email`, m.text, m.carrier, a.home_phone, a.address1, a.address2, a.city, a.state, a.zip, m.office, m.displayFullName, m.doNotDisplay, GROUP_CONCAT(DISTINCT li.instrument) AS `instrument` FROM KCB_Members m LEFT OUTER JOIN KCB_email_address e ON e.member_uid = m.UID AND e.actv_flg = 1 LEFT OUTER JOIN KCB_Address a ON a.member_uid = m.uid LEFT OUTER JOIN KCB_instrument i ON a.member_uid = i.member_uid LEFT OUTER JOIN lkp_instrument li ON i.instrument = li.instrument WHERE m.UID = :uid");
		}

		// Gets all active members
		public function getActiveMembers() {
			return $this->getDb()->query("SELECT m.uid, CONCAT(m.lastName, ', ', m.firstName) AS fullName, GROUP_CONCAT(DISTINCT email_address) AS `email`, m.text, a.home_phone, a.address1, a.address2, a.city, a.state, a.zip, m.office, GROUP_CONCAT(DISTINCT li.display_text) AS `instrument` FROM KCB_Members m LEFT OUTER JOIN KCB_email_address e ON e.member_uid = m.UID AND e.actv_flg = 1 LEFT OUTER JOIN KCB_Address a ON a.member_uid = m.uid LEFT OUTER JOIN KCB_instrument i ON a.member_uid = i.member_uid LEFT OUTER JOIN lkp_instrument li ON i.instrument = li.instrument WHERE m.disabled = 0 AND m.uid <> 1 GROUP BY m.UID ORDER BY lastName, firstName");
		}

		// Checks that the account shouldn't be locked out because more than 3 auth_code attempts were made in the last hour.
		public function accountLockedStatus($email) {
			$this->getDb()->bind("email", $email);
			return $this->getDb()->single("SELECT l.lst_tran_dt_tm FROM KCB_login_cd l INNER JOIN KCB_Members m ON l.KCB_Members_UID=m.uid INNER JOIN KCB_email_address e ON e.member_uid=m.uid WHERE l.invalid_count >= 3 AND DATE_ADD(l.lst_tran_dt_tm, INTERVAL 1 HOUR) > now() AND e.email_address = :email");
		}

		// Gets only the Auth_cd from the Login Cd table
		public function getAuthCd($email) {
			$this->getDb()->bind("email", $email);
			return $this->getDb()->row("SELECT lc.auth_cd, lc.lst_tran_dt_tm FROM KCB_login_cd lc INNER JOIN KCB_email_address e ON e.member_uid=lc.KCB_Members_UID WHERE e.email_address = :email");
		}
		
		// Get Invalid Count
		public function getInvalidCount($email) {
			$this->getDb()->bind("email", $email);
			return $this->getDb()->single("SELECT lc.invalid_count FROM KCB_login_cd lc INNER JOIN KCB_email_address e ON e.member_uid=lc.KCB_Members_UID WHERE e.email_address = :email");
		}
		
		// Get Auth Cd GUID
		public function getAuthCdGuid($email, $cookieAuthCd) {
			$this->getDb()->bind("email", $email);
			$this->getDb()->bind("cookieAuthCd", $cookieAuthCd);
			$retVal = $this->getDb()->single("SELECT 1 FROM KCB_member_auth a INNER JOIN KCB_Members m ON a.member_uid=m.uid INNER JOIN KCB_email_address e ON e.member_uid=m.uid WHERE e.email_address = :email AND a.auth_cd_guid = :cookieAuthCd");

			return $retVal;
		}
		
		// Get all the email addresses for the user
		public function getEmailAddresses($uid) {
			$this->getDb()->bind("uid", $uid);
			return $this->getDb()->query("SELECT email_address FROM KCB_email_address WHERE member_uid = :uid");
		}
		
		// Get all instruments for the user
		public function getMemberInstruments($uid) {
			$this->getDb()->bind("uid", $uid);
			return $this->getDb()->query("SELECT instrument FROM KCB_instrument WHERE member_uid = :uid");
		}
		
		/* UPDATE FUNCTIONS */
		public function setAuthCd($email, $guid) {
			$this->getDb()->bind("auth_cd_guid", $guid);
			$this->getDb()->bind("email", $email);
			$this->getDb()->bind("email2", $email);
			$this->getDb()->bind("email3", $email);
			$retVal = $this->getDb()->query("INSERT INTO KCB_member_auth (member_uid, auth_cd_guid, estbd_by, estbd_dt_tm, lst_updtd_by, lst_tran_dt_tm) select m.uid, :auth_cd_guid, :email, now(), :email2, now() from KCB_Members m INNER JOIN KCB_email_address e ON e.member_uid=m.uid WHERE e.email_address = :email3");

			return $retVal;
		}

		// Updates the login_cd invalid count
		public function setLoginCdInvalidCount($email, $ipAddress, $invalidCount) {
			$this->getDb()->bind("email", $email);
			$this->getDb()->bind("invalid_count", $invalidCount);
			$this->getDb()->bind("ip_address", $ipAddress);			
			
			$retVal = $this->getDb()->query("UPDATE KCB_login_cd l INNER JOIN KCB_email_address e ON e.member_uid=l.KCB_Members_UID SET l.invalid_count = :invalid_count, l.ip_address = :ip_address, l.lst_tran_dt_tm = now() WHERE e.email_address = :email");

			return $retVal;
		}
		
		// Upserts login_cd
		public function setLoginCd($uid, $randomNumber, $invalidCount, $ipAddress) {
			$this->getDb()->bind("KCB_Members_UID", $uid);
			$this->getDb()->bind("auth_cd", $randomNumber);
			$this->getDb()->bind("invalid_count", $invalidCount);
			$this->getDb()->bind("ip_address", $ipAddress);
			// Update variables...due to the way PDO handles variable names
			$this->getDb()->bind("invalid_count_upd", $invalidCount);
			$this->getDb()->bind("ip_address_upd", $ipAddress);
			$this->getDb()->bind("auth_cd_upd", $randomNumber);
						
			$retVal = $this->getDb()->query("INSERT INTO KCB_login_cd (KCB_Members_UID, auth_cd, invalid_count, ip_address, estbd_dt_tm, lst_tran_dt_tm) VALUES(:KCB_Members_UID, :auth_cd, :invalid_count, :ip_address, now(), now()) ON DUPLICATE KEY UPDATE auth_cd=:auth_cd_upd, ip_address=:ip_address_upd, invalid_count=:invalid_count_upd, lst_tran_dt_tm=now()");	
			
			return $retVal;
		}

		// Update user last login and login count
		public function updateLastLogin($email) {
			$this->getDb()->bind("email", $email);
			$retVal = $this->getDb()->query("UPDATE KCB_Members m INNER JOIN KCB_email_address e ON e.member_uid=m.uid SET logonCount = logonCount + 1, lastLogon = now() WHERE e.email_address = :email");

			return $retVal;
		}
		
		// Logs login information
		public function logLogin($email, $success) {
			$this->getDb()->bind("email", $email);
			$this->getDb()->bind("success", $success);
			$retVal = $this->getDb()->query("INSERT INTO KCB_logon_audit (valid, logonValue, estbd_dt_tm) VALUES(:success, :email, now())");
					
			return $retVal;
		}

		public function insertMember($mbrArray, $updateUser) {
			$uid = 0;
			$text = NULL;
			$carrier = NULL;
			
			if($mbrArray['text'] !== '') {
				$text = $mbrArray['text'];
				$carrier = $mbrArray['carrier'];
			}
						
			$this->getDb()->bind('firstName', $mbrArray['firstName']);
			$this->getDb()->bind('lastName', $mbrArray['lastName']);
			$this->getDb()->bind('text', $text);
			$this->getDb()->bind('carrier', $carrier);
			$this->getDb()->bind("updateUser", $updateUser);
			$this->getDb()->bind("updateUser2", $updateUser);

			if(array_key_exists('displayFullName', $mbrArray)) {
				$this->getDb()->bind('displayFullName', "1");			
			}
			else {
				$this->getDb()->bind('displayFullName', "0");			
			}

			$retVal = $this->getDb()->query("INSERT INTO KCB_Members(firstName, lastName, displayFullName, text, carrier, estbd_by, estbd_dt_tm, lst_tran_dt_tm, lst_updtd_by) VALUES (:firstName, :lastName, :displayFullName, :text, :carrier, now(), :updateUser, now(), :updateUser2)");
			
			if($retVal) {
				$uid = $this->getDb()->lastInsertId();
			}
			
			return $uid;
		}
		
		public function updateMember($uid, $mbrArray, $updateUser) {
			$text = NULL;
			$carrier = NULL;
			
			if($mbrArray['text'] !== '') {
				$text = $mbrArray['text'];
				$carrier = $mbrArray['carrier'];
			}

			$this->getDb()->bind('uid', $uid);
			$this->getDb()->bind('firstName', $mbrArray['firstName']);
			$this->getDb()->bind('lastName', $mbrArray['lastName']);
			$this->getDb()->bind('text', $text);
			$this->getDb()->bind('carrier', $carrier);
			$this->getDb()->bind("updateUser", $updateUser);

			if(array_key_exists('displayFullName', $mbrArray)) {
				$this->getDb()->bind('displayFullName', "1");			
			}
			else {
				$this->getDb()->bind('displayFullName', "0");			
			}

			$retVal = $this->getDb()->query("UPDATE KCB_Members SET firstName = :firstName, lastName = :lastName, displayFullName = :displayFullName, text = :text, carrier = :carrier, lst_tran_dt_tm=now(), lst_updtd_by = :updateUser WHERE UID = :uid");
					
			return $retVal;
		}
		
		public function removeMember($uid, $updateUser) {
			$this->getDb()->bind('uid', $uid);
			$this->getDb()->bind("updateUser", $updateUser);
			
			$retVal = $this->getDb()->query("UPDATE KCB_Members SET disabled = 1, disabled_dt_tm = now(), lst_tran_dt_tm = now(), lst_updtd_by = :updateUser WHERE UID = :uid");

			return $retVal;
		}
				
		public function insertAddress($uid, $mbrArray, $updateUser) {
			$this->getDb()->bind('uid', $uid);
			$this->getDb()->bind('home_phone', $mbrArray['home_phone']);
			$this->getDb()->bind('address1', $mbrArray['address1']);
			$this->getDb()->bind('address2', $mbrArray['address2']);
			$this->getDb()->bind('city', $mbrArray['city']);
			$this->getDb()->bind('zip', $mbrArray['zip']);
			$this->getDb()->bind("updateUser", $updateUser);
			$this->getDb()->bind("updateUser1", $updateUser);

			$retVal = $this->getDb()->query("INSERT INTO KCB_Address (member_uid, address1, address2, city, state, zip, home_phone, estbd_by, estbd_dt_tm, lst_tran_dt_tm, lst_updtd_by) VALUES(:uid, :address1, :address2,:city, 'PA', :zip, :home_phone, :updateUser, now(), now(), :updateUser1)");

			return $retVal;
		}
		
		public function updateAddress($uid, $mbrArray, $updateUser) {
			$this->getDb()->bind('uid', $uid);
			$this->getDb()->bind('home_phone', $mbrArray['home_phone']);
			$this->getDb()->bind('address1', $mbrArray['address1']);
			$this->getDb()->bind('address2', $mbrArray['address2']);
			$this->getDb()->bind('city', $mbrArray['city']);
			$this->getDb()->bind('zip', $mbrArray['zip']);
			$this->getDb()->bind("updateUser", $updateUser);

			$retVal = $this->getDb()->query("UPDATE KCB_Address SET address1 = :address1, address2 = :address2, city = :city, zip = :zip, home_phone = :home_phone, lst_tran_dt_tm = now(), lst_updtd_by = :updateUser WHERE member_uid = :uid");

			return $retVal;
		}
		
		public function addEmail($email, $uid, $updateUser) {
			$this->getDb()->bind("email", $email);
			$this->getDb()->bind("uid", $uid);
			$this->getDb()->bind("updateUser1", $updateUser);
			$this->getDb()->bind("updateUser2", $updateUser);
			$retVal = $this->getDb()->query("INSERT INTO KCB_email_address(member_uid, email_address, estbd_dt_tm, estbd_by, lst_tran_dt_tm, lst_updtd_by) VALUES(:uid, :email, now(), :updateUser1, now(), :updateUser2)");
		
			return $retVal;
		}
		
		// Delete emails for active users, that way if they add it back we don't have to re-activate the email
		public function delEmail($email, $uid) {
			$this->getDb()->bind("email", $email);
			$this->getDb()->bind("uid", $uid);
			$retVal = $this->getDb()->query("DELETE FROM KCB_email_address WHERE member_uid=:uid AND email_address=:email");
			
			return $retVal;
		}
		
		// If the user is being "deleted", deactivate the emails from the system, but leave them in case they come back we 
		// have a copy of their email addresses and don't need to re-enter them
		public function deactivateEmail($email, $uid, $updateUser) {
			$this->getDb()->bind("email", $email);
			$this->getDb()->bind("uid", $uid);
			$this->getDb()->bind("updateUser", $updateUser);
			$retVal = $this->getDb()->query("UPDATE KCB_email_address SET actv_flg = 0, lst_tran_dt_tm = now(), lst_updtd_by = :updateUser WHERE member_uid=:uid AND email_address=:email");
			
			return $retVal;
		}
		
		public function addInstrument($instrument, $uid, $updateUser) {
			$this->getDb()->bind("instrument", $instrument);
			$this->getDb()->bind("uid", $uid);
			$this->getDb()->bind("updateUser1", $updateUser);
			$this->getDb()->bind("updateUser2", $updateUser);
			$retVal = $this->getDb()->query("INSERT INTO KCB_instrument(member_uid, instrument, estbd_dt_tm, estbd_by, lst_tran_dt_tm, lst_updtd_by) VALUES(:uid, :instrument, now(), :updateUser1, now(), :updateUser2)");
		
			return $retVal;
		}
		
		public function delInstrument($instrument, $uid) {
			$this->getDb()->bind("instrument", $instrument);
			$this->getDb()->bind("uid", $uid);
			$retVal = $this->getDb()->query("DELETE FROM KCB_instrument WHERE member_uid=:uid AND instrument=:instrument");
			
			return $retVal;
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
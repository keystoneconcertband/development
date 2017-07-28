<?
	// kcbBase is it's parent
 	include_once("kcbBase.class.php");
 	include_once("member.db.class.php");
 	 	
	class Member {
		private $kcbCookie = "KCB_Cookie";
		private $db;
		
		/* PUBLIC FUNCTIONS */
		public function __construct($authReq) {
			new KcbBase();
			$this->setDB(new MemberDB());
			
			if($authReq){
				if(!$this->validSession()) {
					header('Location: reauth.php');
				}
			}
		}
		
		public function getIpAddress() {
			return $_SERVER['REMOTE_ADDR'];
		}
		
		// Gets the members by instrument
		public function getMembers($instrument) {
			return $this->getDb()->getMembers($instrument);
		}
		
		// Gets the current member by email
		public function getMember($email) {
			return $this->getDb()->getMember($email);
		}
		
		// Gets all the email addresses for the user
		public function getEmailAddresses($uid) {
			return $this->getDb()->getEmailAddresses($uid);
		}
		
		// Gets all the instruments for the user
		public function getMemberInstruments($uid) {
			return $this->getDb()->getMemberInstruments($uid);
		}
		
		// Main login function
		public function login($email) {
			$response = $this->isValidUser($email);
			
			if($response == "valid") {
				// See if auth cookie exists for the user.
				if (!isset($_COOKIE[$this->kcbCookie])) {
					// Check whether they are logging in with the auth cd or not
					$response = $this->sendAuthEmail($email);
				}
				else {
					// Validate that the cookie auth code matches what is in the database
					if(!$this->isValidAuthCookie($email)) {
						// Send auth email, user's cookie is bad
						if($this->sendAuthEmail($email) <> 'db_error') {						
							$response = "auth_failed_invalid_cookie";
						}
						else {
							$response = "db_error";
						}
					}
					else {
						// Update login count and last login date.
						$this->getDb()->updateLastLogin($email);

						// Save email address since user's session is now valid to continue.
						$this->saveSession($email, $this->getDb()->getAuthCdGuid($email));
					}
				}
			}
			
			$this->getDb()->logLogin($email, $response);
			return $response;
		}
		
		// Verify auth cd
		public function verifyAuthCd($email, $auth_cd, $auth_remember) {
			// Verify user is still valid
			$response = $this->isValidUser($email);

			if($response == "valid") {
				$ipAddress = $this->getIpAddress(); 
				$authCdDb = $this->getDb()->getAuthCd($email);

				// See if auth_cd matches
				if($auth_cd == $authCdDb['auth_cd']) {
					// See if code is from within the last 10 mins
					$authCdDtTm = strtotime($authCdDb['lst_tran_dt_tm']) + 60*10;
					
					if(date(time()) > $authCdDtTm) {
						if($this->sendAuthEmail($email) <> 'db_error') {
							$response = "auth_old";
						}
						else {
							$response = "db_error";
						}
					}
					else {
						// Create auth_cd_guid for cookie
						$guid = $this->guid();
						
						// Update user's account
						if(!$this->getDb()->setAuthCd($email, $guid)) {
							$response = "db_error";
						}
						else {
							// Update login count and last login date.
							if(!$this->getDb()->updateLastLogin($email)) {
								$response = "db_error";
							}
							else {
								// Save email address since user's session is now valid to continue.
								$this->saveSession($email, $guid);
			
								if($auth_remember == "true") {
									$this->saveCookie($email, $guid);
								}
								
								$response = "valid";
							}
						}
					}					
				}
				else {
					if($this->upInvalidCdCount($email) == "db_error") {
						$response = "db_error";
					}
					else {
						$response = "auth_invalid";
					}
				}
			}

			return $response;
		}
		
		// Makes sure that the email and auth_cd_guid exists in the session
		public function validSession() {
			$validSession = false;
			if(isset($_SESSION['email']) && isset($_SESSION['auth_cd_guid'])){
				$validSession = true;
			}
			
			return $validSession;
		}
		
		public function in_multiarray($elem, $array)
		{
		    while (current($array) !== false) {
		        if (current($array) == $elem) {
		            return true;
		        } elseif (is_array(current($array))) {
		            if ($this->in_multiarray($elem, current($array))) {
		                return true;
		            }
		        }
		        next($array);
		    }
		    return false;
		}

		/* PRIVATE FUNCTIONS */
		private function getDb() {
			return $this->db;
		}
		
		private function setDb($db) {
        	$this->db = $db;
    	}
		
		// Gets whether or not the email address is valid, account is not disabled, and account locked status
		private function isValidUser($email) {
			$response = "valid";
			$member = $this->getDb()->getMember($email);

			// NOTE: 0 can only mean that the user is active. If false, the user doesn't exist or is disabled.
			if($member['disabled'] === 0) {							
				// Validate account auth cd isn't locked out	
				$accountLocked = $this->getDb()->accountLockedStatus($email);
				
				if($accountLocked != '') {
					$response =  "over_max_requests__" . date('D, M j g:i A', strtotime($accountLocked) + 3600);
				}
			}
			else {
				$response = "invalid";
			}
			
			return $response;
		}
						
		// Send Auth Emails
		private function sendAuthEmail($email) {
			$response = "auth_required_no_cookie";					
			$six_digit_random_number = mt_rand(100000, 999999);
			$ipAddress = $this->getIpAddress(); 
			$member = $this->getDb()->getMember($email);
			$authCdDb = $this->getDb()->getAuthCd($email);
			
			if($authCdDb) {
				$authCdDtTm = strtotime($authCdDb['lst_tran_dt_tm']) + 60*10;
				
				// Don't send another email if its been less than 10 mins
				if(date(time()) <= $authCdDtTm) {
					return "auth_cd_ten_min";					
				}
				else {
					if(!$this->getDb()->setLoginCd($member['UID'], $six_digit_random_number, "0", $ipAddress)) {
						return "db_error";
					}
				}
			}
			else {
				// Users first time logging in, just insert a new record
				if(!$this->getDb()->setLoginCd($member['UID'], $six_digit_random_number, "0", $ipAddress)) {
					return "db_error";
				}
			}

			/*
			// Original logic. Changed on 5/6 since I ran into problems where user could accidently lock themselves out without ever entering an invalid code
			// by waiting 10 mins between each attempt. 
			// If not the first time:
			// 1. If its been less than 10 mins since the last check, don't send a new auth_cd. NOTE: This does not up any invalid count, 
			//    as the user might have accidently closed the window to enter the auth code.
			// 2. If greater than 10 mins, update the auth_cd with the new value and send the email.

			if($authCdDb) {
				$authCdDtTm = strtotime($authCdDb['lst_tran_dt_tm']) + 60*10;
				
				if(date(time()) <= $authCdDtTm) {
					return "auth_cd_ten_min";					
				}
					
				// Get invalid count before we continue
				$invCount = $this->getDb()->getInvalidCount($email);
										
				// When setting the login code always set it to 0 so user has 3 tries with the code.	
				if(date(time()) <= strtotime($authCdDb['lst_tran_dt_tm']) + 60*60) {
					if(!$this->getDb()->setLoginCd($member['UID'], $six_digit_random_number, strval($invCount + 1), $ipAddress)) {
						return "db_error";
					}
				}
				else {
					if(!$this->getDb()->setLoginCd($member['UID'], $six_digit_random_number, "0", $ipAddress)) {
						return "db_error";
					}
				}
			}
			else {
				// Users first time logging in, just insert a new record
				if(!$this->getDb()->setLoginCd($member['UID'], $six_digit_random_number, "0", $ipAddress)) {
					return "db_error";
				}
			}
			*/
			
			//Email
			$subject = "Keystone Concert Band Login Code";
			$message = "Hi " . $member['firstName'] . ",\r\n\r\n";
			$message .= "A login code has been requested to login the members section of www.keystoneconcertband.com using your email address, ";
			$message .= $email;
			$message .= ". To continue on the website, you must enter the login code provided below:\r\n";
			$message .= $six_digit_random_number . "\r\n\r\n";
			$message .= "Please note, this code is only valid for 10 minutes, and you will have only 3 tries to enter it successfully. ";
			$message .= "If you enter an incorrect code more than 3 times within an hour, your account will be locked out for 1 hour.\r\n\r\n";
			$message .= "If you did not try to login the website recently, please delete this email as someone else tried to use your email address.\r\n\r\n";
			$message .= "Thanks,\r\n";
			$message .= "Jonathan Gillette";
			$headers = "From: web@keystoneconcertband.com\r\n" . 
			    "Reply-To: web@keystoneconcertband.com\r\n" . 
			    "X-Mailer: PHP/" . phpversion();
			
			try {
				mail($email, $subject, $message, $headers);
			}
			catch(Exception $e) {
				$response = 'Unable to send auth code email. Please try again later.';
			}

			return $response;					
		}
		
		/* Save cookie to the users system */
		private function saveCookie($email, $auth_cd) {
			// Set cookie with information and expiration of one year
			setcookie($this->kcbCookie, $email . "," . $auth_cd, time() + (60*60*24*365), "/");
		}
		
		// Determines whether or not the cookie passed in from the client contains the valid auth code
		private function isValidAuthCookie($email) {
			$response = false;
			
			if(isset($_COOKIE[$this->kcbCookie])) {
				$pieces = explode(",", $_COOKIE[$this->kcbCookie]);
			    $cookieEmail = $pieces[0];
			    $cookieAuthCd = $pieces[1];
								
				// Email must match the cookieEmail
				if($email == $cookieEmail) {
					// Only check if the cookie email matches the email the user is logging in from
					$auth_cd_guid = $this->getDb()->getAuthCdGuid($email);
					$response = $auth_cd_guid == $cookieAuthCd;
				}				
			}
			
			return $response;
		}
		
		// Increase the invalid cd count
		private function upInvalidCdCount($email) {
			$response = "valid";
			$invCount = $this->getDb()->getInvalidCount($email) + 1;
			$ipAddress = $this->getIpAddress(); 
											
			// Update login cd invalid_count
			if(!$this->getDb()->setLoginCdInvalidCount($email, $ipAddress, strval($invCount))) {
				$response =  "db_error";
			}
			
			return $response;
		}
			
		// Calculates GUID
		private function guid()
		{
		    if (function_exists('com_create_guid') === true) {
		        return trim(com_create_guid(), '{}');
		    }
		
		    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
		}
		
		// Save session information
		private function saveSession($email, $guid) {
			$_SESSION["email"] = $email;
			$_SESSION["auth_cd_guid"] = $guid;
			
			// Get member info to store in session
			$member = $this->getDb()->getMember($email);
			$_SESSION['uid'] = $member['UID'];
			$_SESSION['accountType'] = $member['accountType'];
			$_SESSION['office'] = $member['office'];
			$_SESSION['firstName'] = $member['firstName'];
			$_SESSION['lastName'] = $member['lastName'];
		}
	}
?>
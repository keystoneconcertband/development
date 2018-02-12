<?
 	include_once("kcbBase.class.php");
 	include_once("join.db.class.php");
 	
	class Join {
		private $db;
		private $kcb;

		public function __construct() {
			$this->kcb = new KcbBase();
			$this->db = new JoinDb();
		}
		
		public function JoinSubmit($joinArray) {
			$webUser = "JOIN_REQUEST";
			
			// Verify user filled out all the correct fields
			$response = $this->validateJoin($joinArray);
			
			if(empty($response)) {
				$response = $this->processEmail($joinArray);
				
				// If we successfully sent the email, add the user to the database as a pending user
				if($response === "success") {
					try {
						// Check if this pending user's email is already in the database, if so, skip adding the user
						$email = $this->getDb()->checkDupPendingUser($joinArray['txtEmail']);
												
						if(!$email) {
							$this->getDb()->beginTransaction();
							
							// Add user
							$uid = $this->getDb()->addPendingUser($joinArray, $webUser);
							
							// Add email
							if($uid > 0) {
								if($this->getDb()->addEmail($joinArray['txtEmail'], $uid, $webUser)) {
									// Loop through each instrument	and add
									foreach($joinArray['chkInstrument'] as $instr) {
										if(!$this->getDb()->addInstrument($instr, $uid, $webUser)) {
											$this->getDb()->rollBackTransaction();
											$response = "instrument_add_error";								
										}
									}
								}
								else {
									$this->getDb()->rollBackTransaction();
									$response = "email_add_error";
								}
							}
							else {
								$this->getDb()->rollBackTransaction();
								$response = "add_error";
							}
							
							// Everything above was successful, save the transaction								
							if($response === "success") {
								$this->getDb()->executeTransaction();	
							}						
						}
					}
					catch(Exception $e) {
						$this->LogError($e->getMessage());
						$this->getDb()->rollBackTransaction();
						$response = "db_error";
					}
				}
			}
			return $response;
		}
		
		private function validateJoin($joinArray) {
			$response = "";
			
			if(!isset($joinArray['txtName'])) {
				$response = "Name is required.";
			}
			else if(!empty($joinArray['txtPhone']) && strlen($joinArray['txtPhone']) < 10) {
				$response = "Phone number must be 10 digits.";
			}
			else if(!isset($joinArray['txtEmail'])) {
				$response = "Email is required.";
			}
			else if(!isset($joinArray['txtPlayLength'])) {
				$response = "Length of time playing is required.";
			}
			else if(!isset($joinArray['chkInstrument'])) {
				$response = "Please choose at least one instrument that you play.";
			}
			
			return $response;
		}
		
		private function processEmail($joinArray) {
			# Get server variables
			$name = $joinArray["txtName"];
			$phone = empty($joinArray["txtPhone"]) ? "Not Provided" : $joinArray["txtPhone"];
			$email = $joinArray["txtEmail"];
			$instruments = implode(', ', $joinArray['chkInstrument']);
			$playLength = $joinArray["txtPlayLength"];
			$comments = empty($joinArray["txtComments"]) ? "None provided" : $joinArray["txtComments"];
		
			$message = "Booking Request Submitted<br>";
			$message .= "<b>Name</b> " . $name . "<br>";
			$message .= "<b>Phone</b> " . $phone . "<br>";
			$message .= "<b>Email</b> " . $email . "<br>";
			$message .= "<b>Instrument(s)</b> " . $instruments . "<br>";
			$message .= "<b>Length of Play</b> " . $playLength . "<br>";
			$message .= "<b>Comments</b> " . $comments;
			
			# Send email
			if($this->kcb->sendEmail("web@keystoneconcertband.com", $message, "KCB Join Request")) {
				$response = "success";
			}
			else {
				$response = "Unable to save request. Please try again later.";
			}
			
			return $response;
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
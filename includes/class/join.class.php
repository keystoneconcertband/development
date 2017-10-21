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
			// Verify user filled out all the correct fields
			$response = $this->validateJoin($joinArray);
			
			if(empty($response)) {
				$response = $this->processEmail($joinArray);
				
				// If we successfully sent the email, add the user to the database
				// as a pending user
				if($response === "success") {
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
	}
?>
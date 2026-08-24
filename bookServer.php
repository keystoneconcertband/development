<?php 
	session_start();
	# This is the public page for booking
	include_once("includes/class/kcbBase.class.php");
	include_once("includes/class/kcbPublic.class.php");
	$response = "";

	function isValidEmailAddress($email) {
		$email = trim((string)$email);
		return $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
	}

	// Only allow POST requests
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {	
		$book = new KCBPublic();
		$response = $book->validateFormSubmission($_REQUEST, 'book_csrf_token');

		if($response === "") {
			if(!isset($_REQUEST['txtName'])) {
				$response = "Name is required.";
			}
			else if(isset($_REQUEST['txtPhone']) && $_REQUEST['txtPhone'] !== "" && strlen($_REQUEST['txtPhone']) < 10) {
				$response = "Phone number must be 10 digits.";
			}
			else if(!isset($_REQUEST['txtEmail'])) {
				$response = "Email is required.";
			}
			else if(!isValidEmailAddress($_REQUEST['txtEmail'])) {
				$response = "Please enter a valid email address.";
			}
			else if(!isset($_REQUEST['txtComments'])) {
				$response = "Comments are required.";
			}
		}
		
		if($response === "") {
			# Get server variables
			$kcb = new KcbBase();
			$name = isset($_REQUEST["txtName"]) ? $_REQUEST["txtName"] : "";
			$phone = isset($_REQUEST["txtPhone"]) ? $_REQUEST["txtPhone"] : "Not Provided";
			$email = isset($_REQUEST["txtEmail"]) ? $_REQUEST["txtEmail"] : "";
			$comments = isset($_REQUEST["txtComments"]) ? $_REQUEST["txtComments"] : "";
		
			$message = "Booking Request Submitted<br>";
			$message .= "<b>Name</b> " . $name . "<br>";
			$message .= "<b>Phone</b> " . $phone . "<br>";
			$message .= "<b>Email</b> " . $email . "<br>";
			$message .= "<b>Comments</b> " . $comments;
			
			# Send email
			if($kcb->sendEmail("webmaster@keystoneconcertband.com", $message, "KCB Booking Request")) {
				$response = "success";
			}
			else {
				$response = "Unable to send email. Please try again later.";
			}
		}
	}
	else {
		$response = "invalid_request";
	}

	echo json_encode($response);
?>

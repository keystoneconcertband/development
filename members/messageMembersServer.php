<? 
	# This is the public page for music which the ajax requests call.
	include_once('../includes/class/protectedAdmin.class.php');
	header('Content-Type: application/json');

	$response = "";
	$msg = new protectedAdmin();
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		
		$message = $_REQUEST['message'];
		
		if(strlen($message) > 800) {
			$response = "Message must be less than 800 characters.";
		}
		else if($message === "") {
			$response = "Message is required.";
		}
		
		$response = $msg->sendTextMessages($message);
	}
	else {
		$response = "invalid_request";
	}

	echo json_encode($response);
?>
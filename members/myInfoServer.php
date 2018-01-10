<? 
	# This is the public page for myInfo which the ajax requests call.
	//print_r($_POST);
	
	$response = "error";
		
	// Only allow POST requests
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {	
		$validRequest = true;

		// Validate form
		if(!isset($_POST['firstName'])) {
			$response = 'First name is required.';
			$validRequest = false;
		}
		else if(!isset($_POST['lastName'])) {
			$response = 'Last name is required.';
			$validRequest = false;
		}
		else if(!isset($_POST['address1'])) {
			$response = 'Address is required.';
			$validRequest = false;
		}
		else if(!isset($_POST['city'])) {
			$response = 'City is required.';
			$validRequest = false;
		}
		else if(!isset($_POST['zip'])) {
			$response = 'Zip Code is required.';
			$validRequest = false;
		}
		else {
			$emailExists = false;
			// array_filter will filter out any "blank" entries.
			foreach (array_filter($_POST['email']) as $vlu) {
				if($vlu !== '') {
					$emailExists = true;
				}
			}
			
			if(!$emailExists) {
				$response = "At least one email address is required.";
				$validRequest = false;
			}			
		}
		
		if($validRequest) {
			include_once('../includes/class/protectedMember.class.php');
			$myInfo = new ProtectedMember();
			
			$response = $myInfo->updateMember($_POST);
		}
	}
	else {
		$response = "invalid_request";
	}
	
	echo json_encode($response);
?>
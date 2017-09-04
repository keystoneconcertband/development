<? 
	# This is the public page for myInfo which the ajax requests call.
	//print_r($_POST);
	
	$validRequest = true;
	$response = "success";
		
	// Only allow POST requests
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		// Validate form
		if(!isset($_POST['inputFirstName'])) {
		$response = 'First name is required.';
			$validRequest = false;
		}
		else if(!isset($_POST['inputLastName'])) {
			$response = 'Last name is required.';
			$validRequest = false;
		}
		else if(!isset($_POST['inputAddress'])) {
			$response = 'Address is required.';
			$validRequest = false;
		}
		else if(!isset($_POST['inputCity'])) {
			$response = 'City is required.';
			$validRequest = false;
		}
		else if(!isset($_POST['inputZip'])) {
			$response = 'Zip Code is required.';
			$validRequest = false;
		}
		else {
			$emailExists = false;
			// array_filter will filter out any "blank" entries.
			foreach (array_filter($_POST['inputEmail']) as $vlu) {
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
			$myInfo = include_once('class/ProtectedMember.class.php');
			$myInfo->updateMember($_POST);
		}		
	}
	else {
		$response = "invalid_request";
	}
	
	echo json_encode($response);
?>
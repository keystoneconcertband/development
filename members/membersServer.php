<?php 
	# This is the public page for member which the ajax requests call.
	include_once('../includes/class/protectedMember.class.php');
	header('Content-Type: application/json');

	$mbr = new ProtectedMember();
	if(isset($_POST['type']) && ($_POST['type'] === "add" || $_POST['type'] === "edit")) {
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
		
		if($validRequest && $_POST['type'] === "edit") {
			if(!isset($_POST['uid'])) {
				echo json_encode('Unique Identifier is missing.');
			}
			else {
				$response = $mbr->updateMember($_POST['uid'], $_POST);			
			}
		}
		elseif($validRequest && $_POST['type'] === "add")  {
			$response = $mbr->addMember($_POST);
		}
		
		echo json_encode($response);	
	}
	elseif(isset($_POST['type']) && $_POST['type'] === "getMemberRecord") {
		if(!isset($_POST['uid'])) {
			echo json_encode('Unique Identifier is missing.');
		}
		else {			
			echo json_encode($mbr->getMemberRecord($_POST['uid']));
		}
	}
	// Get's the user info for "MyInfo" screen
	elseif(isset($_POST['type']) && $_POST['type'] === "getCurrentMemberRecord") {
		echo json_encode($mbr->getMemberRecord($_SESSION['uid']));
	}
	elseif(isset($_POST['type']) && $_POST['type'] === "delete") {
		if(!isset($_POST['uid'])) {
			echo json_encode('Unique Identifier is missing.');
		}
		else {			
			echo json_encode($mbr->removeMember($_POST['uid'], false));
		}
	}
	else {
		$mbmrs = $mbr->getActiveMembers();
		echo json_encode($mbmrs);	
	}
?>
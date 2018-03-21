<? 
	# This is the public page for music which the ajax requests call.
	include_once('../includes/class/protectedAdmin.class.php');
	header('Content-Type: application/json');

	$admin = new ProtectedAdmin();
	
	if(isset($_POST['type']) && $_POST['type'] === "add") {
		if(!isset($_POST['title'])) {
			echo json_encode('Title is required.');
		}
		elseif(!isset($_POST['message'])) {
			echo json_encode('Message is required.');
		}
		elseif(!isset($_POST['message_type'])) {
			echo json_encode('Message is required.');
		}
		elseif(!isset($_POST['start_dt'])) {
			echo json_encode('Start Date is required.');
		}
		elseif(!isset($_POST['end_dt'])) {
			echo json_encode('End Date is required.');
		}
		else {
			echo json_encode($admin->addHomepageMessage($_POST['title'], $_POST['message'], $_POST['message_type'], $_POST['start_dt'], $_POST['end_dt']));
		}
	}
	elseif(isset($_POST['type']) && $_POST['type'] === "edit") {
		if(!isset($_POST['uid'])) {
			echo json_encode('Unique Identifier is missing.');
		}
		elseif(!isset($_POST['title'])) {
			echo json_encode('Title is required.');
		}
		elseif(!isset($_POST['message'])) {
			echo json_encode('Message is required.');
		}
		elseif(!isset($_POST['message_type'])) {
			echo json_encode('Message is required.');
		}
		elseif(!isset($_POST['start_dt'])) {
			echo json_encode('Start Date is required.');
		}
		elseif(!isset($_POST['end_dt'])) {
			echo json_encode('End Date is required.');
		}
		else {			
			echo json_encode($admin->editHomepageMessage($_POST['uid'], $_POST['title'], $_POST['message'], $_POST['message_type'], $_POST['start_dt'], $_POST['end_dt']));
		}
	}
	elseif(isset($_POST['type']) && $_POST['type'] === "getHomepageMessageRecord") {
		if(!isset($_POST['uid'])) {
			echo json_encode('Unique Identifier is missing.');
		}
		else {			
			echo json_encode($admin->getHomepageMessageRecord($_POST['uid']));
		}
	}
	elseif(isset($_POST['type']) && $_POST['type'] === "homepageMessageDateConflictCheck") {
		echo json_encode($admin->homepageMessageDateConflictCheck($_POST['date']));
	}
	else {
		echo json_encode($admin->getHomepageMessages());	
	}
?>
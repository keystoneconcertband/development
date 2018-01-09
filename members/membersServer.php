<? 
	# This is the public page for member which the ajax requests call.
	include_once('../includes/class/protectedMember.class.php');
	header('Content-Type: application/json');

	$mbr = new ProtectedMember();
	if(isset($_POST['type']) && $_POST['type'] === "add") {
	}
	elseif(isset($_POST['type']) && $_POST['type'] === "edit") {
	}
	elseif(isset($_POST['type']) && $_POST['type'] === "getMemberRecord") {
		if(!isset($_POST['uid'])) {
			echo json_encode('Unique Identifier is missing.');
		}
		else {			
			echo json_encode($mbr->getMemberRecord($_POST['uid']));
		}
	}
	elseif(isset($_POST['type']) && $_POST['type'] === "delete") {
	}
	else {
		$mbmrs = $mbr->getActiveMembers();
		echo json_encode($mbmrs);	
	}
?>
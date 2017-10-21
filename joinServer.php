<? 
	# This is the public page for booking
 	include_once("includes/class/join.class.php");
	$response = "";

	// Only allow POST requests
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {	
		$join = new Join();
		$response = $join->JoinSubmit($_REQUEST);
	}
	else {
		$response = "invalid_request";
	}

	echo json_encode($response);
?>
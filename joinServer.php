<?php 
	# This is the public page for booking
 	include_once("includes/class/kcbPublic.class.php");
	$response = "";

	// Only allow POST requests
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {	
		$join = new KCBPublic();
		$response = $join->JoinSubmit($_REQUEST);
	}
	else {
		$response = "invalid_request";
	}

	echo json_encode($response);
?>
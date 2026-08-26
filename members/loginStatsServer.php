<?php 
	# This is the public page for music which the ajax requests call.
	require_once '../src/Shared/Classes/protectedAdmin.class.php';
	header('Content-Type: application/json');

	$stats = new protectedAdmin();
	echo json_encode($stats->getLoginStats());	
?>

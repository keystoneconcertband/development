<?php 
	# This is the public page for music which the ajax requests call.
	require_once '../src/Shared/Classes/protectedMusic.class.php';
	header('Content-Type: application/json');

	if(isset($_GET['term']) && $_GET['term'] !== "") {
		$music = new ProtectedMusic();
		
		echo json_encode($music->searchTitles($_GET['term']));
	}
	else {
		echo json_encode('[{"uid":"0","title":"no results"}]');
	}
?>

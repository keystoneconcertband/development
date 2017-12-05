<? 
	# This is the public page for music which the ajax requests call.
	include_once('../includes/class/protectedMusic.class.php');
	header('Content-Type: application/json');

	$music = new ProtectedMusic();
	
	if(isset($_POST['type']) && $_POST['type'] === "add") {
		if(!isset($_POST['title'])) {
			echo json_encode('Title is required.');
		}
		else {
			echo json_encode($music->addMusic($_POST['title'], $_POST['notes'], $_POST['music_link'], $_POST['last_played']));
		}
	}
	else {
		echo json_encode($music->getMusic());	
	}
?>
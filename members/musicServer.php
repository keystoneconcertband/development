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
			echo json_encode($music->addMusic($_POST['title'], $_POST['notes'], $_POST['music_link'], $_POST['genre'], $_POST['last_played']));
		}
	}
	elseif(isset($_POST['type']) && $_POST['type'] === "edit") {
		if(!isset($_POST['uid'])) {
			echo json_encode('Unique Identifier is missing.');
		}
		elseif(!isset($_POST['title'])) {
			echo json_encode('Title is required.');
		}
		else {			
			echo json_encode($music->editMusic($_POST['uid'], $_POST['title'], $_POST['notes'], $_POST['music_link'], $_POST['genre'], $_POST['last_played']));
		}
	}
	elseif(isset($_POST['type']) && $_POST['type'] === "delete") {
		if(!isset($_POST['uid'])) {
			echo json_encode('Unique Identifier is missing.');
		}
		else {			
			echo json_encode($music->deleteMusic($_POST['uid']));
		}
	}
	elseif(isset($_POST['type']) && $_POST['type'] === "getMusicRecord") {
		if(!isset($_POST['uid'])) {
			echo json_encode('Unique Identifier is missing.');
		}
		else {			
			echo json_encode($music->getMusicRecord($_POST['uid']));
		}
	}
	elseif(isset($_POST['type']) && $_POST['type'] === "getMusicGenres") {
		echo json_encode($music->getGenres());
	}
	else {
		echo json_encode($music->getMusic());	
	}
?>
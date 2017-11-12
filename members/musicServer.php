<? 
	# This is the public page for music which the ajax requests call.
	include_once('../includes/class/protectedMusic.class.php');
	header('Content-Type: application/json');

	$music = new ProtectedMusic();
	echo json_encode($music->getMusic());
?>
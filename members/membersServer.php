<? 
	# This is the public page for member which the ajax requests call.
	include_once('../includes/class/protectedMember.class.php');
	header('Content-Type: application/json');

	$mbr = new ProtectedMember();
	$mbmrs = $mbr->getActiveMembers();
	echo json_encode($mbmrs);
?>
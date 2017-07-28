<? 
	# This is the public submit page for myInfo which the ajax requests call.
	include_once('class/member.class.php');

	$email = isset($_REQUEST["email"]) ? $_REQUEST["email"] : null;
	$auth_cd = isset($_REQUEST["auth_cd"]) ? $_REQUEST["auth_cd"] : null;
	$auth_remember = isset($_REQUEST["auth_remember"]) ? $_REQUEST["auth_remember"] : null;
	$mbr = new UpdateMember();
	
?>
here
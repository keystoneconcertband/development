<? 
	# This is the public page for memberLogin which the ajax requests call.
	include_once('class/member.class.php');

	$email = isset($_REQUEST["email"]) ? $_REQUEST["email"] : null;
	$auth_cd = isset($_REQUEST["auth_cd"]) ? $_REQUEST["auth_cd"] : null;
	$auth_remember = isset($_REQUEST["auth_remember"]) ? $_REQUEST["auth_remember"] : null;
	$mbr = new member();
		
	if($email != null && $auth_cd == null) {
		echo $mbr->login($email);
	}
	elseif ($email != null && $auth_cd != null){
		echo $mbr->verifyAuthCd($email, $auth_cd, $auth_remember);
	}
	else {
		echo "invalid_request";		
	}
?>
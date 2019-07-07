<? 
	# This is the public page for memberLogin which the ajax requests call.
	include_once('includes/class/member.class.php');

	$email = isset($_REQUEST["email"]) ? $_REQUEST["email"] : null;
	$auth_cd = isset($_REQUEST["auth_cd"]) ? $_REQUEST["auth_cd"] : null;
	$fb_id = isset($_REQUEST["fb_id"]) ? $_REQUEST["fb_id"] : null;
	$auth_remember = isset($_REQUEST["auth_remember"]) ? $_REQUEST["auth_remember"] : null;

	$mbr = new Member(false);

	if($email != null && $fb_id == null && $auth_cd == null) {
		echo $mbr->login($email);
	}
	elseif ($email != null && $auth_cd != null){
		echo $mbr->verifyAuthCd($email, $auth_cd, $auth_remember);
	}
	elseif($email !=null && $fb_id != null) {
		echo $mbr->facebookLogin($email, $fb_id);
	}
	else {
		echo "invalid_request";		
	}
?>
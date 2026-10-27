<?php
# Member login and authentication code verification handler
require_once __DIR__ . '/../includes/class/member.class.php';

$email = isset($_REQUEST["email"]) ? $_REQUEST["email"] : null;
$auth_cd = isset($_REQUEST["auth_cd"]) ? $_REQUEST["auth_cd"] : null;
$auth_remember = isset($_REQUEST["auth_remember"]) ? $_REQUEST["auth_remember"] : null;

$mbr = new Member(false);

if ($email != null && $auth_cd == null) {
	echo trim((string)$mbr->login($email));
}
elseif ($email != null && $auth_cd != null) {
	echo trim((string)$mbr->verifyAuthCd($email, $auth_cd, $auth_remember));
}
else {
	echo "invalid_request";
}

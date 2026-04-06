<?php
include_once("../includes/class/protectedMember.class.php");
include_once("../includes/class/member.db.class.php");

if(strtolower(getenv('SEND_ATTESTATION_EMAILS')) !== 'true') {
    http_response_code(403);
    echo "<h1>Access Denied</h1><p>This page is currently disabled.</p>";
    exit;
}

function formatAddress($mbr) {
	$address = "";
	if($mbr['address1'] !== '') {
		$address .= $mbr['address1'] . '<br />';

		if(!empty($mbr['address2'])) {
			$address .= $mbr['address2'] . '<br />';
		}	
		
		$address .= $mbr['city'] . ', ' . $mbr['state'] . ' ' . $mbr['zip'];				
	}
	
	return $address;
}

function formatPhone($number) {
	return preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $number);
}

$pMbr = new ProtectedMember();
$kcb = new KcbBase();
$members = $pMbr->getActiveMembers();
$memberCount = 0;
foreach($members as $mbr) {
    $text = "Hello " . $mbr['firstName'] ."!<br /><br />";
    $text .= "To ensure we have the most up to date information, please review the information below to ensure it is accurate. ";
    $text .= "If anything is incorrect, or you no longer are apart of the band, please reply to this email with the correct information. ";
    $text .= "You may also log in to the ";
    $text .= "<a href='https://www.keystoneconcertband.com/members.php'>member portal</a> to update your information.<br /><br />";
    $text .= "<b>Name:</b> " . $mbr['fullName'] . "<br />";
    $text .= "<b>Email:</b> " . $mbr['email'] . "<br />";
    $text .= "<b>Instrument:</b> " . $mbr['instrument'] . "<br />";
    $text .= "<b>Phone:</b> " . formatPhone($mbr['text']) . "<br />";
    $text .= "<b>Address:</b><br />" . formatAddress($mbr) . "<br />";
    $text .= "<br />Thank you!<br />The Keystone Concert Band";

    $kcb->sendEmail($mbr['email'], $text, "Keystone Concert Band - Review Your Information");
    $memberCount++;
}

echo "<br />Sent " . $memberCount . " emails.";
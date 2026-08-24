<?php
session_start();
# This is the public page for booking
include_once("includes/class/kcbPublic.class.php");
$response = "";

function isValidEmailAddress($email) {
    $email = trim((string)$email);
    return $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_REQUEST['txtEmail']) ? $_REQUEST['txtEmail'] : '';
    if ($email !== '' && !isValidEmailAddress($email)) {
        $response = "Please enter a valid email address.";
    } else {
        $join = new KCBPublic();
        $response = $join->joinSubmit($_REQUEST);
    }
} else {
    $response = "invalid_request";
}

echo json_encode($response);
?>

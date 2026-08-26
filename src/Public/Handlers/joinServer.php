<?php
session_start();
# This is the public page for booking
require_once __DIR__ . '/../../Shared/Classes/kcbPublic.class.php';
$response = "";

function isValidEmailAddress($email) {
    $email = trim((string)$email);
    return $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $join = new KCBPublic();
    $response = $join->validateFormSubmission($_REQUEST, 'join_csrf_token');

    if ($response === "") {
        $email = isset($_REQUEST['txtEmail']) ? $_REQUEST['txtEmail'] : '';
        if ($email !== '' && !isValidEmailAddress($email)) {
            $response = "Please enter a valid email address.";
        } else {
            $response = $join->joinSubmit($_REQUEST);
        }
    }
} else {
    $response = "invalid_request";
}

echo json_encode($response);
?>

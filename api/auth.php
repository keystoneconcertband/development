<?php
# Member login and authentication code verification handler
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/../includes/class/member.class.php';

// Detect JSON request body if present
$inputData = [];
$rawInput = file_get_contents('php://input');
if (!empty($rawInput)) {
    $decoded = json_decode($rawInput, true);
    if (is_array($decoded)) {
        $inputData = $decoded;
    }
}

$email = isset($inputData['email']) ? $inputData['email'] : (isset($_REQUEST['email']) ? $_REQUEST['email'] : null);
$auth_cd = isset($inputData['auth_cd']) ? $inputData['auth_cd'] : (isset($_REQUEST['auth_cd']) ? $_REQUEST['auth_cd'] : null);
$auth_remember = isset($inputData['auth_remember']) ? $inputData['auth_remember'] : (isset($_REQUEST['auth_remember']) ? $_REQUEST['auth_remember'] : null);
$redirect = isset($inputData['redirect']) ? $inputData['redirect'] : (isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : null);

// Normalize and sanitize inputs
$email = is_string($email) ? trim($email) : '';
$auth_cd = is_string($auth_cd) ? trim($auth_cd) : null;
if ($auth_remember === true || $auth_remember === 'true' || $auth_remember === '1' || $auth_remember === 1) {
    $auth_remember = 'true';
} else {
    $auth_remember = 'false';
}

// Validate redirect target to prevent open-redirect vulnerabilities
$safeRedirect = '/members/index.php';
if (!empty($redirect) && is_string($redirect)) {
    $cleanRedirect = trim($redirect);
    if (str_starts_with($cleanRedirect, '/members/') && !str_starts_with($cleanRedirect, '//') && strpos($cleanRedirect, '\\') === false) {
        $safeRedirect = $cleanRedirect;
    }
}

// Determine output format (prefer JSON if requested)
$wantsJson = (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
    || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
    || (isset($_REQUEST['format']) && $_REQUEST['format'] === 'json')
    || !empty($inputData);

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $rawResponse = "invalid_request";
} else {
    $mbr = new Member(false);
    if ($auth_cd === null || $auth_cd === '') {
        $rawResponse = trim((string)$mbr->login($email));
    } else {
        $rawResponse = trim((string)$mbr->verifyAuthCd($email, $auth_cd, $auth_remember));
    }
}

if (!$wantsJson) {
    header("Content-Type: text/plain; charset=UTF-8");
    echo $rawResponse;
    exit;
}

header("Content-Type: application/json; charset=UTF-8");

$jsonResponse = [
    'raw' => $rawResponse,
    'status' => $rawResponse,
];

if ($rawResponse === 'valid') {
    if ($safeRedirect === '/members/index.php' && !empty($_SESSION['pending_info'])) {
        $safeRedirect = '/members/pendingMembers.php';
    }
    $jsonResponse['status'] = 'valid';
    $jsonResponse['redirect'] = $safeRedirect;
    $jsonResponse['message'] = 'Authentication successful.';
} elseif ($rawResponse === 'auth_required_no_cookie') {
    $jsonResponse['status'] = 'auth_required';
    $jsonResponse['cooldown'] = Member::RESEND_COOLDOWN;
    $jsonResponse['message'] = 'A 6-digit login code has been sent to your email.';
} elseif ($rawResponse === 'auth_cd_not_expired') {
    $jsonResponse['status'] = 'auth_cd_not_expired';
    $jsonResponse['cooldown'] = Member::RESEND_COOLDOWN;
    $jsonResponse['message'] = 'A login code was recently sent and is still valid. Please check your inbox.';
} elseif (str_starts_with($rawResponse, 'resend_throttle__')) {
    $remaining = (int)substr($rawResponse, strlen('resend_throttle__'));
    $jsonResponse['status'] = 'resend_throttle';
    $jsonResponse['remaining'] = $remaining;
    $jsonResponse['message'] = "Please wait {$remaining} seconds before requesting a new code.";
} elseif (str_starts_with($rawResponse, 'over_max_requests__')) {
    $timeout = substr($rawResponse, strlen('over_max_requests__'));
    $jsonResponse['status'] = 'over_max_requests';
    $jsonResponse['timeout'] = $timeout;
    $jsonResponse['message'] = "Account locked due to too many invalid attempts. Please try again after {$timeout}.";
} elseif ($rawResponse === 'auth_invalid') {
    $jsonResponse['status'] = 'auth_invalid';
    $jsonResponse['message'] = 'Sorry, that login code was not valid.';
} elseif ($rawResponse === 'auth_old') {
    $jsonResponse['status'] = 'auth_old';
    $jsonResponse['cooldown'] = Member::RESEND_COOLDOWN;
    $jsonResponse['message'] = 'That login code has expired. A new code has been sent to your email.';
} elseif ($rawResponse === 'auth_failed_invalid_cookie') {
    $jsonResponse['status'] = 'auth_failed_invalid_cookie';
    $jsonResponse['cooldown'] = Member::RESEND_COOLDOWN;
    $jsonResponse['message'] = 'Your saved session expired or was invalid. A new login code has been sent to your email.';
} elseif ($rawResponse === 'invalid' || $rawResponse === 'invalid_pending') {
    $jsonResponse['status'] = 'invalid';
    $jsonResponse['message'] = 'Sorry, that email address is not valid for login.';
} elseif ($rawResponse === 'invalid_request') {
    $jsonResponse['status'] = 'invalid_request';
    $jsonResponse['message'] = 'A valid email address is required to log in.';
} elseif ($rawResponse === 'db_error') {
    $jsonResponse['status'] = 'db_error';
    $jsonResponse['message'] = 'Database error encountered. Please try again later.';
} else {
    $jsonResponse['status'] = 'error';
    $jsonResponse['message'] = $rawResponse;
}

echo json_encode($jsonResponse);

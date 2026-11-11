<?php
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../../includes/class/member.class.php';
require_once __DIR__ . '/../../includes/class/protectedAdmin.class.php';
require_once __DIR__ . '/../../includes/class/pendingMemberService.class.php';

$jsonExit = function ($statusCode, array $payload) {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
};

$verifyCsrf = function ($submittedToken) {
    $sessionToken = $_SESSION['pending_members_csrf'] ?? '';
    return is_string($submittedToken)
        && is_string($sessionToken)
        && $sessionToken !== ''
        && hash_equals($sessionToken, $submittedToken);
};

$memberSession = new Member(false, true);
$service = new PendingMemberService();
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$action = isset($_REQUEST['type']) ? trim((string)$_REQUEST['type']) : '';

$resolveSelfServicePendingMember = static function () use ($method, $verifyCsrf, $service, $jsonExit): array {
    if ($method !== 'POST') {
        $jsonExit(405, ['status' => 'error', 'message' => 'Method not allowed.']);
    }

    if (!$verifyCsrf($_POST['csrf_token'] ?? '')) {
        $jsonExit(403, ['status' => 'error', 'message' => 'Your security token has expired. Please reload the page and try again.']);
    }

    $token = trim((string)($_POST['token'] ?? ''));
    $targetMember = null;
    $actorEmail = '';
    $isOwnSession = false;

    if ($token !== '') {
        $targetMember = $service->getPendingMemberForToken($token);
        if (!$targetMember || (int)($targetMember['pending_info'] ?? 0) !== 1) {
            $jsonExit(403, ['status' => 'error', 'message' => 'This onboarding link is invalid, expired, or has already been completed.']);
        }
        $isOwnSession = !empty($_SESSION['uid']) && (int)$_SESSION['uid'] === (int)$targetMember['uid'];
        if (!empty($_SESSION['email'])) {
            $actorEmail = (string)$_SESSION['email'];
        } else {
            $memberEmails = array_filter(array_map('trim', explode(',', (string)($targetMember['email'] ?? ''))));
            $actorEmail = !empty($memberEmails) ? reset($memberEmails) : 'MEMBER_SELF_SERVICE';
        }
    } elseif (!empty($_SESSION['uid']) && !empty($_SESSION['email'])) {
        $targetMember = $service->getPendingMemberForSessionUid((int)$_SESSION['uid']);
        if (!$targetMember) {
            $jsonExit(403, ['status' => 'error', 'message' => 'No pending member information is currently awaiting completion for your account.']);
        }
        $actorEmail = (string)$_SESSION['email'];
        $isOwnSession = true;
    } else {
        $jsonExit(401, ['status' => 'error', 'message' => 'Please use your emailed onboarding link or sign in to continue.']);
    }

    return [$targetMember, $actorEmail, $isOwnSession];
};

// Self-service (token-based or logged-in pending member) completion endpoint
if ($action === 'completePendingInfo') {
    [$targetMember, $actorEmail, $isOwnSession] = $resolveSelfServicePendingMember();

    $result = $service->completePendingMemberInfo((int)$targetMember['uid'], $_POST, $actorEmail, true);
    if (($result['status'] ?? '') !== 'success') {
        $jsonExit(400, $result);
    }

    if ($isOwnSession) {
        $_SESSION['pending_info'] = 0;
        $_SESSION['firstName'] = $result['firstName'];
        $_SESSION['lastName'] = $result['lastName'];
        if (!empty($result['emails'])) {
            $currentEmail = strtolower((string)($_SESSION['email'] ?? ''));
            $lowerEmails = array_map('strtolower', $result['emails']);
            if ($currentEmail === '' || !in_array($currentEmail, $lowerEmails, true)) {
                $_SESSION['email'] = $result['emails'][0];
            }
        }
        $result['redirect'] = '/members/index.php';
    } else {
        $result['redirect'] = null;
    }

    $jsonExit(200, $result);
}

// Self-service (token-based or logged-in pending member) membership decline / email opt-out endpoint
if ($action === 'declineMembership') {
    [$targetMember, $actorEmail, $isOwnSession] = $resolveSelfServicePendingMember();

    $reason = (string)($_POST['reason'] ?? '');
    $result = $service->declinePendingMembership((int)$targetMember['uid'], $actorEmail, $reason);
    if (($result['status'] ?? '') !== 'success') {
        $jsonExit(400, $result);
    }

    if ($isOwnSession) {
        $memberSession->revokeRememberMeCookie($_SESSION['email'] ?? null);
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    $result['redirect'] = null;
    $jsonExit(200, $result);
}

// All remaining actions require an authenticated officer / administrator
if (empty($_SESSION['email']) || !isset($_SESSION['accountType']) || !in_array((int)$_SESSION['accountType'], [1, 2], true)) {
    $jsonExit(403, ['status' => 'error', 'message' => 'Administrator access is required.']);
}

$admin = new ProtectedAdmin();

$mutatingAdminActions = ['activate', 'remind', 'sendDueReminders', 'edit', 'delete'];
if (in_array($action, $mutatingAdminActions, true)) {
    if ($method !== 'POST') {
        $jsonExit(405, ['status' => 'error', 'message' => 'Method not allowed.']);
    }
    if (!$verifyCsrf($_POST['csrf_token'] ?? '')) {
        $jsonExit(403, ['status' => 'error', 'message' => 'Your security token has expired. Please reload the page and try again.']);
    }
}

switch ($action) {
    case 'activate':
        $uid = (int)($_POST['uid'] ?? 0);
        $res = $admin->activatePendingMember($uid);
        $statusCode = ($res['status'] ?? '') === 'error' ? 400 : 200;
        $jsonExit($statusCode, $res);
        break;

    case 'remind':
        $uid = (int)($_POST['uid'] ?? 0);
        $res = $admin->sendPendingMemberReminder($uid);
        $statusCode = in_array($res['status'] ?? '', ['error', 'failed'], true) ? 400 : 200;
        $jsonExit($statusCode, $res);
        break;

    case 'sendDueReminders':
        $res = $admin->sendDuePendingMemberReminders();
        $statusCode = ($res['status'] ?? '') === 'failed' ? 500 : 200;
        $jsonExit($statusCode, $res);
        break;

    case 'edit':
        $uid = (int)($_POST['uid'] ?? 0);
        if ($uid <= 0) {
            $jsonExit(400, ['status' => 'error', 'message' => 'Invalid member identifier.']);
        }
        $res = $admin->addPendingMember($uid, $_POST);
        if ($res === 'success' || (is_array($res) && ($res['status'] ?? '') === 'success')) {
            $jsonExit(200, [
                'status'  => 'success',
                'message' => is_array($res) ? ($res['message'] ?? 'Member profile saved and finalized.') : 'Member profile saved and finalized.',
            ]);
        }
        $errorMessage = is_array($res) ? ($res['message'] ?? 'Unable to save member information.') : (string)$res;
        $jsonExit(400, ['status' => 'error', 'message' => $errorMessage]);
        break;

    case 'delete':
        $uid = (int)($_POST['uid'] ?? 0);
        if ($uid <= 0) {
            $jsonExit(400, ['status' => 'error', 'message' => 'Invalid member identifier.']);
        }
        $res = $admin->deleteMember($uid, true);
        if ($res === 'success') {
            $jsonExit(200, ['status' => 'success', 'message' => 'Pending member deleted.']);
        }
        $jsonExit(400, ['status' => 'error', 'message' => is_string($res) ? $res : 'Unable to delete pending member.']);
        break;

    case 'getMemberRecord':
        $uid = (int)($_REQUEST['uid'] ?? 0);
        if ($uid <= 0) {
            $jsonExit(400, ['status' => 'error', 'message' => 'Invalid member identifier.']);
        }
        $record = $admin->getMemberRecord($uid);
        if (!$record) {
            $jsonExit(404, ['status' => 'error', 'message' => 'Pending member record was not found.']);
        }
        if (!empty($record['activation_token'])) {
            $record['onboarding_url'] = $service->buildCompletionUrl((string)$record['activation_token']);
            unset($record['activation_token']);
        } else {
            $record['onboarding_url'] = '';
        }
        $jsonExit(200, ['status' => 'success', 'member' => $record]);
        break;

    default:
        $rows = $admin->getPendingMembers();
        if (!is_array($rows)) {
            $rows = [];
        }
        $formatted = [];
        foreach ($rows as $row) {
            if (!empty($row['activation_token'])) {
                $row['onboarding_url'] = $service->buildCompletionUrl((string)$row['activation_token']);
                unset($row['activation_token']);
            } else {
                $row['onboarding_url'] = '';
            }
            $row['pending_info'] = (int)($row['pending_info'] ?? 0);
            $row['reminder_count'] = (int)($row['reminder_count'] ?? 0);
            $formatted[] = $row;
        }
        $jsonExit(200, ['data' => $formatted]);
}

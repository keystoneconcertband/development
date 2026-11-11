<?php
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../includes/asset.php';
require_once __DIR__ . '/../includes/class/member.class.php';
require_once __DIR__ . '/../includes/class/pendingMemberService.class.php';

$memberSession = new Member(false, true);
$pendingService = new PendingMemberService();
$instrumentOptions = $pendingService->getInstrumentOptions();

if (empty($_SESSION['pending_members_csrf'])) {
    $_SESSION['pending_members_csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['pending_members_csrf'];

$token = isset($_GET['token']) ? trim((string)$_GET['token']) : '';
$isLoggedIn = !empty($_SESSION['email']) && !empty($_SESSION['uid']);
$isAdmin = $isLoggedIn && isset($_SESSION['accountType']) && in_array((int)$_SESSION['accountType'], [1, 2], true);

$viewMode = '';
$targetMember = null;

if ($token !== '') {
    $tokenMember = $pendingService->getPendingMemberForToken($token);
    if ($tokenMember !== null) {
        if ((int)($tokenMember['pending_info'] ?? 0) === 1) {
            $viewMode = 'member_form';
            $targetMember = $tokenMember;
        } else {
            $viewMode = 'already_completed';
        }
    } else {
        $viewMode = 'invalid_token';
    }
} elseif ($isLoggedIn) {
    $sessionMember = $pendingService->getPendingMemberForSessionUid((int)$_SESSION['uid']);
    if ($sessionMember !== null) {
        $viewMode = 'member_form';
        $targetMember = $sessionMember;
        $_SESSION['pending_info'] = 1;
    } elseif ($isAdmin) {
        $viewMode = 'admin';
        $_SESSION['pending_info'] = 0;
    } elseif (isset($_SESSION['pending_info']) && (int)$_SESSION['pending_info'] === 0) {
        $viewMode = 'already_completed';
    } else {
        header('Location: /members/adminAccess.php');
        exit;
    }
} else {
    header('Location: /members/reauth.php?redirect=' . urlencode('/members/pendingMembers.php'));
    exit;
}

$formatDisplayPhone = function ($raw) {
    $digits = preg_replace('/\D/', '', (string)$raw);
    if (strlen($digits) === 11 && $digits[0] === '1') {
        $digits = substr($digits, 1);
    }
    if (strlen($digits) === 10) {
        return '(' . substr($digits, 0, 3) . ') ' . substr($digits, 3, 3) . '-' . substr($digits, 6, 4);
    }
    return (string)$raw;
};

$pageTitle = $viewMode === 'admin'
    ? 'Pending Members - Keystone Concert Band'
    : 'Complete Member Information - Keystone Concert Band';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once __DIR__ . '/../includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band member area">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <?php require_once __DIR__ . '/../includes/common_css.php'; ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(asset('/assets/css/member.css'), ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php require_once __DIR__ . '/../includes/nav.php'; ?>

    <main id="pendingMembersApp"
          class="container pt-3 pb-4 flex-grow-1"
          data-view-mode="<?php echo htmlspecialchars($viewMode, ENT_QUOTES, 'UTF-8'); ?>"
          data-csrf-token="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>"
          data-logged-in="<?php echo $isLoggedIn ? '1' : '0'; ?>">

        <?php if ($viewMode === 'admin') { ?>
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 pb-3 mb-3 border-bottom">
                <div>
                    <h1 class="h2 mb-1">Pending Members</h1>
                    <p class="text-muted mb-0">Approve new applicants to send their onboarding link, send reminders, or finalize profiles manually.</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" id="sendDueRemindersBtn" class="btn btn-outline-primary">
                        <i class="fa-solid fa-bell me-1" aria-hidden="true"></i>Send Due Reminders
                    </button>
                </div>
            </div>

            <div class="alert alert-info shadow-sm mb-3" role="note">
                <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-info me-1" aria-hidden="true"></i>Onboarding Workflow</div>
                <ol class="mb-0 ps-3 small">
                    <li>Click <strong>Activate</strong> on a new application to enable their account and email them a link to complete their missing details.</li>
                    <li>Members waiting to finish their profile show as <strong>Awaiting Member Info</strong>. Click <strong>Remind</strong> anytime, or let daily automated reminders run (first after 3 days, then every 7 days, up to 3 reminders).</li>
                    <li>Once submitted, their complete profile is emailed to the Webmaster and Band Director automatically. You can also click <strong>Edit / Complete</strong> to fill in details yourself.</li>
                </ol>
            </div>

            <div id="adminAlert"></div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <table id="kcbMemberTable" class="table table-striped table-bordered table-hover align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Actions</th>
                                <th>Name</th>
                                <th>Status &amp; Reminders</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Instrument</th>
                                <th>Submitted</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

            <!-- Admin Complete / Edit Pending Member Modal -->
            <div class="modal fade" id="memberModal" tabindex="-1" aria-labelledby="memberModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <form id="memberForm" class="modal-content" novalidate>
                        <div class="modal-header">
                            <h5 class="modal-title" id="memberModalLabel">Complete / Edit Pending Member</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                                <div id="modalAlert"></div>
                                <input type="hidden" name="type" value="edit">
                                <input type="hidden" name="uid" id="modalUid" value="">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="modalFirstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="modalFirstName" name="firstName" maxlength="50" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="modalLastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="modalLastName" name="lastName" maxlength="50" required>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" id="modalDisplayFullName" name="displayFullName">
                                            <label class="form-check-label" for="modalDisplayFullName">
                                                Display full last name on the public website roster
                                            </label>
                                        </div>
                                        <div class="form-text">
                                            Public roster preview: <strong id="modalRosterNamePreview">Firstname L.</strong>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <h6 class="text-uppercase text-muted small fw-bold mb-3">Mailing Address</h6>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="modalAddress1" class="form-label">Street Address <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="modalAddress1" name="address1" maxlength="255" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="modalAddress2" class="form-label">Address Line 2</label>
                                        <input type="text" class="form-control" id="modalAddress2" name="address2" maxlength="255">
                                    </div>
                                    <div class="col-md-5">
                                        <label for="modalCity" class="form-label">City <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="modalCity" name="city" maxlength="100" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="modalState" class="form-label">State</label>
                                        <input type="text" class="form-control" id="modalState" value="PA" disabled readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="modalZip" class="form-label">Zip Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="modalZip" name="zip" maxlength="5" pattern="\d{5}" required>
                                    </div>
                                </div>

                                <hr>
                                <h6 class="text-uppercase text-muted small fw-bold mb-3">Contact &amp; Emergency Information</h6>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label for="modalText" class="form-label">Cell Phone (10 digits)</label>
                                        <input type="tel" class="form-control" id="modalText" name="text" maxlength="14" placeholder="(717) 555-1234">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="modalEmergencyName" class="form-label">Emergency Contact Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="modalEmergencyName" name="emergency_contact_name" maxlength="255" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="modalEmergencyPhone" class="form-label">Emergency Contact Phone <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="modalEmergencyPhone" name="emergency_contact_phone" maxlength="14" placeholder="(717) 555-5678" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label mb-0">Email Address(es) <span class="text-danger">*</span></label>
                                        <button type="button" id="modalAddEmailBtn" class="btn btn-outline-secondary btn-sm">
                                            <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Add Email
                                        </button>
                                    </div>
                                    <div id="modalEmailContainer" class="d-flex flex-column gap-2"></div>
                                </div>

                                <hr>
                                <div class="mb-2">
                                    <label class="form-label d-block">Instrument(s) <span class="text-danger">*</span></label>
                                    <div class="row row-cols-2 row-cols-md-3 g-2" id="modalInstrumentsContainer">
                                        <?php foreach ($instrumentOptions as $code => $label) { ?>
                                            <div class="col">
                                                <div class="form-check">
                                                    <input class="form-check-input modal-instrument-checkbox"
                                                           type="checkbox"
                                                           name="instrument[]"
                                                           value="<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>"
                                                           id="modalInstr_<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <label class="form-check-label" for="modalInstr_<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="modalSaveBtn" class="btn btn-primary">
                                <i class="fa-solid fa-check me-1" aria-hidden="true"></i>Save &amp; Finalize Member
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        <?php } elseif ($viewMode === 'member_form' && $targetMember !== null) {
            $firstNameVal = (string)($targetMember['firstName'] ?? '');
            $lastNameVal = (string)($targetMember['lastName'] ?? '');
            $displayFullNameChecked = !empty($targetMember['displayFullName']) && (string)$targetMember['displayFullName'] !== '0';
            $address1Val = (string)($targetMember['address1'] ?? '');
            $address2Val = (string)($targetMember['address2'] ?? '');
            $cityVal = (string)($targetMember['city'] ?? '');
            $zipVal = (string)($targetMember['zip'] ?? '');
            $phoneVal = $formatDisplayPhone($targetMember['text'] ?? '');
            $emergNameVal = (string)($targetMember['emergency_contact_name'] ?? '');
            $emergPhoneVal = $formatDisplayPhone($targetMember['emergency_contact_phone'] ?? '');

            $memberEmails = array_values(array_filter(array_map('trim', explode(',', (string)($targetMember['email'] ?? ''))), function ($item) {
                return $item !== '';
            }));
            if (empty($memberEmails)) {
                $memberEmails = [''];
            }

            $memberInstruments = array_values(array_filter(array_map('trim', explode(',', (string)($targetMember['instrument'] ?? ''))), function ($item) {
                return $item !== '';
            }));

            $initialLast = $lastNameVal !== '' ? mb_substr($lastNameVal, 0, 1) . '.' : 'L.';
            $previewInitialText = $displayFullNameChecked
                ? trim($firstNameVal . ' ' . $lastNameVal)
                : trim(($firstNameVal !== '' ? $firstNameVal : 'Firstname') . ' ' . $initialLast);
        ?>
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <div class="pb-3 mb-3 border-bottom">
                        <h1 class="h2 mb-1">Welcome to Keystone Concert Band, <?php echo htmlspecialchars($firstNameVal !== '' ? $firstNameVal : 'Musician', ENT_QUOTES, 'UTF-8'); ?>!</h1>
                        <p class="text-muted mb-0">
                            Your membership has been approved. Please complete the missing information below to finish onboarding and be added to the band roster.
                        </p>
                    </div>

                    <div id="memberFormAlert"></div>

                    <div id="completionSuccessPanel" class="card shadow-sm border-success d-none mb-4">
                        <div class="card-body p-4 text-center">
                            <div class="text-success mb-3">
                                <i class="fa-solid fa-circle-check fa-3x" aria-hidden="true"></i>
                            </div>
                            <h2 class="h4 mt-0 mb-2">Registration Completed!</h2>
                            <p id="completionSuccessMessage" class="mb-4 text-muted"></p>
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <?php if ($isLoggedIn) { ?>
                                    <a href="/members/index.php" class="btn btn-primary">Go to Member Overview</a>
                                    <a href="/members/myInfo.php" class="btn btn-outline-secondary">View My Info</a>
                                <?php } else { ?>
                                    <a href="/members/reauth.php?redirect=%2Fmembers%2Findex.php" class="btn btn-primary">Sign In to Member Area</a>
                                    <a href="/index.php" class="btn btn-outline-secondary">Return to Homepage</a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <div id="pendingInfoCard" class="card shadow-sm">
                        <div class="card-body p-4">
                            <form id="pendingInfoForm" novalidate>
                                <input type="hidden" name="type" value="completePendingInfo">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

                                <h2 class="h5 mt-0 mb-3">Personal &amp; Roster Display Info</h2>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="firstName" name="firstName" maxlength="50"
                                               value="<?php echo htmlspecialchars($firstNameVal, ENT_QUOTES, 'UTF-8'); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="lastName" name="lastName" maxlength="50"
                                               value="<?php echo htmlspecialchars($lastNameVal, ENT_QUOTES, 'UTF-8'); ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" id="displayFullName" name="displayFullName"
                                                   <?php echo $displayFullNameChecked ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="displayFullName">
                                                Display my full last name on the public website roster
                                            </label>
                                        </div>
                                        <div class="form-text">
                                            If unchecked, only your last initial is shown publicly. Roster preview:
                                            <strong id="rosterNamePreview"><?php echo htmlspecialchars($previewInitialText, ENT_QUOTES, 'UTF-8'); ?></strong>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">
                                <h2 class="h5 mt-0 mb-3">Mailing Address</h2>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="address1" class="form-label">Street Address <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="address1" name="address1" maxlength="255"
                                               value="<?php echo htmlspecialchars($address1Val, ENT_QUOTES, 'UTF-8'); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="address2" class="form-label">Address Line 2 <span class="text-muted small">(Optional)</span></label>
                                        <input type="text" class="form-control" id="address2" name="address2" maxlength="255"
                                               value="<?php echo htmlspecialchars($address2Val, ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                    <div class="col-md-5">
                                        <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="city" name="city" maxlength="100"
                                               value="<?php echo htmlspecialchars($cityVal, ENT_QUOTES, 'UTF-8'); ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="state" class="form-label">State</label>
                                        <input type="text" class="form-control" id="state" value="PA" disabled readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="zip" class="form-label">Zip Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="zip" name="zip" maxlength="5" pattern="\d{5}"
                                               value="<?php echo htmlspecialchars($zipVal, ENT_QUOTES, 'UTF-8'); ?>" placeholder="17101" required>
                                    </div>
                                </div>

                                <hr class="my-4">
                                <h2 class="h5 mt-0 mb-3">Contact &amp; Emergency Information</h2>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label for="text" class="form-label">Cell Phone <span class="text-muted small">(Optional)</span></label>
                                        <input type="tel" class="form-control" id="text" name="text" maxlength="14"
                                               value="<?php echo htmlspecialchars($phoneVal, ENT_QUOTES, 'UTF-8'); ?>" placeholder="(717) 555-1234">
                                        <div class="form-text">10-digit number for urgent rehearsal/concert alerts.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="emergency_contact_name" class="form-label">Emergency Contact Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" maxlength="255"
                                               value="<?php echo htmlspecialchars($emergNameVal, ENT_QUOTES, 'UTF-8'); ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" maxlength="14"
                                               value="<?php echo htmlspecialchars($emergPhoneVal, ENT_QUOTES, 'UTF-8'); ?>" placeholder="(717) 555-5678" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label mb-0">Email Address(es) <span class="text-danger">*</span></label>
                                        <button type="button" id="memberAddEmailBtn" class="btn btn-outline-secondary btn-sm">
                                            <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Add Another Email
                                        </button>
                                    </div>
                                    <div id="memberEmailContainer" class="d-flex flex-column gap-2">
                                        <?php foreach ($memberEmails as $idx => $eml) { ?>
                                            <div class="input-group email-row">
                                                <input type="email" class="form-control" name="email[]" maxlength="100"
                                                       value="<?php echo htmlspecialchars($eml, ENT_QUOTES, 'UTF-8'); ?>"
                                                       placeholder="name@example.com" required>
                                                <button type="button" class="btn btn-outline-danger remove-email-btn<?php echo count($memberEmails) <= 1 ? ' d-none' : ''; ?>"
                                                        title="Remove email" aria-label="Remove email">
                                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>

                                <hr class="my-4">
                                <h2 class="h5 mt-0 mb-2">Instrument(s) <span class="text-danger">*</span></h2>
                                <p class="text-muted small mb-3">Select all instruments you play with the band.</p>
                                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-2 mb-4" id="memberInstrumentsContainer">
                                    <?php foreach ($instrumentOptions as $code => $label) {
                                        $isChecked = in_array($code, $memberInstruments, true);
                                    ?>
                                        <div class="col">
                                            <div class="form-check">
                                                <input class="form-check-input member-instrument-checkbox"
                                                       type="checkbox"
                                                       name="instrument[]"
                                                       value="<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>"
                                                       id="instr_<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>"
                                                       <?php echo $isChecked ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="instr_<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>

                                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 pt-3 border-top">
                                    <div class="small text-muted">
                                        <i class="fa-solid fa-envelope-circle-check me-1" aria-hidden="true"></i>
                                        Upon submission, your completed details will be emailed to our Webmaster and Band Director.
                                    </div>
                                    <button type="submit" id="submitPendingInfoBtn" class="btn btn-primary btn-lg text-nowrap">
                                        <i class="fa-solid fa-paper-plane me-2" aria-hidden="true"></i>Submit Member Information
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <?php } elseif ($viewMode === 'already_completed') { ?>
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-7">
                    <div class="card shadow-sm">
                        <div class="card-body p-4 text-center">
                            <div class="text-success mb-3">
                                <i class="fa-solid fa-circle-check fa-3x" aria-hidden="true"></i>
                            </div>
                            <h1 class="h3 mb-2">Your Member Information Is Complete</h1>
                            <p class="text-muted mb-4">
                                Thank you! Your Keystone Concert Band profile has already been completed and submitted.
                                You can review or update your contact and emergency details anytime in My Info.
                            </p>
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <?php if ($isLoggedIn) { ?>
                                    <a href="/members/myInfo.php" class="btn btn-primary">Go to My Info</a>
                                    <a href="/members/index.php" class="btn btn-outline-secondary">Member Overview</a>
                                <?php } else { ?>
                                    <a href="/members/reauth.php?redirect=%2Fmembers%2FmyInfo.php" class="btn btn-primary">Sign In to Member Area</a>
                                    <a href="/index.php" class="btn btn-outline-secondary">Return to Homepage</a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php } else { ?>
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-7">
                    <div class="card shadow-sm border-warning">
                        <div class="card-body p-4 text-center">
                            <div class="text-warning mb-3">
                                <i class="fa-solid fa-link-slash fa-3x" aria-hidden="true"></i>
                            </div>
                            <h1 class="h3 mb-2">Onboarding Link Unavailable or Already Used</h1>
                            <p class="text-muted mb-4">
                                This pending member onboarding link is invalid, has expired, or has already been submitted.
                                If you have already been activated, you can sign in with your email address to access the member portal.
                            </p>
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <a href="/members/reauth.php?redirect=%2Fmembers%2FpendingMembers.php" class="btn btn-primary">Sign In with Member Email</a>
                                <a href="/join.php" class="btn btn-outline-secondary">Membership Application</a>
                                <a href="/contact.php" class="btn btn-outline-secondary">Contact Us</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </main>

    <div class="container mt-auto">
        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    </div>
    <?php require_once __DIR__ . '/../includes/common_js.php'; ?>
    <?php if ($viewMode === 'admin') { ?>
        <?php require_once __DIR__ . '/../includes/common_datatables.php'; ?>
    <?php } ?>
    <?php
    $pendingJsSri = null;
    $pendingJsUrl = asset('/assets/js/pendingMembers.js', $pendingJsSri);
    ?>
    <script src="<?php echo htmlspecialchars($pendingJsUrl, ENT_QUOTES, 'UTF-8'); ?>"<?php if ($pendingJsSri) { echo ' integrity="' . htmlspecialchars($pendingJsSri, ENT_QUOTES, 'UTF-8') . '" crossorigin="anonymous"'; } ?>></script>
</body>
</html>

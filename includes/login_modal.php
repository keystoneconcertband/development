<?php
$rememberedEmail = '';
if (!empty($_COOKIE['KCB_Cookie'])) {
    $cookieParts = explode(',', $_COOKIE['KCB_Cookie'], 2);
    if (!empty($cookieParts[0]) && filter_var($cookieParts[0], FILTER_VALIDATE_EMAIL)) {
        $rememberedEmail = $cookieParts[0];
    }
}
?>
<!-- KCB Member Login Modal -->
<div id="loginModal" class="modal fade" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel"><i class="fa fa-lock me-2 text-primary" aria-hidden="true"></i>KCB Member Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="loginAlert" class="alert d-none" role="alert"></div>

                <p id="loginIntro" class="text-muted small">
                    <?= !empty($rememberedEmail)
                        ? 'Welcome back! Click <strong>Continue</strong> to sign in to the member area on this device.'
                        : 'Access to the member area requires that you be an active band member. Enter your email on file to log in or receive a one-time verification code.' ?>
                </p>

                <form class="row g-3" id="frmLogin" name="frmLogin" novalidate>
                    <div class="col-12" id="div_email">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-envelope" aria-hidden="true"></i></span>
                            <input type="email" class="form-control" id="email" placeholder="name@example.com" autocomplete="email" inputmode="email" value="<?= htmlspecialchars($rememberedEmail, ENT_QUOTES, 'UTF-8') ?>" required>
                            <button class="btn btn-outline-secondary d-none" type="button" id="btnChangeEmail" title="Change email address">Change</button>
                        </div>
                    </div>

                    <div class="col-12 d-none" id="div_auth">
                        <label for="auth_cd" class="form-label">6-Digit Verification Code</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fa fa-key" aria-hidden="true"></i></span>
                            <input type="text" class="form-control" id="auth_cd" placeholder="123456" maxlength="6" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code">
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="auth_remember" checked>
                                <label class="form-check-label small" for="auth_remember">Remember me on this device</label>
                            </div>
                            <div>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" id="btnResendCode" disabled>
                                    Resend code (<span id="resendTimer">60</span>s)
                                </button>
                            </div>
                        </div>
                        <p class="form-text small text-muted mb-0">
                            Check your inbox (and spam folder) for your 6-digit code. The code is valid for 15 minutes.
                        </p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="frmLogin" class="btn btn-primary" id="memberLogin">
                    <output class="spinner-border spinner-border-sm me-1 d-none" id="loginSpinner" aria-hidden="true"></output>
                    <span id="memberLoginText">Continue</span>
                </button>
            </div>
        </div>
    </div>
</div>

// KCB Member Login Handler
(function () {
  'use strict';

  const loginModalEl = document.querySelector('#loginModal');
  const frmLogin = document.querySelector('#frmLogin');
  const emailInput = document.querySelector('#email');
  const authCdInput = document.querySelector('#auth_cd');
  const authRememberInput = document.querySelector('#auth_remember');
  const divAuth = document.querySelector('#div_auth');
  const memberLoginButton = document.querySelector('#memberLogin');
  const memberLoginText = document.querySelector('#memberLoginText');
  const loginSpinner = document.querySelector('#loginSpinner');
  const loginAlert = document.querySelector('#loginAlert');
  const btnChangeEmail = document.querySelector('#btnChangeEmail');
  const btnResendCode = document.querySelector('#btnResendCode');
  const resendTimerEl = document.querySelector('#resendTimer');

  let resendInterval = null;
  let isSubmitting = false;

  // Retrieve any redirect parameter from URL
  function getRedirectParam() {
    const params = new URLSearchParams(window.location.search);
    const redirect = params.get('redirect');
    if (redirect && redirect.startsWith('/members/') && !redirect.startsWith('//') && !redirect.includes('\\')) {
      return redirect;
    }
    return '';
  }

  function showAlert(message, type = 'danger') {
    if (!loginAlert) return;
    loginAlert.className = `alert alert-${type} py-2 mb-3`;
    loginAlert.innerHTML = message;
    loginAlert.classList.remove('d-none');
  }

  function hideAlert() {
    if (!loginAlert) return;
    loginAlert.classList.add('d-none');
    loginAlert.textContent = '';
  }

  function setLoading(loading, text = 'Submitting...') {
    isSubmitting = loading;
    if (memberLoginButton) {
      memberLoginButton.disabled = loading;
    }
    if (loginSpinner) {
      if (loading) {
        loginSpinner.classList.remove('d-none');
      } else {
        loginSpinner.classList.add('d-none');
      }
    }
    if (memberLoginText) {
      if (loading) {
        memberLoginText.textContent = text;
      } else {
        const isStep2 = divAuth && !divAuth.classList.contains('d-none');
        memberLoginText.textContent = isStep2 ? 'Log In' : 'Continue';
      }
    }
  }

  function startResendCooldown(seconds = 60) {
    if (resendInterval) {
      clearInterval(resendInterval);
      resendInterval = null;
    }

    if (!btnResendCode) return;

    let remaining = seconds;
    btnResendCode.disabled = true;
    if (resendTimerEl) {
      resendTimerEl.textContent = remaining;
    }
    btnResendCode.innerHTML = `Resend code (<span id="resendTimer">${remaining}</span>s)`;

    resendInterval = setInterval(() => {
      remaining--;
      const timerSpan = document.querySelector('#resendTimer');
      if (timerSpan) {
        timerSpan.textContent = remaining;
      }
      if (remaining <= 0) {
        clearInterval(resendInterval);
        resendInterval = null;
        btnResendCode.disabled = false;
        btnResendCode.textContent = 'Resend code';
      }
    }, 1000);
  }

  function showStep2(message = '', cooldown = 60) {
    if (divAuth) {
      divAuth.classList.remove('d-none');
    }
    if (emailInput) {
      emailInput.readOnly = true;
    }
    if (btnChangeEmail) {
      btnChangeEmail.classList.remove('d-none');
    }
    if (memberLoginText) {
      memberLoginText.textContent = 'Log In';
    }
    if (authCdInput) {
      authCdInput.value = '';
      setTimeout(() => authCdInput.focus(), 150);
    }
    if (message) {
      showAlert(message, 'info');
    }
    startResendCooldown(cooldown || 60);
  }

  function resetToStep1() {
    if (resendInterval) {
      clearInterval(resendInterval);
      resendInterval = null;
    }
    if (divAuth) {
      divAuth.classList.add('d-none');
    }
    if (btnChangeEmail) {
      btnChangeEmail.classList.add('d-none');
    }
    if (emailInput) {
      emailInput.readOnly = false;
      emailInput.focus();
    }
    if (authCdInput) {
      authCdInput.value = '';
    }
    if (memberLoginText) {
      memberLoginText.textContent = 'Continue';
    }
    if (btnResendCode) {
      btnResendCode.disabled = true;
      btnResendCode.innerHTML = 'Resend code (<span id="resendTimer">60</span>s)';
    }
    hideAlert();
  }

  async function postAuth(payload) {
    const response = await fetch('/api/auth.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Cache-Control': 'no-cache',
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error(`Server returned error code ${response.status}`);
    }

    return await response.json();
  }

  async function handleLoginSubmit(event) {
    if (event) {
      event.preventDefault();
    }

    if (isSubmitting) return;

    hideAlert();

    const email = emailInput ? emailInput.value.trim() : '';
    const authCd = authCdInput ? authCdInput.value.trim() : '';
    const isStep2 = divAuth && !divAuth.classList.contains('d-none');
    const redirect = getRedirectParam();

    if (!email) {
      showAlert('Please enter your email address to continue.');
      if (emailInput) emailInput.focus();
      return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      showAlert('Please enter a valid email address.');
      if (emailInput) emailInput.focus();
      return;
    }

    // Step 1: Request Code or verify Remember-Me
    if (!isStep2 || !authCd) {
      setLoading(true, 'Checking...');
      try {
        const result = await postAuth({ email, redirect });
        setLoading(false);

        if (result.status === 'valid') {
          showAlert('Login successful! Redirecting...', 'success');
          window.location.href = result.redirect || '/members/index.php';
          return;
        }

        if (result.status === 'auth_required' || result.status === 'auth_cd_not_expired' || result.status === 'auth_failed_invalid_cookie') {
          showStep2(result.message, result.cooldown || 60);
          return;
        }

        if (result.status === 'resend_throttle') {
          showStep2(result.message, result.remaining || 30);
          return;
        }

        if (result.status === 'over_max_requests') {
          showAlert(result.message || 'Account temporarily locked. Please try again later.', 'danger');
          return;
        }

        showAlert(result.message || 'Unable to log in with that email address.', 'danger');
        if (emailInput) emailInput.focus();
      } catch (err) {
        setLoading(false);
        showAlert('Network or server communication error. Please try again later.', 'danger');
      }
      return;
    }

    // Step 2: Validate 6-digit Code
    if (authCd.length < 6) {
      showAlert('Please enter the complete 6-digit login code.');
      if (authCdInput) authCdInput.focus();
      return;
    }

    const rememberMe = authRememberInput ? authRememberInput.checked : true;
    setLoading(true, 'Verifying...');

    try {
      const result = await postAuth({
        email,
        auth_cd: authCd,
        auth_remember: rememberMe,
        redirect,
      });
      setLoading(false);

      if (result.status === 'valid') {
        showAlert('Verification successful! Redirecting...', 'success');
        window.location.href = result.redirect || '/members/index.php';
        return;
      }

      if (result.status === 'auth_invalid') {
        showAlert(result.message || 'The code entered was incorrect. Please try again.', 'danger');
        if (authCdInput) {
          authCdInput.value = '';
          authCdInput.focus();
        }
        return;
      }

      if (result.status === 'auth_old') {
        showAlert(result.message || 'That code has expired. A new code was sent to your email.', 'warning');
        if (authCdInput) {
          authCdInput.value = '';
          authCdInput.focus();
        }
        startResendCooldown(result.cooldown || 60);
        return;
      }

      if (result.status === 'over_max_requests') {
        showAlert(result.message || 'Too many incorrect attempts. Account locked.', 'danger');
        if (memberLoginButton) memberLoginButton.disabled = true;
        return;
      }

      showAlert(result.message || 'Unable to verify login code. Please try again.', 'danger');
    } catch (err) {
      setLoading(false);
      showAlert('Network or server communication error. Please try again later.', 'danger');
    }
  }

  // Resend code action
  async function handleResendCode() {
    if (btnResendCode && btnResendCode.disabled) return;

    const email = emailInput ? emailInput.value.trim() : '';
    if (!email) {
      resetToStep1();
      return;
    }

    hideAlert();
    setLoading(true, 'Sending new code...');

    try {
      const result = await postAuth({ email });
      setLoading(false);

      if (result.status === 'auth_required' || result.status === 'auth_old' || result.status === 'auth_cd_not_expired') {
        showAlert('A new login code has been sent to your email.', 'info');
        startResendCooldown(result.cooldown || 60);
        if (authCdInput) {
          authCdInput.value = '';
          authCdInput.focus();
        }
      } else if (result.status === 'resend_throttle') {
        showAlert(result.message, 'warning');
        startResendCooldown(result.remaining || 30);
      } else {
        showAlert(result.message || 'Unable to send a new code. Please try again.', 'danger');
      }
    } catch (err) {
      setLoading(false);
      showAlert('Unable to dispatch a new code. Please check your connection and try again.', 'danger');
    }
  }

  // Event Listeners
  if (frmLogin) {
    frmLogin.addEventListener('submit', (e) => {
      void handleLoginSubmit(e);
    });
  }

  if (memberLoginButton) {
    memberLoginButton.addEventListener('click', (e) => {
      e.preventDefault();
      void handleLoginSubmit(e);
    });
  }

  if (btnChangeEmail) {
    btnChangeEmail.addEventListener('click', (e) => {
      e.preventDefault();
      resetToStep1();
    });
  }

  if (btnResendCode) {
    btnResendCode.addEventListener('click', (e) => {
      e.preventDefault();
      void handleResendCode();
    });
  }

  if (loginModalEl) {
    loginModalEl.addEventListener('shown.bs.modal', () => {
      const isStep2 = divAuth && !divAuth.classList.contains('d-none');
      if (isStep2 && authCdInput) {
        authCdInput.focus();
      } else if (emailInput) {
        if (emailInput.value) {
          if (memberLoginButton) memberLoginButton.focus();
        } else {
          emailInput.focus();
        }
      }
    });

    loginModalEl.addEventListener('hidden.bs.modal', () => {
      resetToStep1();
      if (emailInput && !emailInput.defaultValue) {
        emailInput.value = '';
      } else if (emailInput && emailInput.defaultValue) {
        emailInput.value = emailInput.defaultValue;
      }
    });
  }

  // Auto-open modal if URL specifies ?login=1 or redirected from reauth
  document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('login') === '1' && loginModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
      const modalInstance = bootstrap.Modal.getOrCreateInstance(loginModalEl);
      modalInstance.show();
    }
  });
})();

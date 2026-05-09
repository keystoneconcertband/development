const loginModal = document.querySelector('#loginModal');
const emailInput = document.querySelector('#email');
const authCdInput = document.querySelector('#auth_cd');
const authRememberInput = document.querySelector('#auth_remember');
const divAuth = document.querySelector('#div_auth');
const memberLoginButton = document.querySelector('#memberLogin');

function getInputValue(input) {
  return input ? input.value : '';
}

function setInputValue(input, value) {
  if (input) {
    input.value = value;
  }
}

function focusElement(element) {
  if (element && typeof element.focus === 'function') {
    element.focus();
  }
}

function showAuthSection() {
  if (divAuth) {
    divAuth.classList.remove('d-none');
  }
}

function hideAuthSection() {
  if (divAuth) {
    divAuth.classList.add('d-none');
  }
}

function noCacheGet(url, params) {
  const queryString = new URLSearchParams(params).toString();
  return fetch(`${url}?${queryString}`, { cache: 'no-cache' }).then((response) => response.text());
}

function handleLoginResponse(msg) {
  if (msg === 'valid') {
    window.location = 'members/index.php';
    return;
  }

  if (msg === 'invalid' || msg === 'invalid_pending') {
    alert('Sorry that email address is not valid for login.');
    setInputValue(emailInput, '');
    focusElement(emailInput);
    return;
  }

  if (msg === 'invalid_request') {
    alert('Email address is required for login.');
    focusElement(emailInput);
    return;
  }

  if (msg === 'auth_required_no_cookie' || msg === 'auth_cd_not_expired') {
    showAuthSection();
    return;
  }

  if (msg === 'auth_failed_invalid_cookie') {
    alert('Sorry, your cookie has either been lost or corrupted. Please re-authenticate.');
    showAuthSection();
    return;
  }

  if (msg.startsWith('over_max_requests')) {
    const timeout = msg.substring(msg.indexOf('__') + 2);
    alert(`Sorry, your account has been disabled due to too many invalid login attempts.\n\nPlease try again after ${timeout}`);
    return;
  }

  if (msg === 'db_error') {
    alert('Oops! We had a problem communicating with the database. Please try again later.');
    return;
  }

  alert(`Unable to login. Please try again later. Msg: ${msg}`);
  focusElement(emailInput);
}

function handleAuthResponse(msg) {
  if (msg === 'valid') {
    window.location = 'members/index.php';
    return;
  }

  if (msg === 'auth_invalid') {
    alert('Sorry that auth code was not valid.');
    setInputValue(authCdInput, '');
    focusElement(authCdInput);
    return;
  }

  if (msg === 'invalid') {
    alert('Sorry that email address is not valid for login.');
    setInputValue(emailInput, '');
    focusElement(emailInput);
    return;
  }

  if (msg === 'auth_old') {
    alert('That Auth Code has expired. A new auth code has been sent.');
    setInputValue(authCdInput, '');
    focusElement(authCdInput);
    return;
  }

  if (msg.startsWith('over_max_requests')) {
    const timeout = msg.substring(msg.indexOf('__') + 2);
    alert(`Sorry, you incorrectly entered the auth code 3 times. Your account has been disabled for one hour.\n\nPlease try again after ${timeout}`);
    return;
  }

  if (msg === 'db_error') {
    alert('Oops! We had a problem communicating with the database. Please try again later.');
    return;
  }

  alert(`Unable to validate auth code. Msg: ${msg}`);
  focusElement(emailInput);
}

function handleFacebookLoginResponse(msg) {
  if (msg === 'fb_valid') {
    window.location = 'members/index.php';
    return;
  }

  if (msg === 'valid') {
    alert('Something terribly went wrong. This status is not valid. Please contact web@keystoneconcertband.com for help!');
    return;
  }

  if (msg === 'sig_not_match') {
    alert('Please retry. Validation of the facebook authentication failed.');
    return;
  }

  if (msg === 'fb_session_hijack') {
    alert('Please retry. Your facebook session cookie has expired.');
    return;
  }

  if (msg === 'no_fb_cookie') {
    alert('Please retry. You are missing the facebook cookie. Re-authenticating usually fixes it.');
    return;
  }

  if (msg === 'invalid') {
    alert('Sorry that email address is not in our system. You must be an active member to login.');
    setInputValue(emailInput, '');
    focusElement(emailInput);
    return;
  }

  if (msg === 'db_error') {
    alert('Oops! We had a problem communicating with the database. Please try again later.');
    return;
  }

  alert(`Unable to validate facebook login. Msg: ${msg}`);
}

if (loginModal) {
  loginModal.addEventListener('shown.bs.modal', () => {
    focusElement(emailInput);
  });

  loginModal.addEventListener('hide.bs.modal', () => {
    setInputValue(emailInput, '');
    setInputValue(authCdInput, '');
    hideAuthSection();
  });
}

if (memberLoginButton) {
  memberLoginButton.addEventListener('click', () => {
    const email = getInputValue(emailInput).trim();
    const authCd = getInputValue(authCdInput).trim();

    if (!email) {
      alert('Email address is required to continue.');
      focusElement(emailInput);
      return false;
    }

    if (!authCd) {
      noCacheGet('/membersServer.php', { email })
        .then(handleLoginResponse)
        .catch(() => {
          alert('Unable to login. Please try again later.');
        });
      return;
    }

    const authRemember = authRememberInput ? authRememberInput.checked : false;

    noCacheGet('/membersServer.php', {
      email,
      auth_cd: authCd,
      auth_remember: authRemember,
    })
      .then(handleAuthResponse)
      .catch(() => {
        alert('Unable to validate auth code. Please try again later.');
      });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const modalContents = document.querySelectorAll('.modal-content');

  modalContents.forEach((modalContent) => {
    modalContent.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        event.preventDefault();
        if (memberLoginButton) {
          memberLoginButton.click();
        }
      }
    });
  });
});

function statusChangeCallback(response) {
  if (response.status === 'connected') {
    console.log('Connected!');
    FB.api('/me', { fields: 'name, email' }, (fbResponse) => {
      console.log(JSON.stringify(fbResponse));
      noCacheGet('/membersServer.php', { email: fbResponse.email, fb_id: fbResponse.id })
        .then(handleFacebookLoginResponse)
        .catch(() => {
          alert('Unable to validate facebook login. Please try again later.');
        });
    });
  }
}

function checkLoginState() {
  FB.getLoginStatus((response) => {
    statusChangeCallback(response);
  });
}

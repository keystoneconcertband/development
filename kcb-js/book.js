document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('frmBook');
  if (!form) return;

  form.addEventListener('submit', function (event) {
    if (event.defaultPrevented || !form.checkValidity()) {
      event.preventDefault();
      formError();
      submitMSG(false, 'Oops! Looks like you have a validation error. Check for errors in the form.');
    } else {
      event.preventDefault();
      submitForm();
    }
  });
});

function submitForm() {
  const form = document.getElementById('frmBook');
  if (!form) return;

  const formData = new FormData(form);

  fetch('bookServer.php', {
    method: 'POST',
    body: formData,
    headers: {
      'Accept': 'application/json'
    }
  })
    .then((response) => response.json())
    .then((text) => {
      if (text === 'success') {
        formSuccess();
      } else {
        formError();
        submitMSG(false, text);
      }
    })
    .catch((xhr) => {
      submitMSG(false, 'Oops! An error occurred processing the form. Please try again later.');
      console.log(xhr);
    });
}

function formSuccess() {
  const form = document.getElementById('frmBook');
  if (form) {
    form.reset();
  }
  submitMSG(true, 'Thanks for submitting your information. We will reply back shortly.');
}

function formError() {
  const form = document.getElementById('frmBook');
  if (!form) return;
  form.classList.add('shake', 'animated');
  function removeClasses() {
    form.classList.remove('shake', 'animated');
    form.removeEventListener('animationend', removeClasses);
    form.removeEventListener('webkitAnimationEnd', removeClasses);
    form.removeEventListener('mozAnimationEnd', removeClasses);
    form.removeEventListener('MSAnimationEnd', removeClasses);
    form.removeEventListener('oanimationend', removeClasses);
  }
  form.addEventListener('animationend', removeClasses);
  form.addEventListener('webkitAnimationEnd', removeClasses);
  form.addEventListener('mozAnimationEnd', removeClasses);
  form.addEventListener('MSAnimationEnd', removeClasses);
  form.addEventListener('oanimationend', removeClasses);
}

function submitMSG(valid, msg) {
  const msgSubmit = document.getElementById('msgSubmit');
  if (!msgSubmit) return;

  msgSubmit.className = valid ? 'h4 tada animated text-success' : 'h4 text-danger';
  msgSubmit.textContent = msg;
}

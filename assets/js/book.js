document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('frmBook');
  if (!form) return;

  const jsCheck = document.getElementById('jsCheck');
  if (jsCheck) {
    jsCheck.value = 'enabled';
  }

  form.addEventListener('submit', function (event) {
    if (event.defaultPrevented || !form.checkValidity()) {
      event.preventDefault();
      shakeForm(form);
      showAlert('#formAlert', false, 'Oops! Looks like you have a validation error. Check for errors in the form.');
    } else {
      event.preventDefault();
      submitForm();
    }
  });
});

function submitForm() {
  const form = document.getElementById('frmBook');
  if (!form) return;

  postFormData('bookServer.php', form)
    .then((text) => {
      if (text === 'success') {
        formSuccess();
      } else {
        shakeForm(form);
        showAlert('#formAlert', false, text);
      }
    })
    .catch((xhr) => {
      showAlert('#formAlert', false, 'Oops! An error occurred processing the form. Please try again later.');
      console.log(xhr);
    });
}

function formSuccess() {
  const form = document.getElementById('frmBook');
  if (form) {
    form.reset();
  }
    showAlert('#formAlert', true, "Thanks for submitting your information. We will reply back shortly.");
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('frmJoin');
    const jsCheck = document.getElementById('jsCheck');
    if (jsCheck) {
        jsCheck.value = 'enabled';
    }
    if (!form) return;

    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            shakeForm(form);
            showAlert('#formAlert', false, "Oops! Looks like you have a validation error. Check for errors in the form.");
        } else {
            event.preventDefault();
            submitForm();
        }
    });
});

function submitForm() {
    const form = document.getElementById('frmJoin');
    if (!form) return;

    postFormData('joinServer.php', form)
    .then(function (text) {
        if (text === "success") {
            formSuccess();
        } else {
            shakeForm(form);
            showAlert('#formAlert', false, text);
        }
    })
    .catch(function (error) {
        shakeForm(form);
        showAlert('#formAlert', false, "Oops! An error occurred processing the form. Please try again later.");
        console.log(error);
    });
}

function formSuccess() {
    const form = document.getElementById('frmJoin');
    if (form) {
        form.reset();
    }
    showAlert('#formAlert', true, "Thanks for submitting your information. We will reply back shortly.");
}

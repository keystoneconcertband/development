document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('frmJoin');
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            formError();
            showAlert(false, "Oops! Looks like you have a validation error. Check for errors in the form.");
        } else {
            event.preventDefault();
            submitForm();
        }
    });
});
function submitForm() {
    const form = document.getElementById('frmJoin');
    const formData = new FormData(form);

    fetch('joinServer.php', {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(text => {
        if (text === "success") {
            formSuccess();
        } else {
            formError();
            showAlert(false, text);
        }
    })
    .catch(error => {
        formError();
        showAlert(false, "Oops! An error occurred processing the form. Please try again later.");
        console.log(error);
    });
}

function formSuccess() {
    document.getElementById('frmJoin').reset();
    showAlert(true, "Thanks for submitting your information. We will reply back shortly.");
}

function formError() {
    const form = document.getElementById('frmJoin');
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

function showAlert(valid, msg) {
    const alertBox = document.getElementById('formAlert');
    alertBox.className = 'alert alert-dismissible fade show';
    alertBox.classList.add(valid ? 'alert-success' : 'alert-danger');
    alertBox.innerHTML = `${msg} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
    alertBox.classList.remove('d-none');
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('frmJoin');
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            formError();
            submitMSG(false, "Oops! Looks like you have a validation error. Check for errors in the form.");
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
            submitMSG(false, text);
        }
    })
    .catch(error => {
        submitMSG(false, "Oops! An error occurred processing the form. Please try again later.");
        console.log(error);
    });
}

function formSuccess() {
    document.getElementById('frmJoin').reset();
    submitMSG(true, "Thanks for submitting your information. We will reply back shortly.");
}

function formError() {
    const form = document.getElementById('frmJoin');
    form.classList.remove(...form.classList);
    form.classList.add('shake', 'animated');
    function removeClasses() {
        form.classList.remove(...form.classList);
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
    let msgClasses;
    if (valid) {
        msgClasses = ['h4', 'tada', 'animated', 'text-success'];
    } else {
        msgClasses = ['h4', 'text-danger'];
    }
    msgSubmit.className = '';
    msgSubmit.classList.add(...msgClasses);
    msgSubmit.textContent = msg;
}

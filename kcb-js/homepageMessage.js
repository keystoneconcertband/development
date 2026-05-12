document.addEventListener('DOMContentLoaded', function() {
    var table = $('#kcbMessageTable').DataTable({
        responsive: true,
        order: [4, 'desc'],
        ajax: {
            url: 'homepageMessageServer.php',
            dataSrc: ''
        },
        columns: [
            { data: null, render: function (data) {
                var title = data.title.replace(/'/g, '&#96;');
                return '<a href="#nojump"><span class="fa fa-edit" onclick="showEditRecord(' + data.uid + ')"></span></a>';
            }},
            { data: 'title' },
            { data: 'message' },
            { data: 'message_type' },
            { data: 'start_dt' },
            { data: 'end_dt' }
        ]
    });
});

const formMessage = document.getElementById('form_message');
if (formMessage) {
    formMessage.addEventListener('submit', function (event) {
        if (event.defaultPrevented || !formMessage.checkValidity()) {
            event.preventDefault();
            formError('Check for errors in the form.');
        } else {
            event.preventDefault();
            submitForm();
        }
    });
}

const modalAddEdit = document.getElementById('modal_add_edit');
if (modalAddEdit) {
    modalAddEdit.addEventListener('show.bs.modal', function () {
        const msgMainHeader = document.getElementById('msgMainHeader');
        const msgSubmit = document.getElementById('msgSubmit');
        if (msgMainHeader) msgMainHeader.className = '';
        if (msgMainHeader) msgMainHeader.textContent = '';
        if (msgSubmit) msgSubmit.className = '';
        if (msgSubmit) msgSubmit.textContent = '';
    });

    modalAddEdit.addEventListener('hidden.bs.modal', function () {
        if (formMessage) {
            formMessage.reset();
        }
        const uid = document.getElementById('uid');
        if (uid) uid.value = '';
    });
}

function submitForm() {
    const uid = document.getElementById('uid');
    if (uid && uid.value !== '') {
        editRecord();
    } else {
        addRecord();
    }
}

function addRecord() {
    if (!formMessage) return;
    const formData = new URLSearchParams(new FormData(formMessage));
    formData.append('type', 'add');

    fetch('homepageMessageServer.php', {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then((response) => response.json())
    .then((text) => {
        if (text === 'success') {
            formSuccess('Item successfully added.');
        } else {
            formError(text);
        }
    })
    .catch((xhr) => {
        submitMSG(false, 'Oops! An error occurred processing the form. Please try again later.');
        console.log(xhr);
    });
}

function showEditRecord(uid) {
    const params = new URLSearchParams({ type: 'getHomepageMessageRecord', uid: uid.toString() });

    fetch('homepageMessageServer.php', {
        method: 'POST',
        body: params,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then((response) => response.json())
    .then((data) => {
        populateForm('#form_message', data);
        const uidField = document.getElementById('uid');
        if (uidField) uidField.value = uid;
        const modal = document.getElementById('modal_add_edit');
        if (modal) {
            var bsModal = bootstrap.Modal.getOrCreateInstance(modal);
            bsModal.show();
        }
    })
    .catch((xhr) => {
        submitMSG(false, 'Oops! An error occurred opening the form. Please try again later.');
        console.log(xhr);
    });
}

function editRecord() {
    if (!formMessage) return;
    const formData = new URLSearchParams(new FormData(formMessage));
    formData.append('type', 'edit');

    fetch('homepageMessageServer.php', {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then((response) => response.json())
    .then((text) => {
        if (text === 'success') {
            formSuccess('Item successfully modified.');
        } else {
            formError(text);
        }
    })
    .catch((xhr) => {
        submitMSG(false, 'Oops! An error occurred processing the form. Please try again later.');
        console.log(xhr);
    });
}

function checkDates(date) {
    const params = new URLSearchParams({ type: 'homepageMessageDateConflictCheck', date: date });

    fetch('homepageMessageServer.php', {
        method: 'POST',
        body: params,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then((response) => response.json())
    .then((data) => {
        if (data !== 0) {
            console.log('conflict');
            formError('Date conflicts with message already in system.');
        } else {
            console.log('here');
            const msgMainHeader = document.getElementById('msgMainHeader');
            const msgSubmit = document.getElementById('msgSubmit');
            if (msgMainHeader) {
                msgMainHeader.className = '';
                msgMainHeader.textContent = '';
            }
            if (msgSubmit) {
                msgSubmit.className = '';
                msgSubmit.textContent = '';
            }
        }
    })
    .catch((xhr) => {
        submitMSG(false, 'Oops! An error occurred opening the form. Please try again later.');
        console.log(xhr);
    });
}

function formSuccess(text) {
    submitMSG(true, text);
    var table = $('#kcbMessageTable').DataTable();
    if (table) {
        table.ajax.reload();
    }
    var modal = document.getElementById('modal_add_edit');
    if (modal) {
        var bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) bsModal.hide();
    }
}

function formError(text) {
    const form = document.getElementById('form_message');
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
    submitMSG(false, text);
}

function submitMSG(valid, msg) {
    const msgMainHeader = document.getElementById('msgMainHeader');
    const msgSubmit = document.getElementById('msgSubmit');
    const msgClasses = valid ? 'h4 tada animated text-success' : 'h4 text-danger';
    if (msgMainHeader) msgMainHeader.className = msgClasses;
    if (msgMainHeader) msgMainHeader.textContent = msg;
    if (msgSubmit) msgSubmit.className = msgClasses;
    if (msgSubmit) msgSubmit.textContent = msg;
}

function populateForm(frm, data) {
    var form = document.querySelector(frm);
    if (!form) return;
    Object.keys(data).forEach(function(key) {
        var field = form.querySelector('[name="' + key + '"]');
        if (field) {
            field.value = data[key];
        }
    });
}

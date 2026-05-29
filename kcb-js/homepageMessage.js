var homepageMessageDateConflict = false;

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
        formMessage.classList.add('was-validated');
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
    modalAddEdit.addEventListener('hidden.bs.modal', function () {
        if (formMessage) {
            formMessage.reset();
            formMessage.classList.remove('was-validated');
        }
        var uid = document.getElementById('uid');
        if (uid) uid.value = '';
    });
}

function submitForm() {
    var uid = document.getElementById('uid');
    if (uid && uid.value !== '') {
        editRecord();
    } else {
        addRecord();
    }
}

function addRecord() {
    if (!formMessage) return;

    if (homepageMessageDateConflict) {
        formError('Date conflicts with another message.');
        return;
    }

    var formData = new URLSearchParams(new FormData(formMessage));
    formData.append('type', 'add');

    postUrlEncoded('homepageMessageServer.php', formData)
    .then(function (text) {
        if (text === 'success') {
            formSuccess('Item successfully added.');
        } else {
            formError(text);
        }
    })
    .catch(function (xhr) {
        showAlert('#formAlert', false, 'Oops! An error occurred processing the form. Please try again later.');
        console.log(xhr);
    });
}

function showEditRecord(uid) {
    var params = new URLSearchParams({ type: 'getHomepageMessageRecord', uid: uid.toString() });

    postUrlEncoded('homepageMessageServer.php', params)
    .then(function (data) {
        populateForm('#form_message', data);
        var uidField = document.getElementById('uid');
        if (uidField) uidField.value = uid;
        var modal = document.getElementById('modal_add_edit');
        if (modal) {
            var bsModal = bootstrap.Modal.getOrCreateInstance(modal);
            bsModal.show();
        }
    })
    .catch(function (xhr) {
        showAlert('#formAlert', false, 'Oops! An error occurred opening the form. Please try again later.');
        console.log(xhr);
    });
}

function editRecord() {
    if (!formMessage) return;
    if (homepageMessageDateConflict) {
        formError('Date conflicts with another message.');
        return;
    }
    var formData = new URLSearchParams(new FormData(formMessage));
    formData.append('type', 'edit');

    postUrlEncoded('homepageMessageServer.php', formData)
    .then(function (text) {
        if (text === 'success') {
            formSuccess('Item successfully modified.');
        } else {
            formError(text);
        }
    })
    .catch(function (xhr) {
        showAlert('#formAlert', false, 'Oops! An error occurred processing the form. Please try again later.');
        console.log(xhr);
    });
}

function checkDates(date) {
    var params = new URLSearchParams({ type: 'homepageMessageDateConflictCheck', date: date });

    postUrlEncoded('homepageMessageServer.php', params)
    .then(function (data) {
        if (data !== 0) {
            homepageMessageDateConflict = true;
            formError('Date conflicts with another message.');
        } else {
            homepageMessageDateConflict = false;
            var formAlert = document.querySelector('#formAlert');
            if (formAlert) {
                formAlert.classList.remove('alert', 'alert-danger', 'alert-success', 'alert-dismissible', 'fade', 'show');
                formAlert.classList.add('d-none');
                formAlert.innerHTML = '';
                formAlert.removeAttribute('role');
            }
        }
    })
    .catch(function (xhr) {
        showAlert('#formAlert', false, 'Oops! An error occurred opening the form. Please try again later.');
        console.log(xhr);
    });
}

function formSuccess(text) {
  showAlert('#pageAlert', true, text);
  var table = $("#kcbMessageTable").DataTable();
  if (table) {
    table.ajax.reload();
  }
  if (formMessage) {
    formMessage.reset();
    formMessage.classList.remove("was-validated");
  }
  var modal = document.getElementById("modal_add_edit");
  if (modal) {
    var bsModal = bootstrap.Modal.getInstance(modal);
    if (bsModal) bsModal.hide();
  }
    window.scrollTo(0, 0);
}

function formError(text) {
  if (!formMessage) return;
  shakeForm(formMessage);
  showAlert('#formAlert', false, text);
}

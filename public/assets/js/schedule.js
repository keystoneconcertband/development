document.addEventListener('DOMContentLoaded', function() {
    var table = $('#kcbScheduleTable').DataTable({
        responsive: true,
        order: [2, 'desc'],
        ajax: {
            url: 'scheduleServer.php',
            dataSrc: ''
        },
        columnDefs: [
            { "orderable": false, "targets": 0 } // Disables sorting on the 1st column
        ],
        columns: [
            { data: null, render: function (data) {
                return '<a href="#nojump"><span class="fa fa-edit" onclick="showEditRecord(' + data.UID + ')"></span></a>';
            }},
            { data: 'Title' },
            { data: 'concertBegin', render: function(d) { return d ? d.replace('T',' ') : ''; } },
            { data: 'pants', render: function(d) { return d == 1 ? 'Tan' : 'Black'; } },
            { data: 'chair', render: function(d) { return d == 1 ? 'Yes' : 'No'; } },
            { data: 'address' }
        ]
    });
});

const formSchedule = document.getElementById('form_schedule');
if (formSchedule) {
    formSchedule.addEventListener('submit', function (event) {
        formSchedule.classList.add('was-validated');
        if (event.defaultPrevented || !formSchedule.checkValidity()) {
            event.preventDefault();
            formError('Check for errors in the form.');
        } else {
            event.preventDefault();
            submitForm();
        }
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
    if (!formSchedule) return;

    // ensure boolean fields are sent as 1/0
    var formData = new URLSearchParams(new FormData(formSchedule));
    formData.append('type', 'add');

    postUrlEncoded('scheduleServer.php', formData)
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
    var params = new URLSearchParams({ type: 'getScheduleRecord', uid: uid.toString() });

    postUrlEncoded('scheduleServer.php', params)
    .then(function (data) {
        populateForm('#form_schedule', data);
        // checkboxes come back as 1/0
        var pants = document.getElementById('pants');
        var chair = document.getElementById('chair');
        if (pants) pants.checked = data.pants == 1;
        if (chair) chair.checked = data.chair == 1;
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
    if (!formSchedule) return;
    var formData = new URLSearchParams(new FormData(formSchedule));
    formData.append('type', 'edit');

    postUrlEncoded('scheduleServer.php', formData)
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

function formSuccess(text) {
  showAlert('#pageAlert', true, text);
  var table = $("#kcbScheduleTable").DataTable();
  if (table) {
    table.ajax.reload();
  }
  if (formSchedule) {
    formSchedule.reset();
    formSchedule.classList.remove("was-validated");
  }
  var modal = document.getElementById("modal_add_edit");
  if (modal) {
    var bsModal = bootstrap.Modal.getInstance(modal);
    if (bsModal) bsModal.hide();
  }
    window.scrollTo(0, 0);
}

function formError(text) {
  if (!formSchedule) return;
  shakeForm(formSchedule);
  showAlert('#formAlert', false, text);
}

// Modal handlers: reset form for new entries
var modal = document.getElementById('modal_add_edit');
if (modal) {
    modal.addEventListener('show.bs.modal', function () {
        var uid = document.getElementById('uid');
        var modalTitle = document.getElementById('modalAddEditLabel');

        if (uid && uid.value) {
            if (modalTitle) modalTitle.textContent = 'Edit Schedule';
        } else {
            if (formSchedule) {
                formSchedule.reset();
                formSchedule.classList.remove('was-validated');
            }
            if (modalTitle) modalTitle.textContent = 'Add Schedule';
            var formAlert = document.querySelector('#formAlert');
            if (formAlert) {
                formAlert.classList.add('d-none');
                formAlert.innerHTML = '';
                formAlert.removeAttribute('role');
            }
        }
    });

    modal.addEventListener('hidden.bs.modal', function () {
        if (formSchedule) {
            formSchedule.reset();
            formSchedule.classList.remove('was-validated');
        }
        var uid = document.getElementById('uid');
        if (uid) uid.value = '';
    });
}

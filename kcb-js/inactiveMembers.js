document.addEventListener('DOMContentLoaded', function() {
    var table = $('#kcbMemberTable').DataTable({
        order: [1, 'asc'],
        ajax: {
            url: 'inactiveMembersServer.php',
            dataSrc: ''
        },
        columns: [
            { data: null, render: function (data) {
                return '<a href="#nojump"><span class="fa fa-edit" onclick="showEditRecord(' + data.uid + ')"></span></a>';
            }},
            { data: 'fullName' },
            { data: null, render: function (data) {
                if (data.email) {
                    var emailArr = data.email.split(',');
                    var emailOut = '';
                    for (var i = 0; i < emailArr.length; i++) {
                        emailOut += '<a href="mailto:' + emailArr[i] + '">' + emailArr[i] + '</a><br />';
                    }
                    return emailOut;
                }
                return '';
            }},
            { data: null, render: function (data) {
                if (data.instrument) {
                    return data.instrument.replace(/,/g, '<br/>');
                }
                return '';
            }},
            { data: null, render: function (data) {
                if (data.text) {
                    return data.text.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
                }
                return '';
            }},
            { data: null, render: function (data) {
                if (data.address1) {
                    var addr = data.address1 + '<br />';
                    if (data.address2) {
                        addr += data.address2 + '<br />';
                    }
                    addr += data.city + ', ' + data.state + ' ' + data.zip;
                    return addr;
                }
                return '';
            }},
            { data: 'disabled_dt_tm' }
        ]
    });

    var addRowButton = document.getElementById('addRow');
    if (addRowButton) {
        addRowButton.addEventListener('click', function () {
            var emailContainers = document.querySelectorAll('.emailContainers');
            if (!emailContainers.length) return;
            var lastContainer = emailContainers[emailContainers.length - 1];
            var lastId = lastContainer.id.replace('emailContainer', '');
            var lastIdInt = parseInt(lastId, 10);
            var emailCount = lastIdInt + 1;
            var html = '<div class="form-group row emailContainers" id="emailContainer' + emailCount + '" style="display:none"><div class="col-sm-12"><label for="Email" class="control-label">Email ' + emailCount + '</label><div class="input-group"><input type="email" class="form-control" name="email[]" id="email[]" placeholder="Email Address ' + emailCount + '" maxlength="100" value=""><span class="input-group-text"><a href="#noscroll" id="email' + emailCount + '" onclick="deleteEmail(\'emailContainer' + emailCount + '\');"><span class="fa fa-remove"></span></a></span></div></div></div>';
            lastContainer.insertAdjacentHTML('afterend', html);
            var newContainer = document.getElementById('emailContainer' + emailCount);
            if (newContainer) {
                newContainer.style.display = 'block';
            }
        });
    }
});

var formMember = document.getElementById('form_member');
if (formMember) {
    formMember.addEventListener('submit', function (event) {
        if (event.defaultPrevented || !formMember.checkValidity()) {
            event.preventDefault();
            formError('Check for errors in the form.');
        } else {
            event.preventDefault();
            submitForm();
        }
    });
}

var modalEditDelete = document.getElementById('modal_edit_delete');
if (modalEditDelete) {
    modalEditDelete.addEventListener('show.bs.modal', function () {
        var msgMainHeader = document.getElementById('msgMainHeader');
        var msgSubmit = document.getElementById('msgSubmit');
        if (msgMainHeader) {
            msgMainHeader.className = '';
            msgMainHeader.textContent = '';
        }
        if (msgSubmit) {
            msgSubmit.className = '';
            msgSubmit.textContent = '';
        }
    });

    modalEditDelete.addEventListener('hidden.bs.modal', function () {
        if (formMember) {
            formMember.reset();
        }
        var uid = document.getElementById('uid');
        if (uid) uid.value = '';
        document.querySelectorAll('.emailContainers').forEach(function (el) {
            el.remove();
        });
        var zipContainer = document.getElementById('zipContainer');
        if (zipContainer) {
            zipContainer.insertAdjacentHTML('afterend', '<div class="form-group row emailContainers" id="emailContainer1"><div class="col-sm-12"><label for="Email" class="control-label">Email</label><div class="input-group"><input type="email" class="form-control email1" name="email[]" id="email[]" placeholder="Email Address" maxlength="100"><span class="input-group-text"><a href="#noscroll" id="email1" onclick="deleteEmail(\'emailContainer1\');"><span class="fa fa-remove"></span></a></span></div></div></div>');
        }
    });
}

function deleteEmail(emailContainer) {
    var numItems = document.querySelectorAll('.emailContainers').length;
    if (numItems < 2) {
        formError('You must keep at least one email address.');
    } else {
        var el = document.getElementById(emailContainer);
        if (el) el.remove();
    }
}

function showEditRecord(uid) {
    var params = new URLSearchParams({ type: 'getMemberRecord', uid: uid.toString() });
    fetch('inactiveMembersServer.php', {
        method: 'POST',
        body: params,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(function (response) {
        return response.json();
    })
    .then(function (data) {
        populateForm('#form_member', data);
        populateEmail(data);
        populateInstrument(data);
        if (data.displayFullName === 1) {
            var displayFullName = document.getElementById('displayFullName');
            if (displayFullName) displayFullName.checked = true;
        }
        var uidField = document.getElementById('uid');
        if (uidField) uidField.value = uid;
        var modal = document.getElementById('modal_edit_delete');
        if (modal) {
            var bsModal = bootstrap.Modal.getOrCreateInstance(modal);
            bsModal.show();
        }
    })
    .catch(function (xhr) {
        submitMSG(false, 'Oops! An error occurred opening the form. Please try again later.');
        console.log(xhr);
    });
}

function submitForm() {
    if (!formMember) return;
    var formData = new URLSearchParams(new FormData(formMember));
    formData.append('type', 'edit');

    fetch('inactiveMembersServer.php', {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(function (response) {
        return response.text();
    })
    .then(function (text) {
        if (text === 'success') {
            formSuccess('User successfully re-activated.');
        } else {
            formError(text);
        }
    })
    .catch(function (xhr) {
        submitMSG(false, 'Oops! An error occurred processing the form. Please try again later.');
        console.log(xhr);
    });
}

function formSuccess(text) {
    submitMSG(true, text);
    var table = $('#kcbMemberTable').DataTable();
    if (table) {
        table.ajax.reload();
    }
    if (formMember) {
        formMember.reset();
    }
    var modal = document.getElementById('modal_edit_delete');
    if (modal) {
        var bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) bsModal.hide();
    }
}

function formError(text) {
    if (!formMember) return;
    formMember.classList.add('shake', 'animated');
    function removeClasses() {
        formMember.classList.remove('shake', 'animated');
        formMember.removeEventListener('animationend', removeClasses);
        formMember.removeEventListener('webkitAnimationEnd', removeClasses);
        formMember.removeEventListener('mozAnimationEnd', removeClasses);
        formMember.removeEventListener('MSAnimationEnd', removeClasses);
        formMember.removeEventListener('oanimationend', removeClasses);
    }
    formMember.addEventListener('animationend', removeClasses);
    formMember.addEventListener('webkitAnimationEnd', removeClasses);
    formMember.addEventListener('mozAnimationEnd', removeClasses);
    formMember.addEventListener('MSAnimationEnd', removeClasses);
    formMember.addEventListener('oanimationend', removeClasses);
    submitMSG(false, text);
}

function submitMSG(valid, msg) {
    var msgMainHeader = document.getElementById('msgMainHeader');
    var msgSubmit = document.getElementById('msgSubmit');
    var msgClasses = valid ? 'h4 tada animated text-success' : 'h4 text-danger';
    if (msgMainHeader) {
        msgMainHeader.className = msgClasses;
        msgMainHeader.textContent = msg;
    }
    if (msgSubmit) {
        msgSubmit.className = msgClasses;
        msgSubmit.textContent = msg;
    }
}

function populateForm(frm, data) {
    var form = document.querySelector(frm);
    if (!form) return;
    Object.keys(data).forEach(function (key) {
        var field = form.querySelector('[name="' + key + '"]');
        if (field) field.value = data[key];
    });
}

function populateEmail(data) {
    var email = data.email;
    if (email !== null && email !== '') {
        if (email.indexOf(',') !== -1) {
            var arr = email.split(',');
            for (var i = 0; i < arr.length; i++) {
                var emailCount = i + 1;
                if (i === 0) {
                    var email1 = document.querySelector('.email1');
                    if (email1) email1.value = arr[i];
                } else {
                    var container = document.getElementById('emailContainer' + i);
                    if (container) {
                        container.insertAdjacentHTML('afterend', '<div class="form-group row emailContainers" id="emailContainer' + emailCount + '"><div class="col-sm-12"><label for="Email" class="control-label">Email ' + emailCount + '</label><div class="input-group"><input type="email" class="form-control" name="email[]" id="email[]" placeholder="Email Address ' + emailCount + '" maxlength="100" value="' + arr[i] + '"><span class="input-group-text"><a href="#noscroll" id="email' + emailCount + '" onclick="deleteEmail(\'emailContainer' + emailCount + '\');"><span class="fa fa-remove"></span></a></span></div></div></div>');
                    }
                }
            }
        } else {
            var email1 = document.querySelector('.email1');
            if (email1) email1.value = email;
        }
    }
}

function populateInstrument(data) {
    if (data.instrument) {
        var arr = data.instrument.split(',');
        for (var i = 0; i < arr.length; i++) {
            var checkbox = document.getElementById(arr[i]);
            if (checkbox) checkbox.checked = true;
        }
    }
}

function printMembers() {
    window.open('inactiveMembersPrint.php', 'Print Members', 'menubar=0,location=0,height=700,width=700');
}

document.addEventListener('DOMContentLoaded', function() {
    var table = $('#kcbMemberTable').DataTable({
        order: [5, 'desc'],
        ajax: {
            url: 'pendingMembersServer.php',
            dataSrc: ''
        },
        columns: [
            { data: null, render: function (data) {
                return '<a href="#nojump"><span class="fa fa-trash-o" onclick="deleteRecord(\'' + data.fullName + '\', ' + data.uid + ')"></span></a>&nbsp;&nbsp;&nbsp;<a href="#nojump"><span class="fa fa-edit" onclick="showEditRecord(' + data.uid + ')"></span></a>';
            }},
            { data: 'fullName' },
            { data: null, render: function (data) {
                if (data.text) {
                    return data.text.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
                }
                return '';
            }},
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
            { data: 'estbd_dt_tm' }
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
            var html = createEmailRow(emailCount, {
                labelText: 'Email',
                placeholderText: 'Email Address ' + emailCount,
                rowClass: 'emailContainers'
            });
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
        formMember.classList.add('was-validated');
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
            formMember.classList.remove('was-validated');
        }
        var uid = document.getElementById('uid');
        if (uid) uid.value = '';
        document.querySelectorAll('.emailContainers').forEach(function (el) {
            el.remove();
        });
        var textContainer = document.getElementById('textContainer');
        if (textContainer) {
            textContainer.insertAdjacentHTML('afterend', createEmailRow(1, {
                labelText: 'Email',
                placeholderText: 'Email Address',
                rowClass: 'emailContainers'
            }));
        }
    });
}

function deleteEmail(emailContainer) {
    var numItems = document.querySelectorAll('.emailContainers').length;
    if (numItems < 2) {
        formError('You must keep at least one email address.');
    } else {
        var emailElement = document.getElementById(emailContainer);
        if (emailElement) {
            emailElement.remove();
        }
    }
}

function showEditRecord(uid) {
    var params = new URLSearchParams({ type: 'getMemberRecord', uid: uid.toString() });

    postUrlEncoded('pendingMembersServer.php', params)
    .then(function (data) {
        populateForm('#form_member', data);
        populateEmail(data, {
            firstSelector: '.email1',
            containerPrefix: 'emailContainer',
            rowClass: 'emailContainers',
            labelText: 'Email'
        });
        populateInstrument(data);
        var state = document.getElementById('state');
        if (state) state.value = 'PA';
        var uidField = document.getElementById('uid');
        if (uidField) uidField.value = uid;
        var modal = document.getElementById('modal_edit_delete');
        if (modal) {
            var bsModal = bootstrap.Modal.getOrCreateInstance(modal);
            bsModal.show();
        }
    })
    .catch(function (xhr) {
        formError('Oops! An error occurred opening the form. Please try again later.');
        console.log(xhr);
    });
}

function submitForm() {
  if (!formMember) return;
  var formData = new URLSearchParams(new FormData(formMember));
  formData.append('type', 'edit');

  postUrlEncoded('pendingMembersServer.php', formData)
    .then(function (text) {
      if (text === 'success') {
        formSuccess('User successfully added to the band.');
      } else {
        formError(text);
      }
    })
    .catch(function (error) {
      console.log('Edit Fetch error:', error);
      formError('Oops! An error occurred processing the form. Please try again later.');
    });
}

function deleteRecord(title, uid) {
    if (confirm('Do you want to remove ' + title + ' from the band roster and email list?')) {
        var params = new URLSearchParams({ type: 'delete', uid: uid.toString() });

        postUrlEncoded('pendingMembersServer.php', params)
        .then(function (text) {
            if (text === 'success') {
                formSuccess('User successfully removed.');
            } else {
                formError(text);
            }
        })
        .catch(function (xhr) {
            formError('Oops! An error occurred processing the form. Please try again later.');
            console.log(xhr);
        });
    }
}

function formSuccess(text) {
  showAlert('#pageAlert', true, text);
  var table = $('#kcbMemberTable').DataTable();
  if (table) {
    table.ajax.reload();
  }
  if (formMember) {
    formMember.reset();
    formMember.classList.remove('was-validated');
  }
  var modal = document.getElementById('modal_edit_delete');
  if (modal) {
    var bsModal = bootstrap.Modal.getInstance(modal);
    if (bsModal) bsModal.hide();
  }
}

function formError(text) {
    if (!formMember) return;
    shakeForm(formMember);
    showAlert('#formAlert', false, text);
}

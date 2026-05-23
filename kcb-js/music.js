document.addEventListener('DOMContentLoaded', function() {
    var table = $('#kcbMusicTable').DataTable({
        responsive: true,
        stateSave: true,
        order: [1, 'asc'],
        ajax: {
            url: 'musicServer.php',
            dataSrc: ''
        },
        columns: [
            { data: null, render: function (data) {
                if (accountType === '1' || accountType === '2') {
                    var title = data.title.replace(/'/g, '&#96;');
                    return '<a href="#nojump"><span class="fa fa-trash-o" onclick="deleteRecord(\'' + title + '\', ' + data.uid + ')"></span></a>&nbsp;&nbsp;&nbsp;<a href="#nojump"><span class="fa fa-edit" onclick="showEditRecord(' + data.uid + ')"></span></a>';
                }
                return '';
            }},
            { data: 'title' },
            { data: 'notes' },
            { data: null, render: function (data) {
                if (data.music_link && data.music_link !== '') {
                    return '<a href="' + data.music_link + '" target="_blank">' + data.music_link + '</a><br />';
                }
                return '<a href="http://www.youtube.com/results?search_query=' + data.title + '" target="_blank">http://www.youtube.com/results?search_query=' + data.title + '</a><br />';
            }},
            { data: 'genre' },
        ]
    });

    var column = table.column(0);
    column.visible(accountType === '1' || accountType === '2');
});

var formMusic = document.getElementById('form_music');
if (formMusic) {
    formMusic.addEventListener('submit', function (event) {
        formMusic.classList.add('was-validated');
        if (event.defaultPrevented || !formMusic.checkValidity()) {
            event.preventDefault();
            formError();
            submitMSG(false, 'Check for errors in the form.');
        } else {
            event.preventDefault();
            submitForm();
        }
    });
}

var modalAddEdit = document.getElementById('modal_add_edit');
if (modalAddEdit) {
    modalAddEdit.addEventListener('show.bs.modal', function () {
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
        populateGenreDropdown();
    });
}

function submitForm() {
    var uidField = document.getElementById('uid');
    if (uidField && uidField.value !== '') {
        editRecord();
    } else {
        addRecord();
    }
}

function addRecord() {
    if (!formMusic) return;
    var formData = new URLSearchParams(new FormData(formMusic));
    formData.append('type', 'add');

    fetch("musicServer.php", {
    method: "POST",
    body: formData,
    headers: {
      Accept: "application/json",
    },
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (text) {
      console.log(text);
      if (text === "success") {
        formSuccess("Record Added.");
      } else {
        formError(text);
      }
    })
    .catch(function (xhr) {
      submitMSG(
        false,
        "Oops! An error occurred processing the form. Please try again later.",
      );
      console.log(xhr);
    });
}

function showEditRecord(uid) {
    var params = new URLSearchParams({ type: 'getMusicRecord', uid: uid.toString() });

    fetch('musicServer.php', {
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
        populateForm('#form_music', data);
        var uidField = document.getElementById('uid');
        if (uidField) uidField.value = uid;
        var nbrPlaysDiv = document.getElementById('nbr_plays_div');
        if (nbrPlaysDiv) nbrPlaysDiv.style.display = 'block';
        var modal = document.getElementById('modal_add_edit');
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

function editRecord() {
    if (!formMusic) return;
    var formData = new URLSearchParams(new FormData(formMusic));
    formData.append('type', 'edit');

  fetch("musicServer.php", {
    method: "POST",
    body: formData,
    headers: {
      Accept: "application/json",
    },
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("HTTP error! status: " + response.status);
      }
      return response.json();
    })
    .then(function (text) {
      if (text === "success") {
        formSuccess("Record Saved.");
      } else {
        formError(text);
      }
    })
    .catch(function (error) {
      console.log("Edit Fetch error:", error);
      submitMSG(
        false,
        "Oops! An error occurred processing the form. Please try again later.",
      );
    });
}

function deleteRecord(title, uid) {
    if (!confirm('Do you want to delete title ' + title + '?')) {
        return;
    }
    var params = new URLSearchParams({ type: 'delete', uid: uid.toString() });

  fetch("musicServer.php", {
    method: "POST",
    body: formData,
    headers: {
      Accept: "application/json",
    },
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("HTTP error! status: " + response.status);
      }
      return response.json();
    })
    .then(function (text) {
      if (text === "success") {
        formSuccess("Record removed.");
      } else {
        formError(text);
      }
    })
    .catch(function (error) {
      console.log("Edit Fetch error:", error);
      submitMSG(
        false,
        "Oops! An error occurred processing the form. Please try again later.",
      );
    });
}

function formSuccess(text) {
  var pageAlert = document.getElementById("pageAlert");
  if (pageAlert) {
    pageAlert.className = "alert alert-success alert-dismissible fade show";
    pageAlert.innerHTML =
      text +
      '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    pageAlert.setAttribute("role", "alert");
  }
  var table = $("#kcbMusicTable").DataTable();
  if (table) {
    table.ajax.reload();
  }
  if (formMusic) {
    formMusic.reset();
    formMusic.classList.remove("was-validated");
  }
  var modal = document.getElementById("modal_add_edit");
  if (modal) {
    var bsModal = bootstrap.Modal.getInstance(modal);
    if (bsModal) bsModal.hide();
  }
}

function formError(text) {
    if (!formMusic) return;
    formMusic.classList.add('shake', 'animated');
    function removeClasses() {
        formMusic.classList.remove('shake', 'animated');
        formMusic.removeEventListener('animationend', removeClasses);
        formMusic.removeEventListener('webkitAnimationEnd', removeClasses);
        formMusic.removeEventListener('mozAnimationEnd', removeClasses);
        formMusic.removeEventListener('MSAnimationEnd', removeClasses);
        formMusic.removeEventListener('oanimationend', removeClasses);
    }
    formMusic.addEventListener('animationend', removeClasses);
    formMusic.addEventListener('webkitAnimationEnd', removeClasses);
    formMusic.addEventListener('mozAnimationEnd', removeClasses);
    formMusic.addEventListener('MSAnimationEnd', removeClasses);
    formMusic.addEventListener('oanimationend', removeClasses);
    submitMSG(false, text);
}

function submitMSG(valid, msg) {
  var formAlert = document.getElementById("formAlert");
  if (!formAlert) return;

  var alertClasses = valid
    ? "alert alert-success alert-dismissible fade show"
    : "alert alert-danger alert-dismissible fade show";
  formAlert.className = alertClasses;
  formAlert.innerHTML =
    msg +
    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
  formAlert.setAttribute("role", "alert");
}

function populateForm(frm, data) {
    var form = document.querySelector(frm);
    if (!form) return;
    Object.keys(data).forEach(function (key) {
        var field = form.querySelector('[name="' + key + '"]');
        if (field) field.value = data[key];
    });
}

function populateGenreDropdown() {
    var genreSelect = document.getElementById('genre');
    if (!genreSelect) return;
    if (genreSelect.options.length === 1) {
        var params = new URLSearchParams({ type: 'getMusicGenres' });
        fetch('musicServer.php', {
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
            data.forEach(function (value) {
                var option = document.createElement('option');
                option.textContent = value.genre;
                genreSelect.appendChild(option);
            });
        })
        .catch(function (xhr) {
            submitMSG(false, 'Oops! An error occurred opening the form. Please try again later.');
            console.log(xhr);
        });
    }
}
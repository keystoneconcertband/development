var dataTable;

document.addEventListener('DOMContentLoaded', function() {
    dataTable = new DataTable('#kcbMusicTable', {
        responsive: true,
        stateSave: true,
        order: [1, 'asc'],
        ajax: {
            url: 'api/music.php',
            dataSrc: ''
        },
        columnDefs: [
            { "orderable": false, "targets": 0 } // Disables sorting on the 1st column
        ],
        columns: [
            { data: null,
              width: '60px',
              render: function (data) {
                if (accountType === '1' || accountType === '2') {
                    var title = data.title.replace(/'/g, '&#96;');
                    return '<a href="#nojump"><span class="fa fa-trash" onclick="deleteRecord(\'' + title + '\', ' + data.uid + ')"></span></a>&nbsp;&nbsp;&nbsp;<a href="#nojump"><span class="fa fa-edit" onclick="showEditRecord(' + data.uid + ')"></span></a>';
                }
                return '';
              width: '10px';
            }},
            { data: 'title' },
            {
                data: 'notes',
                render: function (data, type) {
                    if (type === 'display') {
                        return typeof linkify === 'function' ? linkify(data) : (data || '');
                    }
                    return data || '';
                }
            },
            { data: null, render: function (data) {
                if (data.music_link && data.music_link !== '') {
                    return '<a href="' + data.music_link + '" target="_blank" rel="noopener noreferrer">' + data.music_link + '</a><br />';
                }
                return '<a href="http://www.youtube.com/results?search_query=' + encodeURIComponent(data.title) + '" target="_blank" rel="noopener noreferrer">http://www.youtube.com/results?search_query=' + data.title + '</a><br />';
            }},
            { data: 'genre' },
            {
                data: 'estbd_dt_tm',
                render: function (data, type) {
                    if (!data || data === '0000-00-00 00:00:00' || data === '0000-00-00') return '';
                    if (type === 'sort' || type === 'type') {
                        return data;
                    }
                    const dateObj = new Date(String(data).replaceAll('-', '/'));
                    if (isNaN(dateObj.getTime())) return escapeHtml(data);
                    const formatted = dateObj.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                    if (type === 'filter') {
                        return data + ' ' + formatted;
                    }
                    return formatted;
                }
            }
        ]
    });

    var column = dataTable.column(0);
    column.visible(accountType === '1' || accountType === '2');

    populateGenreDropdown();
});

var formMusic = document.getElementById('form_music');
if (formMusic) {
    formMusic.addEventListener('submit', function (event) {
        formMusic.classList.add('was-validated');
        if (event.defaultPrevented || !formMusic.checkValidity()) {
            event.preventDefault();
            formError('Check for errors in the form.');
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
        clearAlert('#formAlert');

        var uidField = document.getElementById('uid');
        var modalTitle = document.getElementById('modalAddEditLabel');
        if (uidField && uidField.value) {
            if (modalTitle) modalTitle.textContent = 'Edit Music';
        } else {
            if (modalTitle) modalTitle.textContent = 'Add Music';
            if (formMusic) {
                formMusic.reset();
                formMusic.classList.remove('was-validated');
            }
        }
        populateGenreDropdown();
    });

    modalAddEdit.addEventListener('hidden.bs.modal', function () {
        if (formMusic) {
            formMusic.reset();
            formMusic.classList.remove('was-validated');
        }
        var uidField = document.getElementById('uid');
        if (uidField) uidField.value = '';
        var modalTitle = document.getElementById('modalAddEditLabel');
        if (modalTitle) modalTitle.textContent = 'Add Music';
        clearAlert('#formAlert');
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

    postUrlEncoded('api/music.php', formData)
    .then(function (text) {
      if (text === 'success') {
        formSuccess('Record Added.');
      } else {
        formError(text);
      }
    })
    .catch(function (error) {
      showAlert('#formAlert', false, 'Oops! An error occurred processing the form. Please try again later.');
      console.log(error);
    });
}

function showEditRecord(uid) {
    var params = new URLSearchParams({ type: 'getMusicRecord', uid: uid.toString() });

    Promise.all([
        postUrlEncoded('api/music.php', params),
        populateGenreDropdown()
    ])
    .then(function (results) {
        var data = results[0];
        if (data && data.notes) {
            data.notes = String(data.notes)
                .replaceAll('\r\n', '\n')
                .replaceAll('\r', '\n')
                .replace(/\n?<br\s*\/?>\n?/gi, '\n');
        }
        var uidField = document.getElementById('uid');
        if (uidField) uidField.value = uid;

        populateForm('#form_music', data);

        // Ensure genre is properly selected in the dropdown
        var genreSelect = document.getElementById('genre');
        if (genreSelect) {
            var genreVal = (data && data.genre) ? String(data.genre).trim() : '';
            if (genreVal && genreVal !== '0') {
                genreSelect.value = genreVal;
                if (genreSelect.value !== genreVal) {
                    for (var i = 0; i < genreSelect.options.length; i++) {
                        if (genreSelect.options[i].text.trim().toLowerCase() === genreVal.toLowerCase() ||
                            genreSelect.options[i].value.trim().toLowerCase() === genreVal.toLowerCase()) {
                            genreSelect.selectedIndex = i;
                            break;
                        }
                    }
                }
            } else {
                genreSelect.value = '';
            }
        }

        clearAlert('#formAlert');

        var modalTitle = document.getElementById('modalAddEditLabel');
        if (modalTitle) modalTitle.textContent = 'Edit Music';

        var modal = document.getElementById('modal_add_edit');
        if (modal) {
            var bsModal = bootstrap.Modal.getOrCreateInstance(modal);
            bsModal.show();
        }
    })
    .catch(function (error) {
        showAlert('#pageAlert', false, 'Oops! An error occurred opening the form. Please try again later.');
        console.log(error);
    });
}

function editRecord() {
    if (!formMusic) return;
    var formData = new URLSearchParams(new FormData(formMusic));
    formData.append('type', 'edit');

    postUrlEncoded('api/music.php', formData)
    .then(function (text) {
      if (text === 'success') {
        formSuccess('Record Saved.');
      } else {
        formError(text);
      }
    })
    .catch(function (error) {
      console.log('Edit Fetch error:', error);
      showAlert('#formAlert', false, 'Oops! An error occurred processing the form. Please try again later.');
    });
}

function deleteRecord(title, uid) {
    if (!confirm('Do you want to delete title ' + title + '?')) {
        return;
    }
    var params = new URLSearchParams({ type: 'delete', uid: uid.toString() });

    postUrlEncoded('api/music.php', params)
    .then(function (text) {
      if (text === 'success') {
        formSuccess('Record removed.');
      } else {
        formError(text);
      }
    })
    .catch(function (error) {
      console.log('Delete Fetch error:', error);
      showAlert('#formAlert', false, 'Oops! An error occurred processing the form. Please try again later.');
    });
}

function formSuccess(text, resetPaging = false) {
  showAlert('#pageAlert', true, text);
  var table = dataTable;
  if (table) {
    table.ajax.reload(null, resetPaging);
  }
  if (formMusic) {
    formMusic.reset();
    formMusic.classList.remove("was-validated");
  }
  clearAlert('#formAlert');
  var modal = document.getElementById("modal_add_edit");
  if (modal) {
    if (document.activeElement && modal.contains(document.activeElement)) {
      document.activeElement.blur();
    }
    var bsModal = bootstrap.Modal.getInstance(modal);
    if (bsModal) bsModal.hide();
  }
}

function formError(text) {
    if (!formMusic) return;
    shakeForm(formMusic);
    showAlert('#formAlert', false, text);
}

var genresPromise = null;

function populateGenreDropdown() {
    var genreSelect = document.getElementById('genre');
    if (!genreSelect) return Promise.resolve();
    if (genreSelect.options.length > 1) {
        return Promise.resolve();
    }
    if (!genresPromise) {
        var params = new URLSearchParams({ type: 'getMusicGenres' });
        genresPromise = postUrlEncoded('api/music.php', params)
        .then(function (data) {
            if (Array.isArray(data)) {
                data.forEach(function (value) {
                    var option = document.createElement('option');
                    option.value = value.genre;
                    option.textContent = value.genre;
                    genreSelect.appendChild(option);
                });
            }
        })
        .catch(function (error) {
            genresPromise = null;
            showAlert('#formAlert', false, 'Oops! An error occurred loading genres. Please try again later.');
            console.log(error);
        });
    }
    return genresPromise;
}

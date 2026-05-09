document.addEventListener('DOMContentLoaded', function() {
    $("#concert_title").autocomplete({
        source: "musicTitleSearch.php",
        minLength: 2,
        select: function(event, ui) {
            document.querySelectorAll('#concert_program_list #concert_program_empty').forEach(function(el) {
                el.remove();
            });

            var concertUids = document.getElementById('concert_uids');
            if (concertUids && concertUids.value.indexOf(ui.item.value) < 0) {
                document.getElementById('concert_program_list').insertAdjacentHTML('beforeend', '<li>' + ui.item.label + '</li>');
                if (concertUids.value === '') {
                    concertUids.value = ui.item.value;
                } else {
                    concertUids.value = concertUids.value + ',' + ui.item.value;
                }
            }
            document.getElementById('concert_title').value = '';
            return false;
        }
    });

    $('#dpLastPlayed').datetimepicker({
        format: 'L',
        minDate: '1/1/2000',
        maxDate: moment().add(1, 'days'),
        showTodayButton: true,
        showClear: true,
        showClose: true
    });

    $('#dpConcert').datetimepicker({
        format: 'L',
        minDate: '1/1/2000',
        maxDate: moment().add(7, 'days'),
        showTodayButton: true,
        showClear: true,
        showClose: true
    });

    $('#kcbMusicTable').validator();
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
            { data: 'last_played' },
            { data: null, render: function (data) {
                if (data.number_plays) {
                    return data.number_plays;
                }
                return '0';
            }}
        ]
    });

    var column = table.column(0);
    column.visible(accountType === '1' || accountType === '2');
});

var formConcert = document.getElementById('form_concert');
if (formConcert) {
    formConcert.addEventListener('submit', function (event) {
        if (event.defaultPrevented || !formConcert.checkValidity()) {
            event.preventDefault();
            submitMSG(false, 'Check for errors in the form.');
        } else {
            event.preventDefault();
            submitConcert();
        }
    });
}

var formMusic = document.getElementById('form_music');
if (formMusic) {
    formMusic.addEventListener('submit', function (event) {
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

var modalConcert = document.getElementById('modal_concert');
if (modalConcert) {
    modalConcert.addEventListener('show.bs.modal', function () {
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
    modalConcert.addEventListener('hidden.bs.modal', function () {
        if (formConcert) {
            formConcert.reset();
        }
        var list = document.getElementById('concert_program_list');
        if (list) {
            list.innerHTML = '<li id="concert_program_empty">Empty</li>';
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
    modalAddEdit.addEventListener('hidden.bs.modal', function () {
        var nbrPlaysDiv = document.getElementById('nbr_plays_div');
        if (nbrPlaysDiv) {
            nbrPlaysDiv.style.display = 'none';
        }
        if (formMusic) {
            formMusic.reset();
        }
        var uid = document.getElementById('uid');
        if (uid) uid.value = '';
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

function submitConcert() {
    if (!formConcert) return;
    var formData = new URLSearchParams(new FormData(formConcert));
    formData.append('type', 'addConcert');

    fetch('musicServer.php', {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(function (response) {
        return response.json();
    })
    .then(function (text) {
        if (text === true) {
            var concertUids = document.getElementById('concert_uids');
            if (concertUids) concertUids.value = '';
            formSuccess('Concert dates were successfully updated.');
        } else {
            formError(text);
        }
    })
    .catch(function (xhr) {
        submitMSG(false, 'Oops! An error occurred processing the form. Please try again later.');
        console.log(xhr);
    });
}

function addRecord() {
    if (!formMusic) return;
    var formData = new URLSearchParams(new FormData(formMusic));
    formData.append('type', 'add');

    fetch('musicServer.php', {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(function (response) {
        return response.json();
    })
    .then(function (text) {
        if (text === 'success') {
            formSuccess('Item successfully added.');
        } else {
            formError(text);
        }
    })
    .catch(function (xhr) {
        submitMSG(false, 'Oops! An error occurred processing the form. Please try again later.');
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

    fetch('musicServer.php', {
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
            formSuccess('Item successfully modified.');
        } else {
            formError(text);
        }
    })
    .catch(function (xhr) {
        submitMSG(false, 'Oops! An error occurred processing the form. Please try again later.');
        console.log(xhr);
    });
}

function deleteRecord(title, uid) {
    if (!confirm('Do you want to delete title ' + title + '?')) {
        return;
    }
    var params = new URLSearchParams({ type: 'delete', uid: uid.toString() });

    fetch('musicServer.php', {
        method: 'POST',
        body: params,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(function (response) {
        return response.text();
    })
    .then(function (text) {
        if (text === 'success') {
            formSuccess('Item successfully deleted.');
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
    var table = $('#kcbMusicTable').DataTable();
    if (table) {
        table.ajax.reload();
    }
    var modalAddEdit = document.getElementById('modal_add_edit');
    if (modalAddEdit) {
        var bsModal = bootstrap.Modal.getInstance(modalAddEdit);
        if (bsModal) bsModal.hide();
    }
    var modalConcert = document.getElementById('modal_concert');
    if (modalConcert) {
        var bsConcert = bootstrap.Modal.getInstance(modalConcert);
        if (bsConcert) bsConcert.hide();
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

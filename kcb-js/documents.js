document.addEventListener('DOMContentLoaded', function() {
    var table = $('#kcbDocumentTable').DataTable({
        responsive: true,
        order: [sort, 'desc'],
        ajax: {
            url: 'documentsServer.php',
            dataSrc: 'files'
        },
        columns: [
            { data: null, render: function (data) {
                if (accountType === '1' || accountType === '2') {
                    return '<a href="#nojump"><span class="fa fa-trash-o" onclick="deleteFile(\'' + data.name + '\', \\'' + data.deleteUrl + '\')"></span></a>';
                }
                return '';
            }},
            { data: null, render: function (data) {
                if (data.name) {
                    return '<a href="https://docs.google.com/viewer?url=' + data.url + '" target="_blank">' + data.name + '</a><br />';
                }
                return '';
            }},
            { data: 'file_date' },
            { data: null, render: function (data) {
                if (data.name) {
                    return formatSizeUnits(data.size);
                }
                return '';
            }}
        ]
    });

    var column = table.column(0);
    column.visible(accountType === '1' || accountType === '2');

    $('#fileupload').fileupload();
});

var modalUpload = document.getElementById('modal_upload');
if (modalUpload) {
    modalUpload.addEventListener('hidden.bs.modal', function () {
        var table = $('#kcbDocumentTable').DataTable();
        if (table) {
            table.ajax.reload();
        }
    });
}

function deleteFile(name, url) {
    if (confirm('Do you want to remove the file ' + name + '?')) {
        fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then((response) => response.json())
        .then(function(text) {
            var table = $('#kcbDocumentTable').DataTable();
            if (table) {
                table.ajax.reload();
            }
        })
        .catch(function(xhr) {
            alert('Oops! An error occurred deleting the file. Please try again later.');
            console.log(xhr);
        });
    }
}

function formatSizeUnits(bytes) {
    if (bytes >= 1073741824) {
        bytes = (bytes / 1073741824).toFixed(2) + ' GB';
    } else if (bytes >= 1048576) {
        bytes = (bytes / 1048576).toFixed(2) + ' MB';
    } else if (bytes >= 1024) {
        bytes = (bytes / 1024).toFixed(2) + ' KB';
    } else if (bytes > 1) {
        bytes = bytes + ' bytes';
    } else if (bytes === 1) {
        bytes = bytes + ' byte';
    } else {
        bytes = '0 bytes';
    }
    return bytes;
}

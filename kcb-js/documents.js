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
        kcbFetchJson(url, { method: 'POST' })
        .then(function () {
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

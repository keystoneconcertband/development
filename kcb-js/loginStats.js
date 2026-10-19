var dataTable;

document.addEventListener('DOMContentLoaded', function () {
    dataTable = new DataTable('#kcbLogonTable', {
        order: [2, 'desc'],
        responsive: true,
        ajax: {
            url: 'loginStatsServer.php',
            dataSrc: ''
        },
        columns: [
            { data: 'logonValue' },
            { data: 'valid' },
            { data: 'estbd_dt_tm' }
        ]
    });
});

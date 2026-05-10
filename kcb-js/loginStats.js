document.addEventListener('DOMContentLoaded', function () {
    var table = $('#kcbLogonTable').DataTable({
        order: [2, 'desc'],
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

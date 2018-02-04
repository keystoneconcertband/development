$(document).ready(function () {
    var table = $('#kcbLogonTable').DataTable( {
	    responsive: true,
		"order": [2, "desc" ],
	    "ajax": {
		    "url":"loginStatsServer.php",
			"dataSrc": ""
		},
		"columns": [
            { "data": "logonValue" },
            { "data": "valid" },
            { "data": "estbd_dt_tm" }
        ]
    });
});
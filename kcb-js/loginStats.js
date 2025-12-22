document.addEventListener("DOMContentLoaded", function() {
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

<!-- TODO: Remaining jQuery usages detected in this file. Manually port to vanilla JS or keep jQuery temporarily. -->

$(document).ready(function() {
    var table = $('#kcbDocumentTable').DataTable( {
	    responsive: true,
		"order": [sort, "desc" ],
	    "ajax": {
		    "url":"documentsServer.php",
			"dataSrc": "files"
		},
		"columns": [
			{ data: null, render: function ( data, type, row ) {
				if(office !== "") {
					return '<a href="#nojump"><span class="glyphicon glyphicon-trash" onclick="deleteFile(\''+data.name+'\', \''+data.deleteUrl+'\')"></span></a>';
				}
				else {
					return "";
				}
              } 
            },
			{ data: null, render: function ( data, type, row ) {
				if(data.name) {
					return '<a href="https://docs.google.com/viewer?url='+data.url+'" target="_blank">'+data.name+'</a><br />'
				}
				else {
					return "";
				}
              } 
            },
            { "data": "file_date" },
			{ data: null, render: function ( data, type, row ) {
				if(data.name) {
					return formatSizeUnits(data.size);
				}
				else {
					return "";
				}
              } 
            }
        ]
    });

    var column = table.column(0);
    column.visible(office !== "");
	
    $('#fileupload').fileupload();
});

// On close
$('#modal_upload').on('hidden.bs.modal', function () {
	// Refresh table
	$('#kcbDocumentTable').DataTable().ajax.reload();
});

function deleteFile(name, url) {
	if(confirm("Do you want to remove the file " + name + "?")) {
		$.ajax({
	        url: url,
	        type: "POST",
			dataType : 'json', 
	        data: "",
	        success: function(text){
				$('#kcbDocumentTable').DataTable().ajax.reload();
	        },
			error: function(xhr, resp, text) {
				aler("Oops! An error occurred deleting the file. Please try again later.");
	            console.log(xhr, resp, text);
	        }
	    });	
	}
}

function formatSizeUnits(bytes){
      if      (bytes>=1073741824) {bytes=(bytes/1073741824).toFixed(2)+' GB';}
      else if (bytes>=1048576)    {bytes=(bytes/1048576).toFixed(2)+' MB';}
      else if (bytes>=1024)       {bytes=(bytes/1024).toFixed(2)+' KB';}
      else if (bytes>1)           {bytes=bytes+' bytes';}
      else if (bytes==1)          {bytes=bytes+' byte';}
      else                        {bytes='0 bytes';}
      return bytes;
}
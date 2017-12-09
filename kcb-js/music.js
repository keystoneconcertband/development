$(document).ready(function() {
	$('#dpLastPlayed').datetimepicker({
		format: 'L',
		maxDate: moment().add(7, 'days'),
		showTodayButton: true,
		showClear: true,
		showClose: true
	});
	$("#kcbMusicTable").validator();
    $('#kcbMusicTable').DataTable( {
	    responsive: true,
		stateSave: true,
		"order": [[0, "asc" ]],
	    "ajax": {
		    "url":"musicServer.php",
			"dataSrc": ""
		},
		"columns": [
			{ data: null, render: function ( data, type, row ) {
				if(data.uid) {
					return '<a href="#nojump"><span class="glyphicon glyphicon-remove-circle" onclick="deleteRecord(\''+data.title+'\',  '+data.uid+')"></span></a>';
				}
				else {
					return "";
				}
              } 
            },
            { "data": "title" },
            { "data": "notes" },
			{ data: null, render: function ( data, type, row ) {
				if(data.music_link) {
					return '<a href="'+data.music_link+'" target="_blank">'+data.music_link+'</a><br />'
				}
				else {
					return "";
				}
              } 
            },
            { "data": "last_played" },
            { "data": "number_plays" }
        ]
    });
});

$("#form_music").validator().on("submit", function (event) {
    if (event.isDefaultPrevented()) {
        formError();
        submitMSG(false, "Check for errors in the form.");
    } else {
        event.preventDefault();
        submitForm();
    }
});

function submitForm() {
	$.ajax(
	{
        url: "musicServer.php",
        type: "POST",
		dataType : 'json', 
        data: $("#form_music").serialize(),
        success: function(text){
            if (text === "success"){
                formSuccess();
            } else {
                formError(text);
            }
        },
		error: function(xhr, resp, text) {
			submitMSG(false, "Oops! An error occurred processing the form. Please try again later.");
            console.log(xhr, resp, text);
        }
    });
}

function deleteRecord(title, uid) {
	if(confirm("Do you want to delete title " + title + "?")) {
		$.ajax(
		{
	        url: "musicServer.php",
	        type: "POST",
			dataType : 'json', 
	        data: JSON.parse('{"type":"delete","uid":"'+uid+'"}'),
	        success: function(text){
		        formError(text);
	            if (text === "success"){					 
				    $('#kcbMusicTable').DataTable().ajax.reload();
				    submitMSG(true, "Item successfully deleted.");
	            } else {
	                formError(text);
	            }
	        },
			error: function(xhr, resp, text) {
				submitMSG(false, "Oops! An error occurred processing the form. Please try again later.");
	            console.log(xhr, resp, text);
	        }
	    });
	}
}

function formSuccess() {
	var dt = $('#kcbMusicTable').DataTable();
	dt.row.add({
		"title" : $("#title").val(),
        "notes" : $("#notes").val(),
        "music_link" : $("#music_link").val(),
        "last_played" : $("#last_played").val(),
        "number_plays": $("#last_played").val() === "" ? "0" : "1"
    }).draw(false);

	$("#form_music").trigger('reset'); 
    $('#modal_add_edit').modal('hide');
}

function formError(text) {
    $("#form_music").removeClass().addClass('shake animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
        $(this).removeClass();
    });
    submitMSG(false,text);
}

function submitMSG(valid, msg) {
    if(valid) {
        var msgClasses = "h4 tada animated text-success";
    } else {
        var msgClasses = "h4 text-danger";
    }
    $("#msgSubmit").removeClass().addClass(msgClasses).text(msg);
}
$(document).ready(function() {
	$('#dpLastPlayed').datetimepicker({
		format: 'L',
		maxDate: moment().add(7, 'days'),
		showTodayButton: true,
		showClear: true,
		showClose: true
	});
	$("#kcbMusicTable").validator();
    var table = $('#kcbMusicTable').DataTable( {
	    responsive: true,
		stateSave: true,
		"order": [1, "asc" ],
	    "ajax": {
		    "url":"musicServer.php",
			"dataSrc": ""
		},
		"columns": [
			{ data: null, render: function ( data, type, row ) {
				if(office !== "") {
					var title = data.title.replace(/'/g, '&#96;')
					return '<a href="#nojump"><span class="glyphicon glyphicon-trash" onclick="deleteRecord(\''+title+'\',  '+data.uid+')"></span></a>&nbsp;&nbsp;&nbsp;<a href="#nojump"><span class="glyphicon glyphicon-edit" onclick="showEditRecord('+data.uid+')"></span></a>';
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
            { "data": "genre" },
            { "data": "last_played" },
			{ data: null, render: function ( data, type, row ) {
				if(data.number_plays) {
					// TODO: Update this to display a listing of the last played dates
					return data.number_plays;
					//return '<a href="#'+data.number_plays+'" target="_blank">'+data.number_plays+'</a>'
				}
				else {
					return "0";
				}
              } 
            }
        ]
    });

	// Hide first column if user doesn't have access
    var column = table.column(0);
    column.visible(office !== "");
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

// On close
$('#modal_add_edit').on('hidden.bs.modal', function () {
    // re-hide number of plays each time the modal closes
    $("#nbr_plays_div").hide();
    
    // Clear form each time
   	$("#form_music").trigger('reset');
});

// On load
$('#modal_add_edit').on('show.bs.modal', function () {
  	populateGenreDropdown();
})

function submitForm() {
	// Determine whether we are adding or editing record
	if($("#uid").val() !== "") {
		editRecord($("#uid").val());
	}
	else {
		addRecord();
	}
}

function addRecord() {
	$.ajax(
	{
        url: "musicServer.php",
        type: "POST",
		dataType : 'json', 
        data: $("#form_music").serialize() + '&type=add',
        success: function(text){
            if (text === "success"){
                formSuccess("Item successfully added.");
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

function showEditRecord(uid) {
    // Populate genre dropdown if it hasn't been loaded yet (otherwise the genre isn't selected next)
    populateGenreDropdown();

	$.ajax({
        cache: false,
        type: 'POST',
        url: 'musicServer.php',
        data: JSON.parse('{"type":"getMusicRecord","uid":"'+uid+'"}'),
        success: function(data) {	        
            populateForm('#form_music', data);
            $("#uid").val(uid);
			$("#nbr_plays_div").show();
			$('#modal_add_edit').modal('show');
        },
		error: function(xhr, resp, text) {
			submitMSG(false, "Oops! An error occurred opening the form. Please try again later.");
            console.log(xhr, resp, text);
        }
    });	
}

function editRecord() {
	$.ajax({
        cache: false,
        type: 'POST',
        url: 'musicServer.php',
        data: $("#form_music").serialize() + '&type=edit',
        success: function(text) {
            if (text === "success"){
                formSuccess("Item successfully modified.");
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
                	formSuccess("Item successfully deleted.");
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

function formSuccess(text) {
    submitMSG(true, text);

	$('#kcbMusicTable').DataTable().ajax.reload();
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
    $("#msgMainHeader").removeClass().addClass(msgClasses).text(msg);
    $("#msgSubmit").removeClass().addClass(msgClasses).text(msg);
}

function populateForm(frm, data) {
	$.each(data, function(key, value) {
		$('[name='+key+']', frm).val(value);
	});
}

function populateGenreDropdown() {
	// Only needs to populate the first time the user opens the form
	if($("#genre option").length === 1) {
		$.ajax({
	        cache: false,
	        type: 'POST',
	        url: 'musicServer.php',
	        data: JSON.parse('{"type":"getMusicGenres"}'),
	        success: function(data) {
		        $.each(data, function(key, value) {
		            $("#genre").append("<option>" + value.genre + "</option>");
		        });
	        },
			error: function(xhr, resp, text) {
				submitMSG(false, "Oops! An error occurred opening the form. Please try again later.");
	            console.log(xhr, resp, text);
	        }
	    });	
	}
}
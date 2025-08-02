$(document).ready(function() {
	$('#dpStartDt').datetimepicker({
		format: 'L',
		showTodayButton: true,
		showClear: true,
		showClose: true
	});
	
	$('#dpEndDt').datetimepicker({
		format: 'L',
		showTodayButton: true,
		showClear: true,
		showClose: true
	});
	
	$("#kcbMessageTable").validator();
    var table = $('#kcbMessageTable').DataTable( {
	    responsive: true,
		stateSave: true,
		"order": [1, "asc" ],
	    "ajax": {
		    "url":"homepageMessageServer.php",
			"dataSrc": ""
		},
		"columns": [
			{ data: null, render: function ( data, type, row ) {
				var title = data.title.replace(/'/g, '&#96;')
				return '<a href="#nojump"><span class="fa fa-edit" onclick="showEditRecord('+data.uid+')"></span></a>';
              } 
            },
            { "data": "title" },
            { "data": "message" },
            { "data": "message_type" },
            { "data": "start_dt" },
            { "data": "end_dt" }
        ]
    });
});

$("#form_message").validator().on("submit", function (event) {
    if (event.isDefaultPrevented()) {
        formError();
        submitMSG(false, "Check for errors in the form.");
    } else {
        event.preventDefault();
        submitForm();
    }
});

// On load
$('#modal_add_edit').on('show.bs.modal', function () {
    // Clear messages
    $("#msgMainHeader").removeClass().text("");
    $("#msgSubmit").removeClass().text("");
});


// On close
$('#modal_add_edit').on('hidden.bs.modal', function () {
    // Clear form each time
   	$("#form_message").trigger('reset');

   	// Reset UID
    $("#uid").val("");    
});

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
        url: "homepageMessageServer.php",
        type: "POST",
		dataType : 'json', 
        data: $("#form_message").serialize() + '&type=add',
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
	$.ajax({
        cache: false,
        type: 'POST',
        url: 'homepageMessageServer.php',
        data: JSON.parse('{"type":"getHomepageMessageRecord","uid":"'+uid+'"}'),
        success: function(data) {	        
            populateForm('#form_message', data);
            $("#uid").val(uid);
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
        url: 'homepageMessageServer.php',
        data: $("#form_message").serialize() + '&type=edit',
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

function checkDates(date) {
	$.ajax({
        cache: false,
        type: 'POST',
        url: 'homepageMessageServer.php',
        data: JSON.parse('{"type":"homepageMessageDateConflictCheck","date":"'+date+'"}'),
        success: function(data) {
	        if(data !== 0) {
		        console.log("conflict");
		        formError("Date conflicts with message already in system.");
		    }
		    else {
			    console.log("here");
			    $("#msgMainHeader").removeClass().text("");
			    $("#msgSubmit").removeClass().text("");
		    }
        },
		error: function(xhr, resp, text) {
			submitMSG(false, "Oops! An error occurred opening the form. Please try again later.");
            console.log(xhr, resp, text);
        }
    });	
}


function formSuccess(text) {
    submitMSG(true, text);

	$('#kcbMessageTable').DataTable().ajax.reload();
    $('#modal_add_edit').modal('hide');
}

function formError(text) {
    $("#form_message").removeClass().addClass('shake animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
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
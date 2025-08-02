$(document).ready(function() {
    var table = $('#kcbMemberTable').DataTable( {
	    responsive: true,
		"order": [1, "asc" ],
	    "ajax": {
		    "url":"membersServer.php",
			"dataSrc": ""
		},
		"columns": [
			{ data: null, render: function ( data, type, row ) {
				if(accountType === "1" || accountType === "2") {
					return '<a href="#nojump"><span class="fa fa-trash-o" onclick="deleteRecord(\''+data.fullName+'\', '+data.uid+')"></span></a>&nbsp;&nbsp;&nbsp;<a href="#nojump"><span class="fa fa-edit" onclick="showEditRecord('+data.uid+')"></span></a>';
				}
				else {
					return "";
				}
              } 
            },
            { "data": "fullName" },
			{ data: null, render: function ( data, type, row ) {
				if(data.email) {
					var email_arr = data.email.split(',');
					var email_out = "";
					for(var i = 0; i < email_arr.length; i++) {
						email_out += '<a href="mailto:'+email_arr[i]+'">'+email_arr[i]+'</a><br />'
					}
					return email_out;
				}
				else {
					return "";
				}
              } 
            },
			{ data: null, render: function ( data, type, row ) {
				if(data.instrument) {
                	return data.instrument.replace(/,/g, '<br/>');
                }
                else {
	                return "";
                }
              } 
            },
			{ data: null, render: function ( data, type, row ) {
				if(data.text) {
                	return data.text.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
                }
                else {
	                return "";
                }
              } 
            },
			{ data: null, render: function ( data, type, row ) {
				if(data.home_phone) {
                	return data.home_phone.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
                }
                else {
	                return "";
                }
              } 
            },
			{ data: null, render: function ( data, type, row ) {
			    if(data.address1) {
				    var addr = data.address1 + '<br />';
				    							
					if(data.address2) {
						addr += data.address2 + '<br />';
					}
					
					addr += data.city + ', ' + data.state + ' ' + data.zip;
					
					return addr;
			    }
                else {
	                return "";
                }
              } 
            },
            { "data": "office" }
        ]
    });

	// Hide first column if user doesn't have access
    var column = table.column(0);
    column.visible(accountType === "1" || accountType === "2");

	$('#addRow').click(function () {
		var lastId = $('.emailContainers:last').attr('id');
		lastId = lastId.replace('emailContainer','');
		lastIdInt = parseInt(lastId);
		emailCount = lastIdInt + 1;		
				
	    $('#emailContainer' + lastIdInt).after('<div class="form-group row emailContainers" id="emailContainer'+emailCount+'" style="display:none"><div class="col-sm-12"><label for="Email" class="control-label">Email '+emailCount+'</label><div class="input-group"><input type="email" class="form-control" name="email[]" id="email[]" placeholder="Email Address '+emailCount+'" maxlength="100" value=""><span class="input-group-text"><a href="#noscroll" id="email'+emailCount+'" onclick="deleteEmail(\'emailContainer'+emailCount+'\');"><span class="fa fa-remove"></span></a></span></div></div></div>');
		$('.emailContainers').next("div").slideDown("slow");
	});
});

$("#form_member").validator().on("submit", function (event) {
    if (event.isDefaultPrevented()) {
        formError("Check for errors in the form.");
    } else {
        event.preventDefault();
        submitForm();
    }
});

// On load
$('#modal_edit_delete').on('show.bs.modal', function () {
    // Clear messages
    $("#msgMainHeader").removeClass().text("");
    $("#msgSubmit").removeClass().text("");
});

// On close
$('#modal_edit_delete').on('hidden.bs.modal', function () {
    // Clear form each time
   	$("#form_member").trigger('reset');
    $("#uid").val("");
   	$("div").remove('.emailContainers');
   	
   	// Readd the email container
   	$('#zipContainer').after('<div class="form-group row emailContainers" id="emailContainer1"><div class="col-sm-12"><label for="Email" class="control-label">Email</label><div class="input-group"><input type="email" class="form-control email1" name="email[]" id="email[]" placeholder="Email Address" maxlength="100"><span class="input-group-text"><a href="#noscroll" id="email1" onclick="deleteEmail(\'emailContainer1\');"><span class="fa fa-remove"></span></a></span></div></div></div>');
});

function deleteEmail(emailContainer) {
	var numItems = $('.emailContainers').length
	
	if(numItems < 2) {
		formError("You must keep at least one email address.");
	}
	else {
		$("#" + emailContainer).remove();	
	}
}

function submitForm() {
	// Determine whether we are adding or editing record		
	if($("#uid").val() !== "") {
		editRecord();
	}
	else {
		addRecord();
	}
}

function addRecord() {
	$.ajax(
	{
        url: "membersServer.php",
        type: "POST",
		dataType : 'json', 
        data: $("#form_member").serialize() + '&type=add',
        success: function(text){
            if (text === "success"){
                formSuccess("User successfully added.");
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
        url: 'membersServer.php',
        data: JSON.parse('{"type":"getMemberRecord","uid":"'+uid+'"}'),
        success: function(data) {	        
            populateForm('#form_member', data);
            populateEmail(data);
            populateInstrument(data);

			if(data.displayFullName === 1) {
				$('#displayFullName').prop('checked', true);
			}
            if(data.carrier) {
    	        $("#carrier").val(data.carrier).change();            
            }
            else {
	            $("#carrier").val("0").change();            
            }            
            
            $("#uid").val(uid);
			$('#modal_edit_delete').modal('show');
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
        url: 'membersServer.php',
        data: $("#form_member").serialize() + '&type=edit',
        success: function(text) {
            if (text === "success"){
                formSuccess("User successfully modified.");
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
	if(confirm("Do you want to remove " + title + " from the band roster and email list?")) {
		$.ajax(
		{
	        url: "membersServer.php",
	        type: "POST",
			dataType : 'json', 
	        data: JSON.parse('{"type":"delete","uid":"'+uid+'"}'),
	        success: function(text){
		        formError(text);
	            if (text === "success"){					 
                	formSuccess("User successfully removed.");
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

	$('#kcbMemberTable').DataTable().ajax.reload();
	$("#form_member").trigger('reset'); 
    $('#modal_edit_delete').modal('hide');
}

function formError(text) {
    $("#form_member").removeClass().addClass('shake animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
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

function populateEmail(data) {
	var email = data.email;

	if(email !== null && email !== '') {
		if(~email.indexOf(",")) {
			var arr = email.split(',');
		    for(var i = 0; i<arr.length; i++){
			    var emailCount = i+1;
			    if(i === 0) {
				    $(".email1").val(arr[i]);
			    }
			    else {
					$('#emailContainer' + i).after('<div class="form-group row emailContainers" id="emailContainer'+emailCount+'"><div class="col-sm-12"><label for="Email" class="control-label">Email '+emailCount+'</label><div class="input-group"><input type="email" class="form-control" name="email[]" id="email[]" placeholder="Email Address '+emailCount+'" maxlength="100" value="'+arr[i]+'"><span class="input-group-text"><a href="#noscroll" id="email'+emailCount+'" onclick="deleteEmail(\'emailContainer'+emailCount+'\');"><span class="fa fa-remove"></span></a></span></div></div></div>');				    
			    }
		    }
		}
		else {
			$(".email1").val(email);			
		}
	}
}

function populateInstrument(data) {
	if(data.instrument) {
		var arr = data.instrument.split(',');
	    for(var i = 0; i<arr.length; i++){
			$('#' + arr[i]).prop('checked', true);
	    }		
	}
}

function printMembers() {
	var win = window.open('membersPrint.php', "Print Members", "menubar=0,location=0,height=700,width=700");
}
$(document).ready(function () {
	$.ajax({
        cache: false,
        type: 'POST',
        url: 'membersServer.php',
        data: JSON.parse('{"type":"getCurrentMemberRecord"}'),
        success: function(data) {	        
            populateForm('#memberInfo', data);
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
        },
		error: function(xhr, resp, text) {
			submitMSG(false, "Oops! An error occurred opening the form. Please try again later.");
            console.log(xhr, resp, text);
        }
    });	

	$('#addRow').click(function () {
		var lastId = $('.emailContainers:last').attr('id');
		lastId = lastId.replace('emailContainer','');
		lastIdInt = parseInt(lastId);
		emailCount = lastIdInt + 1;		
		
		$('.emailContainers:last').after('<div class="form-group emailContainers" id="emailContainer'+emailCount+'" style="display:none"><div class="col-sm-12"><label for="Email" class="control-label">Email '+emailCount+'</label><input type="email" class="form-control" name="email[]" id="email[]" placeholder="Email Address '+emailCount+'" maxlength="100" value=""></div></div>');
		$('.emailContainers').next("div").slideDown("slow");
	});
});

$("#memberInfo").validator({
    custom: {
        'carrier': function($el) {
	        var cell = $("#inputCellPhoneNbr").val();
	        var carrier = $("#inputCarrier").val();
	        
	        if(cell !== '' && carrier === '0') {
		    	return "Carrier is required when cell phone nbr is entered.";   
	        }
	    }
    }
});

$("#memberInfo").validator().on("submit", function (event) {
    if (event.isDefaultPrevented()) {
        formError();
        submitMSG(false, "Oops! Looks like you have a validation error. Check for errors in the form.");
    } else {
        event.preventDefault();
        submitForm();
    }
});

function submitForm() {
	$.ajax(
	{
        url: "myInfoServer.php",
        type: "POST",
		dataType : 'json', 
        data: $("#memberInfo").serialize(),
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

function formSuccess(){
    submitMSG(true, "Your information has been updated!");
}

function formError(text){
    $("#memberInfo").removeClass().addClass('shake animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
        $(this).removeClass();
    });
    submitMSG(false,text);
}

function submitMSG(valid, msg){
    if(valid){
        var msgClasses = "h4 tada animated text-success";
    } else {
        var msgClasses = "h4 text-danger";
    }
    $("#msgSubmit").removeClass().addClass(msgClasses).text(msg);
}

function deleteEmail(emailContainer) {
	$("#" + emailContainer).remove();
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
				    $('#emailContainer' + i).after('<div class="form-group emailContainers" id="emailContainer'+emailCount+'"><div class="col-sm-12"><label for="Email" class="control-label">Email '+emailCount+'</label><div class="input-group"><input type="email" class="form-control" name="email[]" id="email[]" placeholder="Email Address '+emailCount+'" maxlength="100" value="'+arr[i]+'"><span class="input-group-addon"><a href="#noscroll" id="email'+emailCount+'" onclick="deleteEmail(\'emailContainer'+emailCount+'\');"><span class="glyphicon glyphicon-remove"></span></a></span></div></div></div>');
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
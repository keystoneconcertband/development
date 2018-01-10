$(document).ready(function () {
	$('#addRow').click(function () {
		$('<div/>', {
			'class' : 'col-lg-12 extraEmail', html: GetHtml()
		}).hide().appendTo('#emailContainer').slideDown('slow'); //Get the html from template and hide and slideDown for transtion.
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

function GetHtml() //Get the template and update the input field names
{
	var len = $('.extraEmail').length + 1;
	var $html = $('.extraEmailTemplate').clone();
	$html.find('[name=lblEmail]')[0].id = "lblEmail" + len;
	$html.find('[name=lblEmail]').text("email " + len);
	$html.find('[name=lblEmail]').attr('for', "email" + len);
	$html.find('[name=lblEmail]').attr('name', "lblEmail" + len);
	
	return $html.html();    
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

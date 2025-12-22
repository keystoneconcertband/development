$("#frmBook").validator().on("submit", function (event) {
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
        url: "bookServer.php",
        type: "POST",
		dataType : 'json', 
        data: $("#frmBook").serialize(),
        success: function(text){
            if (text === "success"){
                formSuccess();
            } else {
                formError();
                submitMSG(false,text);
            }
        },
		error: function(xhr, resp, text) {
			submitMSG(false, "Oops! An error occurred processing the form. Please try again later.");
            console.log(xhr, resp, text);
        }
    });
}

function formSuccess(){
	$('#frmBook').trigger("reset");
    submitMSG(true, "Thanks for submitting your information. We will reply back shortly.");
}

function formError(){
    $("#frmBook").removeClass().addClass('shake animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
        $(this).removeClass();
    });
}

function submitMSG(valid, msg){
    if(valid){
        var msgClasses = "h4 tada animated text-success";
    } else {
        var msgClasses = "h4 text-danger";
    }
    $("#msgSubmit").removeClass().addClass(msgClasses).text(msg);
}


<!-- TODO: Remaining jQuery usages detected in this file. Manually port to vanilla JS or keep jQuery temporarily. -->

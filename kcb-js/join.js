function submitForm() {
	$.ajax(
	{
        url: "joinServer.php",
        type: "POST",
		dataType : 'json', 
        data: $("#frmJoin").serialize(),
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
	$('#frmJoin').trigger("reset");
    submitMSG(true, "Thanks for submitting your information. We will reply back shortly.");
}

function formError(){
    $("#frmJoin").removeClass().addClass('shake animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
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

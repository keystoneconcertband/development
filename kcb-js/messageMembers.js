$("#message").keyup(function(){
  $("#messageCount").show();
  $("#messageCount").text("Message is " + $(this).val().length + " characters long.");
});

$("#frmMessage").validator().on("submit", function (event) {
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
        url: "messageMembersServer.php",
        type: "POST",
		dataType : 'json', 
        data: $("#frmMessage").serialize(),
        success: function(text){
            if (text){
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
	$("#message").val("");
	$("#messageCount").val("");
	$("#messageCount").hide();
    submitMSG(true, "Message successfully sent!");
}

function formError(){
    $("#frmMessage").removeClass().addClass('shake animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
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

$("#loginModal").on("shown.bs.modal", function () {
	$("#email").focus();
});

$("#loginModal").on("hide.bs.modal", function () {
	$("#email").val("");
	$("#auth_cd").val("");
	$("#div_auth").hide();
});

$("#memberLogin").click(function () {
	if(!$("#email").val()) {
		alert("Email address is required to continue.");
	  	$("#email").focus();
		return false;
	}
	else if (!$("#auth_cd").val()) {
		$.ajax({
			type: "GET",
			url: "/membersServer.php",
			cache: false,
			data: {email: $("#email").val().trim()}
		}).done(function( msg ) {
			if (msg == "valid") {
				window.location = "members/index.php";
			}
			else if(msg == "invalid") {
				alert("Sorry that email address is not valid for login.");
				$("#email").val("");
				$("#email").focus();
			}
			else if(msg == "invalid_pending") {
				alert("Sorry that email address is not valid for login.");
				$("#email").val("");
				$("#email").focus();
			}
			else if(msg == "invalid_request") {
				alert("Email address is required for login.");
				$("#email").focus();
			}
			else if(msg == "auth_required_no_cookie") {
				$("#div_auth").show();
			}
			else if(msg == "auth_cd_not_expired") {
				$("#div_auth").show();
			}
			else if(msg == "auth_failed_invalid_cookie") {
				alert("Sorry, your cookie has either been lost or corrupted. Please re-authenticate.");
				$("#div_auth").show();
			}
			else if(msg.lastIndexOf("over_max_requests", 0) === 0) {
				var timeout = msg.substring(msg.indexOf("__")+2,msg.length)
				alert("Sorry, your account has been disabled due to too many invalid login attempts.\n\nPlease try again after " + timeout);
			}
			else if(msg == "db_error") {
				alert("Oops! We had a problem communicating with the database. Please try again later.");
			}
			else {
				alert("Unable to login. Please try again later. Msg: " + msg);
				$("#email").focus();
			}
		});
	}
	else if ($("#email").val() && $("#auth_cd").val()) {		
		$.ajax({
			type: "GET",
			url: "/membersServer.php",
			cache: false,
			data: {email: $("#email").val().trim(), auth_cd: $("#auth_cd").val(), auth_remember: $("#auth_remember").is(':checked')}
		}).done(function( msg ) {
			if (msg == "valid") {
				window.location = "members/index.php";
			}
			else if(msg == "auth_invalid") {
				alert("Sorry that auth code was not valid.");
				$("#auth_cd").val("");
				$("#auth_cd").focus();
			}
			else if(msg == "invalid") {
				alert("Sorry that email address is not valid for login.");
				$("#email").val("");
				$("#email").focus();
			}
			else if(msg == "auth_old") {
				alert("That Auth Code has expired. A new auth code has been sent.");
				$("#auth_cd").val("");
				$("#auth_cd").focus();
			}
			else if(msg.lastIndexOf("over_max_requests", 0) === 0) {
				var timeout = msg.substring(msg.indexOf("__")+2,msg.length)
				alert("Sorry, you incorrectly entered the auth code 3 times. Your account has been disabled for one hour.\n\nPlease try again after " + timeout);
			}
			else if(msg == "db_error") {
				alert("Oops! We had a problem communicating with the database. Please try again later.");
			}
			else {
				alert("Unable to validate auth code. Msg: " + msg);
				$("#email").focus();
			}
		});
	}
	else {
		alert("Unknown selection.");
		return false;
	}
});

$(function(){
	/* Fix enter key not properly submitting form */
	$('.modal-content').keypress(function(e) {
		if(e.which == 13) {
		  $("#memberLogin").click();
		}
	})
});

function statusChangeCallback(response) {
	if(response.status == "connected") {
		console.log("Connected!");
		FB.api('/me', {fields: 'name, email'}, function(response) {
		    console.log(JSON.stringify(response));			
			$.ajax({
				type: "GET",
				url: "/membersServer.php",
				cache: false,
				data: {email: response.email, fb_id: response.id}
			}).done(function( msg ) {
				if (msg == "fb_valid") {
					window.location = "members/index.php";
				}
				else if (msg == "valid") {
					alert("Something terribly went wrong. This status is not valid. Please contact web@keystoneconcertband.com for help!");
				}
				else if (msg == "sig_not_match") {
					alert("Please retry. Validation of the facebook authentication failed.");
				}
				else if (msg == "fb_session_hijack") {
					alert("Please retry. Your facebook session cookie has expired.");
				}
				else if (msg == "no_fb_cookie") {
					alert("Please retry. You are missing the facebook cookie. Re-authenticating usually fixes it.");
				}
				else if(msg == "invalid") {
					alert("Sorry that email address is not in our system. You must be an active member to login.");
					$("#email").val("");
					$("#email").focus();
				}
				else if(msg == "db_error") {
					alert("Oops! We had a problem communicating with the database. Please try again later.");
				}
				else {
					alert("Unable to validate facebook login. Msg: " + msg);
				}
			});
		});
	}
}

function checkLoginState() {
  FB.getLoginStatus(function(response) {
    statusChangeCallback(response);
  });
}

<!-- TODO: Remaining jQuery usages detected in this file. Manually port to vanilla JS or keep jQuery temporarily. -->

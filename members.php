<?php
	include_once('includes/class/member.class.php');
	$mbr = new Member(false);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
	<?php require_once 'includes/common_meta.php'; ?>
    <meta name="description" content="The members of the Keystone Concert Band.">
    <title>Members - Keystone Concert Band</title>

	<?php require_once 'includes/common_css.php'; ?>

  </head>

  <body>
	<script>
	  window.fbAsyncInit = function() {
	    FB.init({
	      appId      : '129894350764',
	      cookie     : true,
	      xfbml      : true,
	      version    : 'v3.3'
	    });
	      
	    FB.AppEvents.logPageView();   
	  };
	
	  (function(d, s, id){
	     var js, fjs = d.getElementsByTagName(s)[0];
	     if (d.getElementById(id)) {return;}
	     js = d.createElement(s); js.id = id;
	     js.src = "https://connect.facebook.net/en_US/sdk.js";
	     fjs.parentNode.insertBefore(js, fjs);
	   }(document, 'script', 'facebook-jssdk'));
	</script>
	<?php require_once 'includes/nav.php'; ?>
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="bs-component">
					<div class="jumbotron">
						<h1>Members</h1>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<div class="page-header">
					<h2>Members</h2>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="row">
							<div class="col-sm-10" style="margin-bottom: 15px;">
								All members of the band are volunteers and play for the love of playing! Interested in adding your name to this page and joining the band? Just
								contact us via the <a href="join.php">Join Us</a> page and we'll be sure to get back to you!
							</div>
							<div class="col-sm-2" style="margin-bottom: 15px;">
								<a href="#" class="btn btn-primary" type="button" data-toggle="modal" data-target="#loginModal">KCB Member Login</a>
							</div>
						</div>
					</div>
					<div class="col-lg-12">
						<div class="list-group">
							<h3 class="list-group-item-heading">Baritone</h3>
							<p class="list-group-item-text"><?php getInstrument('baritone'); ?></p>
						</div>							
					</div>
					<div class="col-lg-12">
						<div class="list-group">
							<h3 class="list-group-item-heading">Bass Clarinet</h3>
							<p class="list-group-item-text"><?php getInstrument('bassClarinet'); ?></p>
						</div>							
					</div>
					<div class="col-lg-12">
						<div class="list-group">
							<h3 class="list-group-item-heading">Bassoon</h3>
							<p class="list-group-item-text"><?php getInstrument('bassoon'); ?></p>
						</div>							
					</div>
					<div class="col-lg-12">
						<div class="list-group">
							<h3 class="list-group-item-heading">Clarinet</h3>
							<p class="list-group-item-text"><?php getInstrument('clarinet'); ?></p>
						</div>							
					</div>
					<div class="col-lg-12">
						<div class="list-group">
							<h3 class="list-group-item-heading">Flute</h3>
							<p class="list-group-item-text"><?php getInstrument('flute'); ?></p>
						</div>							
					</div>
					<div class="col-lg-12">
						<div class="list-group">
							<h3 class="list-group-item-heading">French Horn</h3>
							<p class="list-group-item-text"><?php getInstrument('frenchHorn'); ?></p>
						</div>							
					</div>
					<div class="col-lg-12">
						<div class="list-group">
							<h3 class="list-group-item-heading">Oboe</h3>
							<p class="list-group-item-text"><?php getInstrument('oboe'); ?></p>
						</div>							
					</div>
					<div class="col-lg-12">
						<div class="list-group">
							<h3 class="list-group-item-heading">Percussion</h3>
							<p class="list-group-item-text"><?php getInstrument('percussion'); ?></p>
						</div>							
					</div>
					<div class="col-lg-12">
						<div class="list-group">
							<h3 class="list-group-item-heading">Saxophone</h3>
							<p class="list-group-item-text"><?php getInstrument('saxophone'); ?></p>
						</div>							
					</div>
					<div class="col-lg-12">
						<div class="list-group">
							<h3 class="list-group-item-heading">Trombone</h3>
							<p class="list-group-item-text"><?php getInstrument('trombone'); ?></p>
						</div>							
					</div>
					<div class="col-lg-12">
						<div class="list-group">
							<h3 class="list-group-item-heading">Trumpet</h3>
							<p class="list-group-item-text"><?php getInstrument('trumpet'); ?></p>
						</div>							
					</div>
					<div class="col-lg-12">
						<div class="list-group">
							<h3 class="list-group-item-heading">Tuba</h3>
							<p class="list-group-item-text"><?php getInstrument('tuba'); ?></p>
						</div>							
					</div>
				</div>	
			</div>
		</div>
		<div id="loginModal" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<a type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</a>
						<h4 class="modal-title">KCB Member Site Login</h4>
					</div>
					<div class="modal-body">
						<p>Access to the member site requires that you be an active member with an email address. 
							You can login to the site with the email address you have on file with us or use
							the Facebook Login button. <a href="privacy.php">Review the privacy page for what 
							information we require from Facebook for authentication.</a>
							<br><br>
							If you login with the email address and it's your first time, you'll be prompted to provide 
							additional authentication to access the site.
						</p>
						<form class="form-horizontal" id="frmLogin" name="frmLogin">
						    <div class="form-group">
						      <label for="email" class="col-lg-3 control-label">Email Address</label>
						      <div class="col-lg-9">
						        <input type="text" class="form-control" id="email" placeholder="Email Address">
						      </div>
						    </div>
						    <div class="form-group" id="div_auth" style="display:none;">
								<label for="auth_cd" class="col-lg-3 control-label">Additional Authentication Required</label>
								<div class="col-lg-9">
									<input type="text" class="form-control" id="auth_cd" placeholder="Login Code" maxlength="6">
									<input type="checkbox" id="auth_remember" checked="checked"> Remember me (do not use on public computers)
									<p class="help-block">This is the first time this account is being accessed from this computer (or your
									    cookies have been deleted since the last time you logged in.) You have received 
									    a text (or email if you don't have texting enabled) with a 6-digit code. 
									    <strong>Please enter this code in the text box above</strong>.
									</p>
								</div>
							</div>
							<div class="form-group">
								<label for="fb" class="col-lg-3 control-label"></label>
						      <div class="col-lg-9">
							  	<strong> -- OR -- </strong>
						      </div>
							</div>
							<div class="form-group">
								<label for="fb" class="col-lg-3 control-label"></label>
						      <div class="col-lg-9">
								<div id="fb" class="fb-login-button" data-width="" data-size="medium" data-button-type="login_with" data-auto-logout-link="false" data-use-continue-as="false" data-onlogin="checkLoginState()" data-scope="public_profile,email"></div>
						      </div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
				        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				        <button type="button" class="btn btn-primary" id="memberLogin">Submit</button>
					</div>
				</div>
			</div>
		</div>

		<?php require_once 'includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require_once 'includes/common_js.php'; ?>
	<script src="kcb-js/memberlogin-20190707.js"></script>
  </body>
</html>
<?php
function getInstrument($instrument) {
	global $mbr; // This must be in this function so that it can access the variable defined outside it.
	$counter = 0;
	$members = $mbr->getMembers($instrument);

	if(count($members) == 0){
		echo("There are no members who currently play this instrument!<br /><a href='join.php'>Come join us!</a>");
	}
	else {
		foreach ($members as $member) {
			if ($member['displayFullName'] == '1') {
				echo($member['firstName'] . ' ' . $member['lastName']);
			}
			else {
				echo($member['firstName'] . ' ' . substr($member['lastName'], 0, 1));
			}

			if (++$counter != count($members)) {
		        echo ", ";
		    }
		}
	}
}
?>
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
    <?php require_once 'includes/nav.php'; ?>
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="jumbotron">
                    <h1 class="display-5">Members</h1>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="mb-4">
                    <h2>Members</h2>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-sm-10 mb-3">
                                All members of the band are volunteers and play for the love of playing! Interested in
                                adding your name to this page and joining the band? Just
                                contact us via the <a href="join.php">Join Us</a> page and we'll be sure to get back to
                                you!
                            </div>
                            <div class="col-sm-2 mb-3">
                                <a href="#" class="btn btn-primary" type="button" data-bs-toggle="modal"
                                    data-bs-target="#loginModal">KCB Member Login</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="list-group mb-3">
                            <div class="list-group-item">
                                <h3 class="h5 mb-2">Baritone</h3>
                                <p class="mb-0"><?php getInstrument('baritone'); ?></p>
                            </div>
                            <div class="list-group-item">
                                <h3 class="h5 mb-2">Bass Clarinet</h3>
                                <p class="mb-0"><?php getInstrument('bassClarinet'); ?></p>
                            </div>
                            <div class="list-group-item">
                                <h3 class="h5 mb-2">Bassoon</h3>
                                <p class="mb-0"><?php getInstrument('bassoon'); ?></p>
                            </div>
                            <div class="list-group-item">
                                <h3 class="h5 mb-2">Clarinet</h3>
                                <p class="mb-0"><?php getInstrument('clarinet'); ?></p>
                            </div>
                            <div class="list-group-item">
                                <h3 class="h5 mb-2">Flute</h3>
                                <p class="mb-0"><?php getInstrument('flute'); ?></p>
                            </div>
                            <div class="list-group-item">
                                <h3 class="h5 mb-2">French Horn</h3>
                                <p class="mb-0"><?php getInstrument('frenchHorn'); ?></p>
                            </div>
                            <div class="list-group-item">
                                <h3 class="h5 mb-2">Oboe</h3>
                                <p class="mb-0"><?php getInstrument('oboe'); ?></p>
                            </div>
                            <div class="list-group-item">
                                <h3 class="h5 mb-2">Percussion</h3>
                                <p class="mb-0"><?php getInstrument('percussion'); ?></p>
                            </div>
                            <div class="list-group-item">
                                <h3 class="h5 mb-2">Saxophone</h3>
                                <p class="mb-0"><?php getInstrument('saxophone'); ?></p>
                            </div>
                            <div class="list-group-item">
                                <h3 class="h5 mb-2">Trombone</h3>
                                <p class="mb-0"><?php getInstrument('trombone'); ?></p>
                            </div>
                            <div class="list-group-item">
                                <h3 class="h5 mb-2">Trumpet</h3>
                                <p class="mb-0"><?php getInstrument('trumpet'); ?></p>
                            </div>
                            <div class="list-group-item">
                                <h3 class="h5 mb-2">Tuba</h3>
                                <p class="mb-0"><?php getInstrument('tuba'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="loginModal" class="modal fade" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="loginModalLabel">KCB Member Site Login</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                        <form class="row g-3" id="frmLogin" name="frmLogin">
                            <div class="col-12">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="text" class="form-control" id="email" placeholder="Email Address">
                            </div>
                            <div class="col-12 d-none" id="div_auth">
                                <label for="auth_cd" class="form-label">Additional Authentication Required</label>
                                <input type="text" class="form-control mb-2" id="auth_cd" placeholder="Login Code"
                                    maxlength="6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="auth_remember" checked>
                                    <label class="form-check-label" for="auth_remember">Remember me (do not use on
                                        public computers)</label>
                                </div>
                                <p class="form-text">This is the first time this account is being accessed from this
                                    computer (or your
                                    cookies have been deleted since the last time you logged in.) You have received
                                    a text (or email if you don't have texting enabled) with a 6-digit code.
                                    <strong>Please enter this code in the text box above</strong>.
                                </p>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
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
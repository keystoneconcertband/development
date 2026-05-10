<?php
include_once '../includes/class/member.class.php';
new Member(true);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require '../includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band member area">

    <title>Member Area - Keystone Concert Band</title>

    <?php require '../includes/common_css.php'; ?>
    <link href="<?= asset('/css/member.css') ?>" rel="stylesheet">

</head>

<body>

    <?php require '../includes/nav.php'; ?>
    <div class="container">
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-lg-12">
                <div class="mb-4 pb-2 border-bottom">
                    <h2>Hey there
                        <?php echo $_SESSION["office"] . ' ' ?: "" ?><?php echo $_SESSION["firstName"] ?: 'Firstname'?></h2>
                </div>
				<!--
                <div class='alert alert-info'><strong>Discord</strong><br />Please join us on Discord (a group chat
                    platform) at <a href="https://discord.gg/Szux9TQ" target="_blank">https://discord.gg/Szux9TQ</a>
                </div>
				-->
                The info here can help provide some information with the operation of the band.<br>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="bs-component">
                    <div class="card border-info bg-light mb-4">
                        <div class="card-body">
                            <h4 class="card-title"><i class="fa fa-shield me-2" aria-hidden="true"></i>Administrative</h4>
                            <h6 class="card-subtitle mb-2 text-muted">Administrative functions for managing the band. Only visible to officers and administrators.
                            </h6>
                            <i class="fa fa-info-circle me-2" aria-hidden="true"></i><a href="loginStats.php" class="card-link">Login Statistics</a> | 
                            <i class="fa fa-exclamation me-2" aria-hidden="true"></i><a href="homepageMessage.php" class="card-link">Homepage Message</a> | 
                            <i class="fa fa-user-plus me-2" aria-hidden="true"></i><a href="pendingMembers.php" class="card-link">Pending Members</a> | 
                            <i class="fa fa-user-times me-2" aria-hidden="true"></i><a href="inactiveMembers.php" class="card-link">Inactive Members</a>
                        </div>
                    </div>
                </div>
            </div>

			<div class="col-lg-6">
                <div class="bs-component">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="card-title"><i class="fa fa-user me-2" aria-hidden="true"></i>My Info</h4>
                            <h6 class="card-subtitle mb-2 text-muted">View or update your information.
                            </h6>
                            <a href="myInfo.php" class="card-link">Go to My Info</a>
                        </div>
                    </div>
                </div>
                <div class="bs-component">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="card-title"><i class="fa fa-users me-2" aria-hidden="true"></i>Roster</h4>
                            <h6 class="card-subtitle mb-2 text-muted">View the current list of band members.</h6>
                            <a href="members.php" class="card-link">Go to Roster</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="bs-component">
					<div class="card mb-4">
						<div class="card-body">
							<h4 class="card-title"><i class="fa fa-music me-2" aria-hidden="true"></i>Music</h4>
							<h6 class="card-subtitle mb-2 text-muted">List of music we have in the library.</h6>
							<a href="music.php" class="card-link">Go to Music</a>
						</div>
					</div>
				</div>
                <div class="bs-component">
					<div class="card mb-3">
						<div class="card-body">
							<h4 class="card-title"><i class="fa fa-file-text me-2" aria-hidden="true"></i>Documents</h4>
							<h6 class="card-subtitle mb-2 text-muted">View and download band documents.</h6>
							<a href="documents.php" class="card-link">Go to Documents</a>
						</div>
					</div>
				</div>
            </div>
        </div>
        <?php require '../includes/footer.php'; ?>
    </div> <!-- /container -->

    <?php require '../includes/common_js.php'; ?>
</body>

</html>
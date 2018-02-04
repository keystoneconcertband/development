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
	<link href="/css/member.css" rel="stylesheet">

	<style type="text/css">
		a.kcb-a:hover {
			text-decoration: none;
		}
		.caption h3 {
			margin-top:0px;
		}
	</style>
  </head>

  <body>

	<?php require '../includes/nav.php'; ?>
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="bs-component">
					<div class="jumbotron">
						<h1>KCB Members</h1>
					</div>
				</div>
			</div>
		</div>
		<div class="row" style="margin-bottom: 20px;">
			<div class="col-lg-12">
				<div class="page-header">
					<h2>Welcome <?php echo $_SESSION["office"] . ' ' ?: "" ?><?php echo $_SESSION["firstName"] ?: 'Firstname'?> <?php echo $_SESSION["lastName"] ?: 'Lastname' ?>!</h2>
				</div>
				Welcome to the KCB Member section! With this site you can update your information, view the other band member's
				information, and listen to music the band has played.<br>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
				<a href="myInfo.php" class="kcb-a">
					<div class="panel price panel-red">
						<div class="panel-heading  text-center">
						<h3>My Info</h3>
						</div>
						<ul class="list-group list-group-flush text-center">
							<li class="list-group-item"><i class="icon-ok text-danger"></i> Update your information we have for you</li>
						</ul>
					</div>
				</a>
			</div>
			
			<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
				<a href="members.php" class="kcb-a">
					<div class="panel price panel-blue">
						<div class="panel-heading arrow_box text-center">
						<h3>Members</h3>
						</div>
						<ul class="list-group list-group-flush text-center">
							<li class="list-group-item"><i class="icon-ok text-info"></i> View the other band member's information</li>
						</ul>
					</div>
				</a>
			</div>
			
			<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
				<a href="music.php" class="kcb-a">
					<div class="panel price panel-green">
						<div class="panel-heading arrow_box text-center">
						<h3>Music</h3>
						</div>
						<ul class="list-group list-group-flush text-center">
							<li class="list-group-item"><i class="icon-ok text-success"></i> View links and history of the music the band plays</li>
						</ul>
					</div>
				</a>
			</div>
			
			<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
				<a href="documents.php" class="kcb-a">
					<div class="panel price panel-grey">
						<div class="panel-heading arrow_box text-center">
						<h3>Documents</h3>
						</div>
						<ul class="list-group list-group-flush text-center">
							<li class="list-group-item"><i class="icon-ok text-muted"></i> View all the documents related to the operations of the band</li>
						</ul>
					</div>
				</a>
			</div>
		</div>
		<?php require '../includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require '../includes/common_js.php'; ?>
  </body>
</html>

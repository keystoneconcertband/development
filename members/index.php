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
					<h2>Welcome <?php echo $_SESSION["email"]?>!</h2>
				</div>
				Welcome to the KCB Member section! With this site you can update your information, view the other band member's
				information, and listen to music the band has played.<br>
			</div>
		</div>
		<div class="row">
		  <div class="col-sm-6 col-md-4">
		  <a href="#" class="kcb-a">
		    <div class="thumbnail" style="background-color: #f5a651;">
		      <div class="caption">
		        <h3>My Info</h3>
		        <p>Update your information stored with the KCB</p>
		      </div>
		    </div>
		  </a>
		  </div>
		  <div class="col-sm-6 col-md-4">
		  <a href="#" class="kcb-a">
		    <div class="thumbnail" style="background-color: #2998dc;">
		      <div class="caption">
		        <h3>Members</h3>
		        <p>View the other band member's information</p>
		      </div>
		    </div>
		  </a>
		  </div>
		  <div class="col-sm-6 col-md-4">
		    <a href="#" class="kcb-a">
		    <div class="thumbnail" style="background-color: #16812a">
		      <div class="caption">
		        <h3>Music</h3>
		        <p>View and post links to the music the band plays</p>
		      </div>
		    </div>
		    </a>
		  </div>
		</div>
		</div>
		<?php require '../includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require '../includes/common_js.php'; ?>
  </body>
</html>

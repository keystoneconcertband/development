<?
	include_once('../includes/class/member.class.php'); 
	new Member(true);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
	<? require '../includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band member area">

    <title>Member Area - Keystone Concert Band</title>

	<? require '../includes/common_css.php'; ?>
	
	<!--
		Not sure what this was for? Removed 6/8/17
	<style type="text/css">
		.row .col-lg-12 ul li {
			margin-bottom: 10px;
		}
		</style>
	-->
  </head>

  <body>

	<? require '../includes/nav.php'; ?>
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
					<h2>Welcome <?=$_SESSION["email"]?>!</h2>
				</div>
				Welcome to the KCB Member section! With this site you can update your information, view the other band member's
				information, and listen to music the band has played.<br>
			</div>
		</div>
		<div class="row">
		  <div class="col-sm-6 col-md-4">
		    <div class="thumbnail">
		      <img src="../images/info.png" alt="...">
		      <div class="caption">
		        <h3>My Info</h3>
		        <p>Update your information stored with the KCB</p>
		        <p><a href="#" class="btn btn-default" role="button">Update</a></p>
		      </div>
		    </div>
		  </div>
		  <div class="col-sm-6 col-md-4">
		    <div class="thumbnail">
		      <img src="../images/group-of-people.png" alt="Member Image">
		      <div class="caption">
		        <h3>Members</h3>
		        <p>View the other band member's information</p>
		        <p><a href="#" class="btn btn-default" role="button">View</a></p>
		      </div>
		    </div>
		  </div>
		  <div class="col-sm-6 col-md-4">
		    <div class="thumbnail">
		      <img src="../images/logo_concert.jpg" alt="Music Image" style="max-height: 150px;">
		      <div class="caption">
		        <h3>Music</h3>
		        <p>View and post links to the music the band plays</p>
		        <p><a href="#" class="btn btn-default" role="button">Listen</a></p>
		      </div>
		    </div>
		  </div>
		</div>
		</div>
		<? require '../includes/footer.php'; ?>
	</div> <!-- /container -->

	<? require '../includes/common_js.php'; ?>
  </body>
</html>

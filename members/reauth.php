<!DOCTYPE html>
<html lang="en">
  <head>
	<?php require '../includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band member area">

    <title>Member Area - Keystone Concert Band</title>

	<?php require '../includes/common_css.php'; ?>
	
	<style type="text/css">
		.row .col-lg-12 ul li {
			margin-bottom: 10px;
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
					<h2>Oops!</h2>
				</div>
				You need to be logged in to view this page. You will be redirected to the login page momentarily (or just <a href="../members.php">click here</a> to be taken there immediately).<br>
			</div>
		</div>
		<?php require '../includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require '../includes/common_js.php'; ?>
	<script type="text/javascript">
		var redirectTimeoutId = window.setTimeout(redirectToHomepage, 10000);
		
		function redirectToHomepage() {
			window.location.href = '../members.php';
		}
	</script>
  </body>
</html>

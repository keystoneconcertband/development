<?php
include_once '../includes/class/protectedAdmin.class.php';
new protectedAdmin();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
	<?php require '../includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band member area">

    <title>KCB Members - Keystone Concert Band</title>

	<?php require '../includes/common_css.php'; ?>
	<link rel="stylesheet" href="/css/member.css">
	<link rel="stylesheet" href="/3rd-party/datatables-1.10.21/datatables.min.css"/>
  </head>

  <body>

	<?php require '../includes/nav.php'; ?>
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
		<div class="row mb-3">
			<div class="col-lg-12">
				<div class="page-header">
					<h2>Text Message Members</h2>
				</div>
				<div class="alert alert-info" role="alert">
					Send an email to <a href="mailto:text@keystoneconcertband.com">text@keystoneconcertband.com</a>.
					<br /><br />
					<strong>Limited to Jonathan, Donna, Vicki and Chris</strong>
				</div>
			</div>
		</div>
		<?php require '../includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require '../includes/common_js.php'; ?>
	<script type="text/javascript" src="/3rd-party/datatables-1.10.21/datatables.min.js"></script>
	<script type="text/javascript" src="/3rd-party/bootstrap-validator-0.11.9/js/bootstrap-validator-0.11.9.min.js"></script>
  </body>
</html>
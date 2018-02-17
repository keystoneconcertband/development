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
    <link rel="stylesheet" href="/css/font-awesome.min.css"/>
    <link rel="stylesheet" href="/css/checkboxes.min.css"/>
	<link rel="stylesheet" type="text/css" href="/dataTables-1.10.15/datatables.min.css"/>
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
		<div class="row" style="margin-bottom: 20px;">
			<div class="col-lg-12">
				<div class="page-header">
					<h2>Login Satistics</h2>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<table id="kcbLogonTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
							<thead>
								<th>Login ID</th>
								<th>Status</th>
								<th>Date/Time</th>
							</thead>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php require '../includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require '../includes/common_js.php'; ?>
	<script type="text/javascript">
		var office = "<?=$_SESSION['office']?>";
	</script>
	<script type="text/javascript" src="/dataTables-1.10.15/datatables.min.js"></script>
	<script type="text/javascript" src="/kcb-js/loginStats.js"></script>
  </body>
</html>
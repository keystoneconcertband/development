<?php
include_once '../includes/class/protectedAdmin.class.php';
require_once '../includes/asset.php';
new protectedAdmin();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
	<?php require '../includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band member area">

    <title>KCB Members - Keystone Concert Band</title>

	<?php require '../includes/common_css.php'; ?>
	<link rel="stylesheet" href="<?= asset('/css/member.css') ?>">
  </head>

  <body>

	<?php require '../includes/nav.php'; ?>
	<div class="container">
		<div class="row" style="margin-bottom: 20px;">
			<div class="col-lg-12">
				<div class="mb-4 pb-2 border-bottom">
					<h2>Login Statistics</h2>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<table id="kcbLogonTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
							<thead>
								<tr>
									<th>Login ID</th>
									<th>Status</th>
									<th>Date/Time</th>
								</tr>
							</thead>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php require '../includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require '../includes/common_js.php'; ?>
	<?php require '../includes/common_datatables.php'; ?>
	<script type="text/javascript">
		var accountType = "<?=$_SESSION['accountType']?>";
	</script>
	<script type="text/javascript" src="<?=asset('/kcb-js/loginStats.js')?>" ></script>
  </body>
</html>
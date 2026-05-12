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
	<link rel="stylesheet" href="<?= asset('/css/member.css') ?>">
  </head>

  <body>

	<?php require '../includes/nav.php'; ?>
	<div class="container">
		<div class="row" style="margin-bottom: 20px;">
			<div class="col-lg-12">
				<div class="mb-4 pb-2 border-bottom">
					<h2>Text Message Members</h2>
				</div>
				<div class="p-4 mb-4 bg-light rounded-3">
					Messaging is now located at <a href="https://www.callmultiplier.com/login.php">Call Multiplier</a>.
					Contact Jonathan if you require access.
				</div>
			</div>
		</div>
		<?php require '../includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require '../includes/common_js.php'; ?>
  </body>
</html>
<?php
require_once __DIR__ . '/../../Shared/Classes/protectedAdmin.class.php';
new protectedAdmin();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
	<?php require __DIR__ . '/../../../templates/partials/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band member area">

    <title>KCB Members - Keystone Concert Band</title>

	<?php require __DIR__ . '/../../../templates/partials/common_css.php'; ?>
	<link rel="stylesheet" href="<?= asset('/assets/css/member.css') ?>">
  </head>

  <body>

	<?php require __DIR__ . '/../../../templates/partials/nav.php'; ?>
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
		<?php require __DIR__ . '/../../../templates/partials/footer.php'; ?>
	</div> <!-- /container -->

	<?php require __DIR__ . '/../../../templates/partials/common_js.php'; ?>
  </body>
</html>

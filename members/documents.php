<?php
include_once '../includes/class/protectedMember.class.php';
new ProtectedMember();
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
					<h2>Documents</h2>
				</div>
				<div class="row mb-3">
					<div class="alert alert-info">
					<span class="fa fa-info-circle"></span> <strong>Please review both the
					  <a href="documents_perm/KCB_Bylaws_2021.pdf" target="_blank">KCB By Laws</a>, and the 
					  <a href="documents_perm/KCB_Policy_March_2023.pdf" target="_blank">Band Policies</a>.
						</strong>
					</div>
				</div>
				<div class="ratio ratio-16x9">
				  <iframe src="tinyfilemanager.php" allowfullscreen></iframe>
				</div>
			</div>
		</div>
		<?php require '../includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require '../includes/common_js.php'; ?>

  </body>
</html>

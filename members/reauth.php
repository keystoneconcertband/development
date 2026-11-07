<?php
$redirect = isset($_GET['redirect']) ? trim($_GET['redirect']) : '';
$loginUrl = '/roster.php?login=1';
if (!empty($redirect) && str_starts_with($redirect, '/members/') && !str_starts_with($redirect, '//') && strpos($redirect, '\\') === false) {
    $loginUrl .= '&redirect=' . rawurlencode($redirect);
}
?>
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
		<div class="row" style="margin-bottom: 20px;">
			<div class="col-lg-12">
				<div class="mb-4 pb-2 border-bottom">
					<h2>Authentication Required</h2>
				</div>
				<div class="alert alert-info">
					You need to be logged in to view this page. Redirecting you to member login... (or <a href="<?= htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') ?>" class="alert-link">click here</a> to log in immediately).
				</div>
			</div>
		</div>
		<?php require '../includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require '../includes/common_js.php'; ?>
	<script type="text/javascript">
		const targetUrl = <?= json_encode($loginUrl) ?>;
		window.setTimeout(function() {
			window.location.href = targetUrl;
		}, 2500);
	</script>
  </body>
</html>

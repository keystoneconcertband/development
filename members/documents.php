<?php
include_once '../includes/class/protectedMember.class.php';
new ProtectedMember();
$membersPathURL = 'https://' . $_SERVER['HTTP_HOST'] . "/members"
?>

<!DOCTYPE html>
<html lang="en">
  <head>
	<?php require '../includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band member area">

    <title>KCB Members - Keystone Concert Band</title>

	<?php require '../includes/common_css.php'; ?>
	<link rel="stylesheet" href="/css/member.css">
    <link rel="stylesheet" href="/css/checkboxes.min.css"/>
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
		<div class="row" style="margin-bottom: 20px;">
			<div class="col-lg-12">
				<div class="page-header">
					<h2>Documents</h2>
				</div>
				<div class="row form-group">
					<div class="alert alert-info">
					<span class="glyphicon glyphicon-info-sign"></span> <strong>Please review both the
					  <a href="documents_perm/KCB_Bylaws_2021.pdf" target="_blank" style="text-decoration: underline; color: yellow">KCB By Laws</a>, and the 
					  <a href="documents_perm/KCB_Policy_June_2022.pdf" target="_blank" style="text-decoration: underline; color: yellow">Band Policies</a>.
						</strong>
					</div>
				</div>
				<div class="embed-responsive embed-responsive-16by9">
				  <iframe class="embed-responsive-item" src="tinyfilemanager.php" allowfullscreen></iframe>
				</div>
			</div>
		</div>
		<?php require '../includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require '../includes/common_js.php'; ?>

  </body>
</html>

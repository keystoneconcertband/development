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
					<h2>Text Message Members</h2>
				</div>
				<div class="well bs-component">
					<form class="form-horizontal" id="frmMessage" data-toggle="validator">
						<fieldset>
						    <legend>Text Message the band</legend>
						    The maximum length of a text message that you can send is 800 characters. 
						    However, if you send more than 112 characters then your message will be split
						    into multiple messages due to the FROM and SUBJECT lines.
						    <div class="form-group">
						      <label for="message" class="col-lg-2 control-label">Message</label>
						      <div class="col-lg-10">
						        <textarea class="form-control" rows="3" id="message" name="message" maxlength="800"></textarea>
						        <div id="messageCount"></div>
								<div class="help-block with-errors"></div>
						      </div>
						    </div>
						</fieldset>
						<div class="form-group">
						  <div class="col-lg-12">
						    <button type="submit" class="btn btn-primary">Send</button>
							<div id="msgSubmit" class="h4 hidden"></div>
						  </div>
						</div>
					</form>
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
	<script type="text/javascript" src="/bootstrap-validator-0.11.9/js/bootstrap-validator-0.11.9.min.js"></script>
	<script type="text/javascript" src="/kcb-js/messageMembers.js"></script>
  </body>
</html>
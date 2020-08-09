<?php
include_once '../includes/class/protectedMusic.class.php';
new ProtectedMusic();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
	<?php require '../includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band member area">

    <title>Homepage Messages - Keystone Concert Band</title>

	<?php require '../includes/common_css.php'; ?>
	<link rel="stylesheet" href="/css/member.css">
    <link rel="stylesheet" href="/css/checkboxes.min.css" />
	<link rel="stylesheet" href="/3rd-party/bootstrap-timepicker-4.17.47/bootstrap-datetimepicker.min.css" />
	<link rel="stylesheet" href="/3rd-party/dataTables-1.10.15/datatables.min.css"/>
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
					<h2>Homepage Messages</h2>
				</div>

				<div class="row form-group">
					<div class="col-sm-3">
						<div>
							<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_add_edit">Add New</button>
						</div>
					</div>
					<div class="col-sm-9">
						<div id="msgMainHeader" class="h4 hidden"></div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<table id="kcbMessageTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
							<thead>
								<th></th>
								<th>Title</th>
								<th>Message</th>
								<th>Importance</th>
								<th>Start Date</th>
								<th>End Date</th>
							</thead>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="modal_add_edit" role="dialog">
			<form id="form_message" data-toggle="validator">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title">Homepage Message</h5>
						</div>
						<div class="modal-body form-horizontal">
							<div class="form-group">
								<div class="col-sm-12">
									<label for="title" class="control-label">Title</label>
									<input type="text" class="form-control" name="title" id="title" placeholder="Title of message" value="" required="true" maxlength="100">
									<div class="help-block with-errors"></div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-12">
									<label for="message" class="control-label">Message</label>
									<textarea class="form-control" id="message" name="message" placeholder="Message to display" maxlength="2000" rows="3" required="true"></textarea>
									<div class="help-block with-errors"></div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-12">
									<label for="message_type" class="control-label">Message Type</label>
									<select class="form-control" name="message_type" id="message_type">
										<option value="Regular">Regular</option>
										<option value="Important">Important</option>
							        </select>
									<div class="help-block with-errors"></div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-12">
									<label for="start_dt" class="control-label">Start Date</label>
									<div class="input-group date" id="dpStartDt">
										<input type="text" class="form-control" name="start_dt" id="start_dt" placeholder="First day to show message" required="true" data-error="Date is required." onblur="checkDates(this.value)">
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
									</div>
									<div class="help-block with-errors"></div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-12">
									<label for="end_dt" class="control-label">End Date</label>
									<div class="input-group date" id="dpEndDt">
										<input type="text" class="form-control" name="end_dt" id="end_dt" placeholder="Last day to show message" required="true" data-error="Date is required." onblur="checkDates(this.value)">
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
									</div>
									<div class="help-block with-errors"></div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<input type="hidden" id="uid" name="uid" value="" />
							<button type="submit" class="btn btn-primary">Save</button>
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
							<div id="msgSubmit" class="h4 hidden"></div>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php require '../includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require '../includes/common_js.php'; ?>
	<script  type="text/javascript" src="/3rd-party/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
	<script type="text/javascript" src="/3rd-party/dataTables-1.10.15/datatables.min.js"></script>
	<script type="text/javascript" src="/3rd-party/bootstrap-validator-0.11.9/js/bootstrap-validator-0.11.9.min.js"></script>
	<script type="text/javascript" src="/3rd-party/moment-2.19.2/moment.min.js"></script>
	<script type="text/javascript" src="/3rd-party/bootstrap-timepicker-4.17.47/bootstrap-datetimepicker.min.js"></script>
	<script type="text/javascript" src="/kcb-js/homepageMessage.js"></script>
  </body>
</html>

<?php
include_once '../includes/class/protectedAdmin.class.php';
new protectedAdmin();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
	<?php require '../includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band member area">

    <title>Homepage Messages - Keystone Concert Band</title>

	<?php require '../includes/common_css.php'; ?>
	<link rel="stylesheet" href="/css/member.css">
	<link rel="stylesheet" href="/3rd-party/bootstrap-timepicker-4.17.47/bootstrap-datetimepicker.min.css" />
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
					<h2>Homepage Messages</h2>
				</div>

				<div class="row mb-3">
					<div class="col-sm-3">
						<div>
							<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_add_edit">Add New</button>
						</div>
					</div>
					<div class="col-sm-9">
						<div id="msgMainHeader" class="h4 d-none"></div>
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
		<div class="modal fade" id="modal_add_edit" tabindex="-1" role="dialog" aria-labelledby="modal_add_editLabel" aria-hidden="true">
			<form id="form_message" data-bs-toggle="validator">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="modal_add_editLabel">Homepage Message</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<div class="mb-3">
								<label for="title" class="form-label">Title</label>
								<input type="text" class="form-control" name="title" id="title" placeholder="Title of message" value="" required="true" maxlength="100">
								<div class="form-text"></div>
							</div>
							<div class="mb-3">
								<label for="message" class="form-label">Message</label>
								<textarea class="form-control" id="message" name="message" placeholder="Message to display" maxlength="2000" rows="3" required="true"></textarea>
								<div class="form-text"></div>
							</div>
							<div class="mb-3">
								<label for="importance" class="form-label">Importance</label>
								<select class="form-select" id="importance" name="importance">
									<option value="low">Low</option>
									<option value="medium" selected>Medium</option>
									<option value="high">High</option>
								</select>
							</div>
							<div class="mb-3">
								<label for="startDate" class="form-label">Start Date</label>
								<input type="date" class="form-control" id="startDate" name="startDate">
								<div class="form-text"></div>
							</div>
							<div class="mb-3">
								<label for="endDate" class="form-label">End Date</label>
								<input type="date" class="form-control" id="endDate" name="endDate">
								<div class="form-text"></div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
							<button type="submit" class="btn btn-primary">Save Message</button>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php require '../includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require '../includes/common_js.php'; ?>
	<script type="text/javascript" src="/3rd-party/datatables-1.10.21/datatables.min.js"></script>
	<script type="text/javascript" src="/3rd-party/bootstrap-validator-0.11.9/js/bootstrap-validator-0.11.9.min.js"></script>
  </body>
</html>
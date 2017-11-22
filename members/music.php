<?php
include_once '../includes/class/protectedMember.class.php';
new ProtectedMember();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
	<?php require '../includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band member area">

    <title>Music - Keystone Concert Band</title>

	<?php require '../includes/common_css.php'; ?>
    <link rel="stylesheet" href="/css/font-awesome.min.css" />
    <link rel="stylesheet" href="/css/checkboxes.min.css" />
	<link rel="stylesheet" href="/bootstrap-timepicker-4.17.47/bootstrap-datetimepicker.min.css" />
	<link rel="stylesheet" href="/dataTables-1.10.15/datatables.min.css"/>
  </head>

  <body>

	<?php require '../includes/nav.php'; ?>
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="bs-component">
					<div class="jumbotron">
						<h1>KCB Members</h1>
					</div>
				</div>
			</div>
		</div>
		<div class="row" style="margin-bottom: 20px;">
			<div class="col-lg-12">
				<div class="page-header">
					<h2>Music</h2>
				</div>
				<div class="row form-group">
					<div class="col-lg-12">
						<div>
							<button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal_add_edit">Add Music</button>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<table id="kcbMusicTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
							<thead>
								<th>Title</th>
								<th>Notes</th>
								<th>Music Link</th>
								<th>Last Played</th>
								<th>Number of Plays</th>
							</thead>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="modal_add_edit">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Add Music</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<form id="form_music" data-toggle="validator">
							<div class="form-group">
								<div class="col-sm-12">
									<label for="title" class="control-label">Title*</label>
									<input type="text" class="form-control" name="title" id="title" placeholder="Title" value="" required="true" maxlength="255" data-error="Title is required.">
									<div class="help-block with-errors"></div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-12">
									<label for="title" class="control-label">Notes</label>
									<input type="text" class="form-control" name="notes" id="notes" placeholder="Notes" value="" maxlength="2000">
									<div class="help-block with-errors"></div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-12">
									<label for="title" class="control-label">Link to music</label>
									<input type="text" class="form-control" name="music_link" id="music_link" placeholder="Music Link" value="" maxlength="2000">
									<div class="help-block with-errors"></div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-12">
									<label for="title" class="control-label">Last Played</label>
									<div class="input-group date" id="dpLastPlayed">
										<input type="text" class="form-control" name="last_played" id="last_played" placeholder="Last Played" value="">
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
										<div class="help-block with-errors"></div>
									</div>
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-primary">Save changes</button>
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		<?php require '../includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require '../includes/common_js.php'; ?>
	<script type="text/javascript" src="/dataTables-1.10.15/datatables.min.js"></script>
	<script type="text/javascript" src="/bootstrap-validator-0.11.9/js/bootstrap-validator-0.11.9.min.js"></script>
	<script type="text/javascript" src="/moment-2.19.2/moment.min.js"></script>
	<script type="text/javascript" src="/bootstrap-timepicker-4.17.47/bootstrap-datetimepicker.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#dpLastPlayed').datetimepicker( {
				format: 'L',
				maxDate: moment().add(7, 'days'),
				showTodayButton: true,
				showClear: true,
				showClose: true
			});
			$("#kcbMusicTable").validator();
		    $('#kcbMusicTable').DataTable( {
			    responsive: true,
				stateSave: true,
				"order": [[0, "asc" ]],
			    "ajax": {
				    "url":"musicServer.php",
					"dataSrc": ""
				},
				"columns": [
		            { "data": "title" },
		            { "data": "notes" },
					{ data: null, render: function ( data, type, row ) {
						if(data.music_link) {
							return '<a href="'+data.music_link+'" target="_blank">'+data.music_link+'</a><br />'
						}
						else {
							return "";
						}
		              } 
		            },
		            { "data": "last_played" },
		            { "data": "number_plays" }
		        ]
	        });
		});
	</script>  
  </body>
</html>

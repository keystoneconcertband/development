<?php
include_once '../includes/class/protectedMusic.class.php';
new ProtectedMusic();
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
				<? if(isset($_SESSION['office'])) { ?>
				<div class="row form-group">
					<div class="col-sm-2">
						<div>
							<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_add_edit">Add Music</button>
						</div>
					</div>
					<div class="col-sm-10">
						<div id="msgMainHeader" class="h4 hidden"></div>
					</div>
				</div>
				<? } ?>
				<div class="row">
					<div class="col-lg-12">
						<table id="kcbMusicTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
							<thead>
								<th></th>
								<th>Title</th>
								<th>Notes</th>
								<th>Music Link</th>
								<th>Genre</th>
								<th>Last Played</th>
								<th>Number of Plays</th>
							</thead>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="modal_add_edit" role="dialog">
			<form id="form_music" data-toggle="validator">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title">Add Music</h5>
						</div>
						<div class="modal-body form-horizontal">
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
									<textarea class="form-control" id="notes" name="notes" placeholder="Notes" maxlength="2000" rows="3"></textarea>
									<div class="help-block with-errors"></div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-12">
									<label for="title" class="control-label">Genre*</label>
									<select class="form-control" name="genre" id="genre" required="true">
							            <option value="0" selected="Selected">Select type</option>
							        </select>
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
									</div>
									<div class="help-block with-errors"></div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<input type="hidden" id="uid" name="uid" value="" />
							<div id="msgSubmit" class="h4 hidden"></div>
							<button type="submit" class="btn btn-primary">Save changes</button>
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
	<script type="text/javascript">
		var office = "<?=$_SESSION['office']?>";
	</script>
	<script type="text/javascript" src="/dataTables-1.10.15/datatables.min.js"></script>
	<script type="text/javascript" src="/bootstrap-validator-0.11.9/js/bootstrap-validator-0.11.9.min.js"></script>
	<script type="text/javascript" src="/moment-2.19.2/moment.min.js"></script>
	<script type="text/javascript" src="/bootstrap-timepicker-4.17.47/bootstrap-datetimepicker.min.js"></script>
	<script type="text/javascript" src="/kcb-js/music.js"></script>
  </body>
</html>

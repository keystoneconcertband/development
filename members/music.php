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
	<link rel="stylesheet" href="/css/member.css">
    <link rel="stylesheet" href="/css/checkboxes.min.css" />
	<link rel="stylesheet" href="/3rd-party/bootstrap-timepicker-4.17.47/bootstrap-datetimepicker.min.css" />
	<link rel="stylesheet" href="/3rd-party/datatables-1.10.21/datatables.min.css"/>
	<link rel="stylesheet" href="/3rd-party/jquery-ui-1.12.1.custom/jquery-ui.min.css">
	<style>
		.ui-autocomplete-loading {
			background: white url("/images/ui-anim_basic_16x16.gif") right center no-repeat;
		}
		.ui-autocomplete {
		    z-index: 5000;
		}
	</style>
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
					<h2>Music</h2>
							The number of plays starts with the first 2018 concert.
				</div>

				<?php if($_SESSION['accountType'] === 1 || $_SESSION['accountType'] === 2) { ?>
				<div class="row form-group row">
					<div class="col-sm-3">
						<div>
							<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_add_edit">Add Music</button>
							<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_concert">Concert</button>
						</div>
					</div>
					<div class="col-sm-9">
						<div id="msgMainHeader" class="h4 hidden"></div>
					</div>
				</div>
				<?php } ?>
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
		<div class="modal fade" id="modal_concert" role="dialog">
			<form id="form_concert" data-bs-toggle="validator">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title">Concert</h5>
						</div>
						<div class="modal-body form-horizontal">
							<div class="form-group row">
								<div class="col-sm-12">
									<label for="dpConcert" class="control-label">Date of concert*</label>
									<div class="input-group date" id="dpConcert">
										<input type="text" class="form-control" name="concert_date" id="concert_date" placeholder="Date of Concert" required="true" data-error="Date is required.">
										<span class="input-group-text">
											<span class="glyphicon glyphicon-calendar"></span>
										</span>
									</div>
									<div class="help-block with-errors"></div>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-sm-12">
									<label for="concert_title" class="control-label">Title</label>
									<input type="text" class="form-control" name="concert_title" id="concert_title" placeholder="Title" value="" maxlength="255">
									<div class="help-block with-errors"></div>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-sm-12">
									<h4>Concert Program:</h4>
									<ul id="concert_program_list">
										<li id="concert_program_empty">Empty</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<input type="hidden" id="concert_uids" name="concert_uids" value="" />
							<div id="msgSubmit" class="h4 hidden"></div>
							<button type="submit" class="btn btn-primary">Save changes</button>
							<button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
							<div id="msgSubmit" class="h4 hidden"></div>
						</div>
					</div>
				</div>
			</form>
		</div>
		<div class="modal fade" id="modal_add_edit" role="dialog">
			<form id="form_music" data-bs-toggle="validator">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title">Add Music</h5>
						</div>
						<div class="modal-body form-horizontal">
							<div class="form-group row">
								<div class="col-sm-12">
									<label for="title" class="control-label">Title*</label>
									<input type="text" class="form-control" name="title" id="title" placeholder="Title" value="" required="true" maxlength="255" data-error="Title is required.">
									<div class="help-block with-errors"></div>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-sm-12">
									<label for="notes" class="control-label">Notes</label>
									<textarea class="form-control" id="notes" name="notes" placeholder="Notes" maxlength="2000" rows="3"></textarea>
									<div class="help-block with-errors"></div>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-sm-12">
									<label for="genre" class="control-label">Genre*</label>
									<select class="form-control" name="genre" id="genre" required="true">
							            <option value="0" selected="Selected">Select type</option>
							        </select>
									<div class="help-block with-errors"></div>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-sm-12">
									<label for="music_link" class="control-label">Link to music</label>
									<input type="text" class="form-control" name="music_link" id="music_link" placeholder="Music Link" value="" maxlength="2000">
									<div class="help-block with-errors"></div>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-sm-12">
									<label for="dpLastPlayed" class="control-label">Last Played</label>
									<div class="input-group date" id="dpLastPlayed">
										<input type="text" class="form-control" name="last_played" id="last_played" placeholder="Last Played" value="">
										<span class="input-group-text">
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
							<button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
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
		var accountType = "<?=$_SESSION['accountType']?>";
	</script>
	<script  type="text/javascript" src="/3rd-party/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
	<script type="text/javascript" src="/3rd-party/datatables-1.10.21/datatables.min.js"></script>
	<script type="text/javascript" src="/3rd-party/bootstrap-validator-0.11.9/js/bootstrap-validator-0.11.9.min.js"></script>
	<script type="text/javascript" src="/3rd-party/moment-2.27.0/moment.min.js"></script>
	<script type="text/javascript" src="/3rd-party/bootstrap-timepicker-4.17.47/bootstrap-datetimepicker.min.js"></script>
	<script type="text/javascript" src="/kcb-js/music-20230825.js"></script>
  </body>
</html>

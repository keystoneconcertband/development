<?php
include_once '../includes/class/protectedMusic.class.php';
new ProtectedMusic();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
	<?php require '../includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band member area - Music">

    <title>Music - Keystone Concert Band Members</title>

	<?php require '../includes/common_css.php'; ?>
	<link rel="stylesheet" href="/css/member.css">
	<link rel="stylesheet" href="/3rd-party/datatables-1.10.21/datatables.min.css"/>
	<style>
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
				<div class="row mb-3">
					<div class="col-sm-3">
						<div>
							<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_add_edit">Add Music</button>
							<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_concert">Concert</button>
						</div>
					</div>
					<div class="col-sm-9">
						<div id="msgMainHeader" class="h4 d-none"></div>
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
		<div class="modal fade" id="modal_concert" tabindex="-1" role="dialog" aria-labelledby="modal_concertLabel" aria-hidden="true">
			<form id="form_concert" data-bs-toggle="validator">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="modal_concertLabel">Concert</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<div class="mb-3">
								<label for="dpConcert" class="form-label">Date of concert*</label>
								<div class="input-group date" id="dpConcert">
									<input type="text" class="form-control" name="concert_date" id="concert_date" placeholder="Date of Concert" required="true" data-error="Date is required.">
									<span class="input-group-text">
										<span class="bi bi-calendar"></span>
									</span>
								</div>
								<div class="form-text"></div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
							<button type="submit" class="btn btn-primary">Save Concert</button>
						</div>
					</div>
				</div>
			</form>
		</div>
		<div class="modal fade" id="modal_add_edit" tabindex="-1" role="dialog" aria-labelledby="modal_add_editLabel" aria-hidden="true">
			<form id="form_music" data-bs-toggle="validator">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="modal_add_editLabel">Add/Edit Music</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<div class="mb-3">
								<label for="txtTitle" class="form-label">Title*</label>
								<input type="text" class="form-control" name="title" id="txtTitle" placeholder="Music title" required="true">
								<div class="form-text"></div>
							</div>
							<div class="mb-3">
								<label for="txtGenre" class="form-label">Genre</label>
								<input type="text" class="form-control" name="genre" id="txtGenre" placeholder="Music genre">
								<div class="form-text"></div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
							<button type="submit" class="btn btn-primary">Save Music</button>
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
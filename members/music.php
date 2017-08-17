<?php
include_once '../includes/class/updateMember.class.php';
$mbr = new UpdateMember();
$member = $mbr->getMember($_SESSION['email']);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
	<?php require '../includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band member area">

    <title>My Personal Information - Keystone Concert Band</title>

	<?php require '../includes/common_css.php'; ?>
    <link rel="stylesheet" href="/css/font-awesome.min.css"/>
    <link rel="stylesheet" href="/css/checkboxes.min.css"/>
    <style type="text/css">
	    .extraEmailTemplate {
		    display:none;
		}
	</style>
	<link rel="stylesheet" type="text/css" href="/dataTables-1.10.15/datatables.min.css"/>
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
				<table id="kcbMusicTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
					<thead>
						<th></th>
						<th>Title</th>
						<th>Music Link</th>
						<th>Last Played</th>
						<th>Last Mod By</th>
						<th>Last Mod Dt</th>
					</thead>
					<tbody>
						<tr>
							<td><button name="Select" id="select0">Modify</button>
								<button name="Select" id="play0">Play History</button></td>
							<td>From Tropic to Tropic</td>
							<td><a href="youtube.com/abc">www.youtube.com/abc</a></td>
							<td>8/12/2017</td>
							<td>Gillette</td>
							<td>8/16/2017 7:30 PM</td>
						</tr>
						<tr>
							<td><button name="Select" id="select0">Modify</button>
								<button name="Select" id="play0">Play History</button></td>
							<td>Music for a Carnival</td>
							<td><a href="youtube.com/abc">www.youtube.com/567</a></td>
							<td>8/12/2017</td>
							<td>Gillette</td>
							<td>8/16/2017 7:31 PM</td>
						</tr>
						<tr>
							<td><button name="Select" id="select0">Modify</button>
								<button name="Select" id="play0">Play History</button></td>
							<td>Galop to End all Galops</td>
							<td><a href="youtube.com/abc">www.youtube.com/456</a></td>
							<td>8/12/2017</td>
							<td>Gillette</td>
							<td>8/16/2017 7:31 PM</td>
						</tr>
						<tr>
							<td><button name="Select" id="select0">Modify</button>
								<button name="Select" id="play0">Play History</button></td>
							<td>American Patrol</td>
							<td><a href="youtube.com/abc">www.youtube.com/345</a></td>
							<td>8/12/2017</td>
							<td>Gillette</td>
							<td>8/16/2017 7:32 PM</td>
						</tr>
						<tr>
							<td><button name="Select" id="select0">Modify</button>
								<button name="Select" id="play0">Play History</button></td>
							<td>Blue Tango</td>
							<td><a href="youtube.com/abc">www.youtube.com/234</a></td>
							<td>8/12/2017</td>
							<td>Gillette</td>
							<td>8/16/2017 7:33 PM</td>
						</tr>
						<tr>
							<td><button name="Select" id="select0">Modify</button>
								<button name="Select" id="play0">Play History</button></td>
							<td>Klaxon</td>
							<td><a href="youtube.com/abc">www.youtube.com/123</a></td>
							<td>8/12/2017</td>
							<td>Gillette</td>
							<td>8/16/2017 7:34 PM</td>
						</tr>
						<tr>
							<td><button name="Select" id="select0">Modify</button>
								<button name="Select" id="play0">Play History</button></td>
							<td>Belle of the Ball </td>
							<td><a href="youtube.com/abc">www.youtube.com/123</a></td>
							<td>8/12/2017</td>
							<td>Gillette</td>
							<td>8/16/2017 7:34 PM</td>
						</tr>
						<tr>
							<td><button name="Select" id="select0">Modify</button>
								<button name="Select" id="play0">Play History</button></td>
							<td>Colonel Bogey</td>
							<td><a href="youtube.com/abc">www.youtube.com/123</a></td>
							<td>8/12/2017</td>
							<td>Gillette</td>
							<td>8/16/2017 7:34 PM</td>
						</tr>
						<tr>
							<td><button name="Select" id="select0">Modify</button>
								<button name="Select" id="play0">Play History</button></td>
							<td>Armed Forces Salute</td>
							<td><a href="youtube.com/abc">www.youtube.com/123</a></td>
							<td>8/12/2017</td>
							<td>Gillette</td>
							<td>8/16/2017 7:34 PM</td>
						</tr>
						<tr>
							<td><button name="Select" id="select0">Modify</button>
								<button name="Select" id="play0">Play History</button></td>
							<td>America, the Beautiful</td>
							<td><a href="youtube.com/abc">www.youtube.com/123</a></td>
							<td>8/12/2017</td>
							<td>Gillette</td>
							<td>8/16/2017 7:34 PM</td>
						</tr>
						<tr>
							<td><button name="Select" id="select0">Modify</button>
								<button name="Select" id="play0">Play History</button></td>
							<td>Stars and Stripes</td>
							<td><a href="youtube.com/abc">www.youtube.com/123</a></td>
							<td>8/12/2017</td>
							<td>Gillette</td>
							<td>8/16/2017 7:34 PM</td>
						</tr>
						<tr>
							<td><button name="Select" id="select0">Modify</button>
								<button name="Select" id="play0">Play History</button></td>
							<td>Beguine for Band</td>
							<td><a href="youtube.com/abc">www.youtube.com/123</a></td>
							<td>7/23/2017</td>
							<td>Gillette</td>
							<td>8/16/2017 7:34 PM</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<?php require '../includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require '../includes/common_js.php'; ?>
	<script type="text/javascript" src="/dataTables-1.10.15/datatables.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
		    $('#kcbMusicTable').DataTable( {
			    responsive: true
			});
		});
	</script>
  </body>
</html>

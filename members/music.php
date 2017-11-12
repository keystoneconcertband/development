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
						<th>Title</th>
						<th>Notes</th>
						<th>Music Link</th>
						<th>Last Played</th>
						<th>Number of Plays</th>
					</thead>
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
		            /*
					{ data: null, render: function ( data, type, row ) {
						if(data.instrument) {
		                	return data.instrument.replace(/,/g, '<br/>');
		                }
		                else {
			                return "";
		                }
		              } 
		            },
					{ data: null, render: function ( data, type, row ) {
						if(data.text) {
		                	return data.text.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
		                }
		                else {
			                return "";
		                }
		              } 
		            },
					{ data: null, render: function ( data, type, row ) {
						if(data.home_phone) {
		                	return data.home_phone.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
		                }
		                else {
			                return "";
		                }
		              } 
		            },
					{ data: null, render: function ( data, type, row ) {
					    if(data.address1) {
						    var addr = data.address1 + '<br />';
						    							
							if(data.address2) {
								addr += data.address2 + '<br />';
							}
							
							addr += data.city + ', ' + data.state + ' ' + data.zip;
							
							return addr;
					    }
		                else {
			                return "";
		                }
		              } 
		            },
		            { "data": "office" }*/
		        ]
	        });
		});
	</script>  </body>
</html>

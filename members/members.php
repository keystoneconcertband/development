<?php
include_once '../includes/class/protectedMember.class.php';
new ProtectedMember();
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
					<h2>Current Members</h2>
				</div>
				<table id="kcbMusicTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
					<thead>
						<th>Name</th>
						<th>Primary Email Address</th>
						<th>Cell Phone</th>
						<th>Home Phone</th>
						<th>Address 1</th>
						<th>Address 2</th>
						<th>City</th>
						<th>State</th>
						<th>Zip</th>
						<th>KCB Office Held</th>
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
				    "url":"/includes/getAllActiveMembers.php",
					"dataSrc": ""
				},
				//https://datatables.net/reference/option/rowCallback
				"rowCallback": function( row, data, index ) {
				    if ( data.email !== null ) {
				      $('td:eq(1)', row).html( '<a href="mailto:'+data.email+'">'+data.email+'</a>' );
				    }
				    if(data.text !== null) {
				      $('td:eq(2)', row).html( data.text.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3'));
				    }
				    if(data.home_phone !== null) {
				      $('td:eq(3)', row).html( data.home_phone.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3'));
				    }
				    
				    // Make Address field one thing
				  },
				"columns": [
		            { "data": "fullName" },
		            { "data": "email" },
		            { "data": "text" },
		            { "data": "home_phone" },
		            { "data": "address1" },
		            { "data": "address2" },
		            { "data": "city" },
		            { "data": "state" },
		            { "data": "zip" },
		            { "data": "office" }
		        ]
	        });
		});
	</script>
  </body>
</html>
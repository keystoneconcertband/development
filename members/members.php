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
					<h2>Members</h2>
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
				"columns": [
		            { "data": "fullName" },
		            { "data": "email",
			            "render": function (data,type,row,meta) {
				            if(data === null) {
					        	return "";
					        }
					        else {
					      		return '<a href="mailto:'+data+'">'+data+'</a>';
					      	}
				    	} 
				    },
		            { "data": "text",
			            "render": function(data,type,row,meta){
				            if(data !== null) {
					            data = data.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');					            
				            }
				            return data;
			            }
		            },
		            { "data": "home_phone",
			            "render": function(data,type,row,meta){
				            if(data !== null) {
					            data = data.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');					            
				            }
				            return data;
			            }			            
		            },
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
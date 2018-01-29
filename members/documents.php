<?php
include_once '../includes/class/protectedMember.class.php';
new ProtectedMember();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
	<?php require '../includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band member area">

    <title>KCB Members - Keystone Concert Band</title>

	<?php require '../includes/common_css.php'; ?>
    <link rel="stylesheet" href="/css/font-awesome.min.css"/>
    <link rel="stylesheet" href="/css/checkboxes.min.css"/>
	<link rel="stylesheet" href="/dataTables-1.10.15/datatables.min.css"/>
	<link rel="stylesheet" href="/jquery.fileupload-9.20.0/css/jquery.fileupload.css">
	<link rel="stylesheet" href="/jquery.fileupload-9.20.0/css/jquery.fileupload-ui.css">
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
					<h2>Documents</h2>
				</div>
				<? if(isset($_SESSION['office'])) { ?>
				<div class="row form-group">
					<div class="col-sm-2">
						<div>
							<button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal_upload">Add Document</button>
						</div>
					</div>
					<div class="col-sm-10">
						<div id="msgMainHeader" class="h4 hidden"></div>
					</div>
				</div>
				<? } ?>
				<div class="row">
					<div class="col-lg-12">
						<table id="kcbDocumentTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
							<thead>
								<th>File</th>
								<th>Date Uploaded</th>
							</thead>
						<?
							$dir = getcwd() . '/documents';
							$files = array_diff(scandir($dir), array('..', '.'));
							
							foreach($files as $file) {
								echo "<tr>";
								$stat = stat($dir . '/' . $file);
								echo "<td>" . $file . "</td>";
								echo "<td>" . @date('M d Y H:i',$stat['mtime']) . "</td>";
								echo "</tr>";
							}
						?>
						</table>						
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="modal_upload" role="dialog">
		    <form id="fileupload" action="documentsServer.php" method="POST" enctype="multipart/form-data">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title">Add Document</h5>
						</div>
						<div class="modal-body form-horizontal">
							<fieldset>
							    <legend>Upload Documents</legend>
							    <div class="form-group">
							      <div class="col-sm-12">
							        <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
							        <div class="row fileupload-buttonbar">
							            <div class="col-lg-7">
							                <!-- The fileinput-button span is used to style the file input field as button -->
							                <span class="btn btn-success fileinput-button">
							                    <i class="glyphicon glyphicon-plus"></i>
							                    <span>Add files...</span>
							                    <input type="file" name="files[]" multiple>
							                </span>
							                <button type="submit" class="btn btn-primary start">
							                    <i class="glyphicon glyphicon-upload"></i>
							                    <span>Start upload</span>
							                </button>
							                <button type="reset" class="btn btn-warning cancel">
							                    <i class="glyphicon glyphicon-ban-circle"></i>
							                    <span>Cancel upload</span>
							                </button>
							                <!-- The global file processing state -->
							                <span class="fileupload-process"></span>
							            </div>
							            <!-- The global progress state -->
							            <div class="col-lg-5 fileupload-progress fade">
							                <!-- The global progress bar -->
							                <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
							                    <div class="progress-bar progress-bar-success" style="width:0%;"></div>
							                </div>
							                <!-- The extended global progress state -->
							                <div class="progress-extended">&nbsp;</div>
							            </div>
							        </div>
							        <!-- The table listing the files available for upload/download -->
							        <table role="presentation" class="table table-striped"><tbody class="files"></tbody></table>
							      </div>
							    </div>
							</fieldset>
						</div>
						<div class="modal-footer">
							<div id="msgSubmit" class="h4 hidden"></div>
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
		    </form>
		</div>
		<?php require '../includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require '../includes/common_js.php'; ?>
	<!-- The template to display files available for upload -->
	<script id="template-upload" type="text/x-tmpl">
	{% for (var i=0, file; file=o.files[i]; i++) { %}
	    <tr class="template-upload fade">
	        <td>
	            <span class="preview"></span>
	        </td>
	        <td>
	            <p class="name">{%=file.name%}</p>
	            <strong class="error text-danger"></strong>
	        </td>
	        <td>
	            <p class="size">Processing...</p>
	            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
	        </td>
	        <td>
	            {% if (!i && !o.options.autoUpload) { %}
	                <button class="btn btn-primary start" disabled>
	                    <i class="glyphicon glyphicon-upload"></i>
	                    <span>Start</span>
	                </button>
	            {% } %}
	            {% if (!i) { %}
	                <button class="btn btn-warning cancel">
	                    <i class="glyphicon glyphicon-ban-circle"></i>
	                    <span>Cancel</span>
	                </button>
	            {% } %}
	        </td>
	    </tr>
	{% } %}
	</script>
	<!-- The template to display files available for download -->
	<script id="template-download" type="text/x-tmpl">
	{% for (var i=0, file; file=o.files[i]; i++) { %}
	    <tr class="template-download fade">
	        <td>
	            <span class="preview">
	                {% if (file.thumbnailUrl) { %}
	                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
	                {% } %}
	            </span>
	        </td>
	        <td>
	            <p class="name">
	                {% if (file.url) { %}
	                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
	                {% } else { %}
	                    <span>{%=file.name%}</span>
	                {% } %}
	            </p>
	            {% if (file.error) { %}
	                <div><span class="label label-danger">Error</span> {%=file.error%}</div>
	            {% } %}
	        </td>
	        <td>
	            <span class="size">{%=o.formatFileSize(file.size)%}</span>
	        </td>
	        <td>
	            {% if (file.deleteUrl) { %}
	                <button class="btn btn-danger delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
	                    <i class="glyphicon glyphicon-trash"></i>
	                    <span>Delete</span>
	                </button>
	            {% } else { %}
	                <button class="btn btn-warning cancel">
	                    <i class="glyphicon glyphicon-ban-circle"></i>
	                    <span>Cancel</span>
	                </button>
	            {% } %}
	        </td>
	    </tr>
	{% } %}
	</script>
	<script type="text/javascript" src="/dataTables-1.10.15/datatables.min.js"></script>
	<script type="text/javascript" src="/bootstrap-validator-0.11.9/js/bootstrap-validator-0.11.9.min.js"></script>
	<script type="text/javascript" src="/jquery.fileupload-9.20.0/vendor/jquery.ui.widget.js"></script>
	<script type="text/javascript" src="/JavaScript-Templates-3.11.0/js/tmpl.min.js"></script>
	<script type="text/javascript" src="/jquery.fileupload-9.20.0/js/jquery.fileupload.js"></script>
	<script type="text/javascript" src="/jquery.fileupload-9.20.0/js/jquery.fileupload-process.js"></script>
	<script type="text/javascript" src="/jquery.fileupload-9.20.0/js/jquery.fileupload-validate.js"></script>
	<script type="text/javascript" src="/jquery.fileupload-9.20.0/js/jquery.fileupload-ui.js"></script>
	<script type="text/javascript" src="/kcb-js/documents.js"></script>
	</script>
  </body>
</html>
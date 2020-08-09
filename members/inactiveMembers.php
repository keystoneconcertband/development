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
	<link rel="stylesheet" href="/css/member.css">
    <link rel="stylesheet" href="/css/checkboxes.min.css"/>
	<link rel="stylesheet" type="text/css" href="/3rd-party/dataTables-1.10.15/datatables.min.css"/>
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
					<h2>Inactive Members</h2>
				</div>
				<div class="row form-group">
					<div class="col-sm-12">
						<div id="msgMainHeader" class="h4 hidden"></div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<table id="kcbMemberTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
							<thead>
								<th></th>
								<th>Name</th>
								<th>Email Address(es)</th>
								<th>Instrument</th>
								<th>Cell Phone</th>
								<th>Home Phone</th>
								<th>Address</th>
								<th>Disabled Date</th>
							</thead>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="modal_edit_delete" role="dialog">
			<form id="form_member" data-toggle="validator">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title">Reinstate Member</h5>
						</div>
						<div class="modal-body form-horizontal">
							<fieldset>
							    <legend>Personal Information</legend>
							    <div class="form-group">
							      <div class="col-sm-12">
							        <label for="FirstName" class="control-label">First Name</label>
							        <input type="text" class="form-control" name="firstName" id="firstName" placeholder="First Name" value="" required="true" maxlength="50" data-error="First name is required.">
									<div class="help-block with-errors"></div>
							      </div>
							    </div>
							    <div class="form-group">
							      <div class="col-sm-12">
							        <label for="LastName" class="control-label">Last Name</label>
							        <input type="text" class="form-control" name="lastName" id="lastName" placeholder="Last Name" value="" required="true" maxlength="50" data-error="Last name is required.">
									<div class="help-block with-errors"></div>
							      </div>
							    </div>
							    <div class="form-group">
							      <div class="col-lg-12">
					                    <div class="checkbox checkbox-success checkbox-inline">
					                        <input type="checkbox" name="displayFullName" id="displayFullName" value="1">
					                        <label for="displayFullName"> Display <strong>full name</strong> on website. <em>If unselected your name will be displayed as <strong>Firstname L.</strong></em></label>
					                    </div>
							      </div>
							    </div>
								<div class="form-group">
							      <div class="col-sm-12">
							        <label for="HomePhoneNbr" class="control-label">Home Phone Nbr</label>
							        <input type="tel" class="form-control" name="home_phone" id="home_phone" placeholder="Home Phone Number - NOT your cell phone number." value="" data-minlength="10" maxlength="10">
									<div class="help-block with-errors"></div>
							      </div>
								</div>
								<div class="form-group">
							      <div class="col-sm-12">
							        <label for="Address" class="control-label">Address</label>
							        <input type="text" class="form-control" name="address1" id="address1" placeholder="Address" value="" required="true" maxlength="255" data-error="Address is required.">
									<div class="help-block with-errors"></div>
							      </div>
								</div>
								<div class="form-group">
							      <div class="col-sm-12">
							        <label for="Address2" class="control-label">Address 2</label>
							        <input type="text" class="form-control" name="address2" id="address2" placeholder="Address 2" value="" maxlength="255">
									<div class="help-block with-errors"></div>
							      </div>
								</div>
								<div class="form-group">
							      <div class="col-sm-12">
							        <label for="City" class="control-label">City</label>
							        <input type="text" class="form-control" name="city" id="city" placeholder="City" value="" required="true" maxlength="100">
									<div class="help-block with-errors"></div>
							      </div>
								</div>
								<div class="form-group">
							      <div class="col-sm-2">
							        <label for="State" class="control-label">State</label>
							        <input type="text" class="form-control" name="state" id="state" placeholder="State" value="PA" disabled="true" required="true" maxlength="2">
									<div class="help-block with-errors"></div>
							      </div>
								</div>
								<div class="form-group" id="zipContainer">
							      <div class="col-sm-4">
							        <label for="Zip" class="control-label">Zip Code</label>
							        <input type="tel" class="form-control" name="zip" id="zip" placeholder="Zip Code" value="" required="true" data-minlength="5" maxlength="5">
									<div class="help-block with-errors"></div>
							      </div>
								</div>
							    <div class="form-group emailContainers" id="emailContainer1">
							      	<div class="col-sm-12">
							        	<label for="Email" class="control-label">Email</label>
							        	<div class="input-group">
											<input type="email" class="form-control email1" name="email[]" id="email[]" placeholder="Email Address" maxlength="100">
											<span class="input-group-addon">
												<a href="#noscroll" id="email1" onclick="deleteEmail('emailContainer1');"><span class="glyphicon glyphicon-remove"></span></a>
											</span>
							        	</div>
								    </div>
							    </div>
							    <div class="form-group">
									<div class="col-sm-12">
										<button type="button" class="btn btn-default btn-xs" id="addRow">
										  <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add New Email
										</button>
									</div>
							    </div>
							</fieldset>
							<div class="form-group">
							</div>
							<fieldset>
							  <legend>Band Information</legend>
								<div class="form-group">
							      <div class="col-sm-12">
							        <label for="CellPhoneNbr" class="control-label">Cell Phone / Texting Notification Nbr</label>
							        <input type="tel" class="form-control" name="text" id="text" placeholder="Cell Phone Number" value="" data-minlength="10" maxlength="10">
									<div class="help-block with-errors"></div>
							      </div>
								</div>
								<div class="form-group">
									<div class="col-sm-12">
							        	<label for="optCarrier" class="control-label">Cell Phone Carrier</label>
										<select class="form-control" name="carrier" id="carrier" data-carrier>
											<option value="0">Select an option</option>
											<option value="txt.att.net">AT&amp;T</option>
											<option value="messaging.sprintpcs.com">Sprint</option>
											<option value="tmomail.net">TMobile</option>
											<option value="mmst5.tracfone.com">TracFone</option>
											<option value="vtext.com">Verizon</option>
											<option value="vmobl.com">Virgin</option>
								        </select>
										<div class="help-block with-errors"></div>
								    </div>
								</div>
								<div class="form-group">
									<div class="col-sm-12">
										<label for="Instrument" class="control-label">Instrument(s)</label><br>
										<div class="checkbox checkbox-success checkbox-inline" style="margin-left:10px;">
					                        <input type="checkbox" id="baritone" value="baritone" name="instrument[]">
					                        <label for="baritone"> Baritone</label>
					                    </div>
					                    <div class="checkbox checkbox-success checkbox-inline">
					                        <input type="checkbox" id="bassClarinet" value="bassClarinet" name="instrument[]">
					                        <label for="bassClarinet"> Bass Clarinet</label>
					                    </div>
					                    <div class="checkbox checkbox-success checkbox-inline">
					                        <input type="checkbox" id="bassoon" value="bassoon" name="instrument[]">
					                        <label for="bassoon"> Bassoon</label>
					                    </div>
					                    <div class="checkbox checkbox-success checkbox-inline">
					                        <input type="checkbox" id="clarinet" value="clarinet" name="instrument[]">
					                        <label for="clarinet"> Clarinet</label>
					                    </div>
					                    <div class="checkbox checkbox-success checkbox-inline">
					                        <input type="checkbox" id="flute" value="flute" name="instrument[]">
					                        <label for="flute"> Flute</label>
					                    </div>
					                    <div class="checkbox checkbox-success checkbox-inline">
					                        <input type="checkbox" id="frenchHorn" value="frenchHorn" name="instrument[]">
					                        <label for="frenchHorn"> French Horn</label>
					                    </div>
					                    <div class="checkbox checkbox-success checkbox-inline">
					                        <input type="checkbox" id="saxophone" value="saxophone" name="instrument[]">
					                        <label for="saxophone"> Saxophone</label>
					                    </div>
					                    <div class="checkbox checkbox-success checkbox-inline">
					                        <input type="checkbox" id="trombone" value="trombone" name="instrument[]">
					                        <label for="trombone"> Trombone</label>
					                    </div>
					                    <div class="checkbox checkbox-success checkbox-inline">
					                        <input type="checkbox" id="trumpet" value="trumpet" name="instrument[]">
					                        <label for="bassoon"> Trumpet</label>
					                    </div>
					                    <div class="checkbox checkbox-success checkbox-inline">
					                        <input type="checkbox" id="tuba" value="tuba" name="instrument[]">
					                        <label for="tuba"> Tuba</label>
					                    </div>
					                    <div class="checkbox checkbox-success checkbox-inline">
					                        <input type="checkbox" id="percussion" value="percussion" name="instrument[]">
					                        <label for="percussion"> Percussion</label>
					                    </div>
									</div>
								</div>
							</fieldset>
						</div>
						<div class="modal-footer">
							<div id="msgSubmit" class="h4 hidden"></div>
							<input type="hidden" id="uid" name="uid" value="" />
							<button type="submit" class="btn btn-primary">Save changes</button>
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php require '../includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require '../includes/common_js.php'; ?>
	<script type="text/javascript" src="/3rd-party/dataTables-1.10.15/datatables.min.js"></script>
	<script type="text/javascript" src="/3rd-party/bootstrap-validator-0.11.9/js/bootstrap-validator-0.11.9.min.js"></script>
	<script type="text/javascript" src="/kcb-js/inactiveMembers.js"></script>
  </body>
</html>
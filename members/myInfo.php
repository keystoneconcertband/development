<?php
include_once '../includes/class/protectedMember.class.php';
$mbr = new ProtectedMember();
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
					<h2>My Information</h2>
				</div>
				<div class="well bs-component">
					<form class="form-horizontal" id="memberInfo" data-toggle="validator">
						<fieldset>
						    <legend>Personal Information</legend>
						    <div class="form-group">
						      <div class="col-sm-12">
						        <label for="txtFirstName" class="control-label">First Name</label>
						        <input type="text" class="form-control" name="txtFirstName" id="txtFirstName" placeholder="First Name" value="<?= $member['firstName']?>" required="true" maxlength="50" data-error="First name is required.">
								<div class="help-block with-errors"></div>
						      </div>
						    </div>
						    <div class="form-group">
						      <div class="col-sm-12">
						        <label for="txtLastName" class="control-label">Last Name</label>
						        <input type="text" class="form-control" name="txtLastName" id="txtLastName" placeholder="Last Name" value="<?= $member['lastName']?>" required="true" maxlength="50" data-error="Last name is required.">
								<div class="help-block with-errors"></div>
						      </div>
						    </div>
						    <div class="form-group">
						      <div class="col-lg-12">
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="chkFullName" value="1" name="chkFullName" <?= $member['displayFullName'] == '1' ? 'checked="checked"' : ''; ?>>
				                        <label for="chkFullName"> Display <strong>full name</strong> on website. <em>If unselected your name will be displayed as <strong><?= $member['firstName']  ?> <?= substr($member['lastName'], 0, 1)  ?></strong></em></label>
				                    </div>
						      </div>
						    </div>
							<div class="form-group">
						      <div class="col-sm-12">
						        <label for="txtHomePhoneNbr" class="control-label">Home Phone Nbr</label>
						        <input type="tel" class="form-control" name="txtHomePhoneNbr" id="txtHomePhoneNbr" placeholder="Home Phone Number - NOT your cell phone number." value="<?= $member['home_phone']?>" data-minlength="10" maxlength="10">
								<div class="help-block with-errors"></div>
						      </div>
							</div>
							<div class="form-group">
						      <div class="col-sm-12">
						        <label for="txtAddress" class="control-label">Address</label>
						        <input type="text" class="form-control" name="txtAddress" id="txtAddress" placeholder="Address" value="<?= $member['address1']?>" required="true" maxlength="255" data-error="Address is required.">
								<div class="help-block with-errors"></div>
						      </div>
							</div>
							<div class="form-group">
						      <div class="col-sm-12">
						        <label for="txtAddress2" class="control-label">Address 2</label>
						        <input type="text" class="form-control" name="txtAddress2" id="txtAddress2" placeholder="Address 2" value="<?= $member['address2']?>" maxlength="255">
								<div class="help-block with-errors"></div>
						      </div>
							</div>
							<div class="form-group">
						      <div class="col-sm-12">
						        <label for="txtCity" class="control-label">City</label>
						        <input type="text" class="form-control" name="txtCity" id="txtCity" placeholder="City" value="<?= $member['city']?>" required="true" maxlength="100">
								<div class="help-block with-errors"></div>
						      </div>
							</div>
							<div class="form-group">
						      <div class="col-sm-2">
						        <label for="txtState" class="control-label">State</label>
						        <input type="text" class="form-control" name="txtState" id="txtState" placeholder="State" value="<?= $member['state']?>" disabled="true" required="true" maxlength="2">
								<div class="help-block with-errors"></div>
						      </div>
							</div>
							<div class="form-group">
						      <div class="col-sm-4">
						        <label for="txtZip" class="control-label">Zip Code</label>
						        <input type="tel" class="form-control" name="txtZip" id="txtZip" placeholder="Zip Code" value="<?= $member['zip']?>" required="true" data-minlength="5" maxlength="5">
								<div class="help-block with-errors"></div>
						      </div>
							</div>
						    <div class="form-group" id="emailContainer">
						    <?php
								$emailAddresses = $mbr->getEmailAddresses($_SESSION['uid']);
								$i = 1;
								// Loop through each user email and create textboxes for each
								if(count($emailAddresses) == 0){
							?>
						      	<div class="col-lg-12 extraEmail">
						        	<label for="txtEmail1" class="control-label" id="lblEmail1" name="lblEmail1">Email</label>
									<input type="email" class="form-control" name="txtEmail[]" id="txtEmail[]" placeholder="Email Address" value="" maxlength="100" required="true" data-error="Email must be in a valid format and is required.">
									<div class="help-block with-errors"></div>
								</div>
							<?
								}
								else {
									foreach ($emailAddresses as $email) {
							?>
						      	<div class="col-sm-12 extraEmail">
						        	<label for="txtEmail<?=$i?>" class="control-label" id="lblEmail<?=$i?>" name="lblEmail<?=$i?>">Email <?=$i?></label>
									<input type="email" class="form-control" name="txtEmail[]" id="txtEmail[]" placeholder="Email Address" value="<?=$email['email_address']?>" maxlength="100" data-error="Email must be in a valid format.">
									<div class="help-block with-errors"></div>
								</div>
							<?php
										$i++;
									}
								}
							?>
						      	<div class="col-sm-12 extraEmailTemplate">
						        	<label for="txtEmail" class="control-label" id="lblEmail" name="lblEmail">Email</label>
									<input type="email" class="form-control" name="txtEmail[]" id="txtEmail[]" placeholder="Email Address" maxlength="100">
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
						        <label for="txtCellPhoneNbr" class="control-label">Cell Phone / Texting Notification Nbr</label>
						        <input type="tel" class="form-control" name="txtCellPhoneNbr" id="txtCellPhoneNbr" placeholder="Cell Phone Number" value="<?= $member['text']?>" data-minlength="10" maxlength="10">
								<div class="help-block with-errors"></div>
						      </div>
							</div>
							<div class="form-group">
								<div class="col-sm-12">
						        	<label for="optCarrier" class="control-label">Cell Phone Carrier</label>
									<select class="form-control" name="optCarrier" id="optCarrier" data-carrier>
										<option value="0">Select an option</option>
										<option value="txt.att.net" <?= $member['carrier'] == 'txt.att.net' ? ' selected="selected"' : '';?>>AT&amp;T</option>
										<option value="messaging.sprintpcs.com" <?= $member['carrier'] == 'messaging.sprintpcs.com' ? ' selected="selected"' : '';?>>Sprint</option>
										<option value="tmomail.net" <?= $member['carrier'] == 'tmomail.net' ? ' selected="selected"' : '';?>>TMobile</option>
										<option value="mmst5.tracfone.com" <?= $member['carrier'] == 'mmst5.tracfone.com' ? ' selected="selected"' : '';?>>TracFone</option>
										<option value="vtext.com" <?= $member['carrier'] == 'vtext.com' ? ' selected="selected"' : '';?>>Verizon</option>
										<option value="vmobl.com" <?= $member['carrier'] == 'vmobl.com' ? ' selected="selected"' : '';?>>Virgin</option>
							        </select>
									<div class="help-block with-errors"></div>
							    </div>
							</div>
							<div class="form-group">
								<div class="col-sm-12">
									<?php
										$instruments[] = $mbr->getMemberInstruments($_SESSION['uid']);
									?>
									<label for="Instrument" class="control-label">Instrument(s)</label><br>
									<div class="checkbox checkbox-success checkbox-inline" style="margin-left:10px;">
				                        <input type="checkbox" id="baritone" value="baritone" name="chkInstrument[]" <?if($mbr->in_multiarray('baritone', $instruments)) echo('checked="checked"');?>>
				                        <label for="baritone"> Baritone</label>
				                    </div>
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="bassClarinet" value="bassClarinet" name="chkInstrument[]" <?if($mbr->in_multiarray('bassClarinet', $instruments)) echo('checked="checked"');?>>
				                        <label for="bassClarinet"> Bass Clarinet</label>
				                    </div>
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="bassoon" value="bassoon" name="chkInstrument[]" <?if($mbr->in_multiarray('bassoon', $instruments)) echo('checked="checked"');?>>
				                        <label for="bassoon"> Bassoon</label>
				                    </div>
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="clarinet" value="clarinet" name="chkInstrument[]" <?if($mbr->in_multiarray('clarinet', $instruments)) echo('checked="checked"');?>>
				                        <label for="clarinet"> Clarinet</label>
				                    </div>
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="flute" value="flute" name="chkInstrument[]" <?if($mbr->in_multiarray('flute', $instruments)) echo('checked="checked"');?>>
				                        <label for="flute"> Flute</label>
				                    </div>
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="frenchHorn" value="frenchHorn" name="chkInstrument[]" <?if($mbr->in_multiarray('frenchHorn', $instruments)) echo('checked="checked"');?>>
				                        <label for="frenchHorn"> French Horn</label>
				                    </div>
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="saxophone" value="saxophone" name="chkInstrument[]" <?if($mbr->in_multiarray('saxophone', $instruments)) echo('checked="checked"');?>>
				                        <label for="saxophone"> Saxophone</label>
				                    </div>
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="trombone" value="trombone" name="chkInstrument[]" <?if($mbr->in_multiarray('trombone', $instruments)) echo('checked="checked"');?>>
				                        <label for="trombone"> Trombone</label>
				                    </div>
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="trumpet" value="trumpet" name="chkInstrument[]" <?if($mbr->in_multiarray('trumpet', $instruments)) echo('checked="checked"');?>>
				                        <label for="bassoon"> Trumpet</label>
				                    </div>
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="tuba" value="tuba" name="chkInstrument[]" <?if($mbr->in_multiarray('tuba', $instruments)) echo('checked="checked"');?>>
				                        <label for="tuba"> Tuba</label>
				                    </div>
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="percussion" value="percussion" name="chkInstrument[]" <?if($mbr->in_multiarray('percussion', $instruments)) echo('checked="checked"');?>>
				                        <label for="percussion"> Percussion</label>
				                    </div>
								</div>
							</div>
						</fieldset>
						<div class="form-group">
						</div>
						<div class="form-group">
						  <div class="col-lg-12">
						    <button type="submit" class="btn btn-primary">Submit</button>
						    <button type="reset" class="btn btn-default">Cancel</button>
							<div id="msgSubmit" class="h4 hidden"></div>
						  </div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php require '../includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require '../includes/common_js.php'; ?>
	<script type="text/javascript" src="/bootstrap-validator-0.11.9/js/bootstrap-validator-0.11.9.min.js"></script>
	<script type="text/javascript" src="/kcb-js/myInfo.js"></script>
  </body>
</html>

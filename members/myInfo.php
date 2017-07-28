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
				If any of your information has changed, please use the form below to update it.<br/><br>
				<div class="well bs-component">
					<form class="form-horizontal">
						<fieldset>
						    <legend>Personal Information</legend>
						    <div class="form-group">
						      <div class="col-sm-6">
						        <label for="inputFirstName" class="control-label">First Name</label>
						        <input type="text" class="form-control" id="inputFirstName" placeholder="First Name" value="<?= $member['firstName']?>" required="true">
						      </div>
						      <div class="col-sm-6">
						        <label for="inputLastName" class="control-label">Last Name</label>
						        <input type="text" class="form-control" id="inputLastName" placeholder="Last Name" value="<?= $member['lastName']?>" required="true">
						      </div>
						      <div class="col-sm-12">
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="inputFullName" value="1" name="inputFullName" <?= $member['displayFullName'] == '1' ? 'checked="checked"' : ''; ?>>
				                        <label for="inputFullName"> Display <strong>full name</strong> on website. <em>If unselected your name will be displayed as <strong><?= $member['firstName']  ?> <?= substr($member['lastName'], 0, 1)  ?></strong></em></label>
				                    </div>
						      </div>
						    </div>
							<div class="form-group">
						      <div class="col-lg-12">
						        <label for="inputHomePhoneNbr" class="control-label">Home Phone Nbr</label>
						        <input type="text" class="form-control" id="inputHomePhoneNbr" placeholder="Home Phone Number - NOT your cell phone number." value="<?= $member['home_phone']?>">
						      </div>
							</div>
							<div class="form-group">
						      <div class="col-lg-12">
						        <label for="inputAddress" class="control-label">Address</label>
						        <input type="text" class="form-control" id="inputAddress" placeholder="Address" value="<?= $member['address1']?>" required="true">
						      </div>
							</div>
							<div class="form-group">
						      <div class="col-lg-12">
						        <label for="inputAddress2" class="control-label">Address 2</label>
						        <input type="text" class="form-control" id="inputAddress2" placeholder="Address" value="<?= $member['address2']?>">
						      </div>
							</div>
							<div class="form-group">
						      <div class="col-sm-6">
						        <label for="inputCity" class="control-label">City</label>
						        <input type="text" class="form-control" id="inputCity" placeholder="Address" value="<?= $member['city']?>" required="true">
						      </div>
						      <div class="col-sm-2">
						        <label for="inputState" class="control-label">State</label>
						        <input type="text" class="form-control" id="inputState" placeholder="Address" value="<?= $member['state']?>" disabled="" required="true">
						      </div>
						      <div class="col-sm-4">
						        <label for="inputZip" class="control-label">Zip Code</label>
						        <input type="text" class="form-control" id="inputZip" placeholder="Address" value="<?= $member['zip']?>" required="true">
						      </div>
							</div>
						    <div class="form-group" id="emailContainer">
						    <?php
								$emailAddresses = $mbr->getEmailAddresses($_SESSION['uid']);
								$i = 1;
								// Loop through each user email and create textboxes for each
								if(count($emailAddresses) == 0){
									echo("There are no members who currently play this instrument!<br /><a href='join.php'>Come join us!</a>");
								}
								else {
									foreach ($emailAddresses as $email) {
							?>
						      	<div class="col-lg-12 extraEmail">
						        	<label for="inputEmail<?=$i?>" class="control-label" id="lblEmail<?=$i?>" name="lblEmail<?=$i?>">Email <?=$i?></label>
									<input type="text" class="form-control" id="inputEmail[]" placeholder="Email Address" value="<?=$email['email_address']?>">
								</div>
							<?php
										$i++;
									}
								}
							?>
						      	<div class="col-lg-12 extraEmailTemplate">
						        	<label for="inputEmail" class="control-label" id="lblEmail" name="lblEmail">Email</label>
									<input type="text" class="form-control" id="inputEmail[]" placeholder="Email Address" name="inputEmail">
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
						      <div class="col-lg-12">
						        <label for="inputCellPhoneNbr" class="control-label">Cell Phone / Texting Notification Nbr</label>
						        <input type="text" class="form-control" id="inputCellPhoneNbr" placeholder="Cell Phone Number" value="<?= $member['text']?>">
						      </div>
							</div>
							<div class="form-group">
								<div class="col-lg-12">
						        	<label for="inputCarrier" class="control-label">Cell Phone Carrier</label>
									<select class="form-control" id="inputCarrier">
										<option value="0">Select an option</option>
										<option value="txt.att.net" <?= $member['carrier'] == 'txt.att.net' ? ' selected="selected"' : '';?>>AT&amp;T</option>
										<option value="messaging.sprintpcs.com" <?= $member['carrier'] == 'messaging.sprintpcs.com' ? ' selected="selected"' : '';?>>Sprint</option>
										<option value="tmomail.net" <?= $member['carrier'] == 'tmomail.net' ? ' selected="selected"' : '';?>>TMobile</option>
										<option value="mmst5.tracfone.com" <?= $member['carrier'] == 'mmst5.tracfone.com' ? ' selected="selected"' : '';?>>TracFone</option>
										<option value="vtext.com" <?= $member['carrier'] == 'vtext.com' ? ' selected="selected"' : '';?>>Verizon</option>
										<option value="vmobl.com" <?= $member['carrier'] == 'vmobl.com' ? ' selected="selected"' : '';?>>Virgin</option>
							        </select>								
							    </div>
							</div>
							<div class="form-group">
								<div class="col-lg-12">
									<?php
										$instruments[] = $mbr->getMemberInstruments($_SESSION['uid']);
									?>
									<label for="inputInstrument" class="control-label">Instrument(s)</label><br>
									<div class="checkbox checkbox-success checkbox-inline" style="margin-left:10px;">
				                        <input type="checkbox" id="baritone" value="baritone" name="inputInstrument[]" <?if($mbr->in_multiarray('baritone', $instruments)) echo('checked="checked"');?>>
				                        <label for="baritone"> Baritone</label>
				                    </div>
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="bassClarinet" value="bassClarinet" name="inputInstrument[]" <?if($mbr->in_multiarray('bassClarinet', $instruments)) echo('checked="checked"');?>>
				                        <label for="bassClarinet"> Bass Clarinet</label>
				                    </div>
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="bassoon" value="bassoon" name="inputInstrument[]" <?if($mbr->in_multiarray('bassoon', $instruments)) echo('checked="checked"');?>>
				                        <label for="bassoon"> Bassoon</label>
				                    </div>
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="clarinet" value="clarinet" name="inputInstrument[]" <?if($mbr->in_multiarray('clarinet', $instruments)) echo('checked="checked"');?>>
				                        <label for="clarinet"> Clarinet</label>
				                    </div>
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="flute" value="flute" name="inputInstrument[]" <?if($mbr->in_multiarray('flute', $instruments)) echo('checked="checked"');?>>
				                        <label for="flute"> Flute</label>
				                    </div>
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="frenchHorn" value="frenchHorn" name="inputInstrument[]" <?if($mbr->in_multiarray('frenchHorn', $instruments)) echo('checked="checked"');?>>
				                        <label for="frenchHorn"> French Horn</label>
				                    </div>
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="saxophone" value="saxophone" name="inputInstrument[]" <?if($mbr->in_multiarray('saxophone', $instruments)) echo('checked="checked"');?>>
				                        <label for="saxophone"> Saxophone</label>
				                    </div>
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="trombone" value="trombone" name="inputInstrument[]" <?if($mbr->in_multiarray('trombone', $instruments)) echo('checked="checked"');?>>
				                        <label for="trombone"> Trombone</label>
				                    </div>
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="trumpet" value="trumpet" name="inputInstrument[]" <?if($mbr->in_multiarray('trumpet', $instruments)) echo('checked="checked"');?>>
				                        <label for="bassoon"> Trumpet</label>
				                    </div>
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="tuba" value="tuba" name="inputInstrument[]" <?if($mbr->in_multiarray('tuba', $instruments)) echo('checked="checked"');?>>
				                        <label for="tuba"> Tuba</label>
				                    </div>
				                    <div class="checkbox checkbox-success checkbox-inline">
				                        <input type="checkbox" id="percussion" value="percussion" name="inputInstrument[]" <?if($mbr->in_multiarray('percussion', $instruments)) echo('checked="checked"');?>>
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
						  </div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php require '../includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require '../includes/common_js.php'; ?>
	<script>
		$(document).ready(function () {
	    	var options = {
	    		url: 'memberUpdate.php',
	    		type: 'POST',
	    		beforeSubmit: validate,
	    		success: showSuccess }
	        $('#kcbMemberInfo').ajaxForm(options);
	        
		    var msg = getParameterByName('err');
		    if(msg != "") {
	        	$("#majorFailure").show();
				$("#errMsg").html(msg);
		    }
			$('#addRow').click(function () {
				$('<div/>', {
					'class' : 'col-lg-12 extraEmail', html: GetHtml()
				}).hide().appendTo('#emailContainer').slideDown('slow'); //Get the html from template and hide and slideDown for transtion.
			});
		});

		function GetHtml() //Get the template and update the input field names
		{
			var len = $('.extraEmail').length + 1;
			var $html = $('.extraEmailTemplate').clone();
			$html.find('[name=lblEmail]')[0].id="lblEmail" + len;
			$html.find('[name=lblEmail]').text("Email " + len);
			$html.find('[name=lblEmail]').attr('for', "inputEmail" + len);
			$html.find('[name=lblEmail]').attr('name', "lblEmail" + len);
			$html.find('[name=inputEmail]')[0].name="inputEmail" + len;
			
			return $html.html();    
		}
		
	    function validate(formData, jqForm, options) {
		    // need this!!! http://1000hz.github.io/bootstrap-validator/#validator-markup
	        var form = jqForm[0];
	    
			else if(form.text.value.length !== 10 && form.text.value.length !== 0) {
	        	$("#errorMsg").html("Enter the full 10 digit phone number.");
			}
			else if (form.text.value.length > 1 && form.carrier.value === '0' ) {
	        	$("#errorMsg").html("Carrier is required when you enter your phone number.");
			}
	        else {
	        	if(form.text.value.length === 0) {
		        	form.carrier.value = 0;
	        	}
	        	
	        	$("#failure").hide();
	        	$("#success").show();
	
	        	return true;
	        }
	        
	        return false;
	    }
	</script>
  </body>
</html>

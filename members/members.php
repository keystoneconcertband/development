<?php
include_once '../includes/class/protectedMember.class.php';
require_once '../includes/asset.php';
new ProtectedMember();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require '../includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band member area">

    <title>KCB Members - Keystone Concert Band</title>

    <?php require '../includes/common_css.php'; ?>
    <link rel="stylesheet" href="<?= asset('/css/member.css') ?>">
</head>

<body>

    <?php require '../includes/nav.php'; ?>
    <div class="container">
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-lg-12">
                <div class="mb-4 pb-2 border-bottom">
                    <h2>Current Members</h2>
                </div>
                <?php if($_SESSION['accountType'] === 1 || $_SESSION['accountType'] === 2) { ?>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#modal_edit_delete">Add Member</button>
                        <button type="button" class="btn btn-light fa fa-print btn-lg" onclick="printMembers()"></button>
                    </div>
                    <div class="col-sm-9">
                    </div>
                </div>
                <?php } else { ?>
                <div class="row mb-3">
                    <div class="col-sm-12">
                        <button type="button" class="btn btn-light fa fa-print btn-lg" onclick="printMembers()"></button>
                    </div>
                </div>
                <?php } ?>
                <div id="pageAlert" class="alert d-none alert-dismissible fade show" role="alert"></div>
                <div class="row">
                    <div class="col-lg-12">
                        <table id="kcbMemberTable" class="table table-striped table-bordered" cellspacing="0"
                            width="100%">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Name</th>
                                    <th>Email Address(es)</th>
                                    <th>Instrument</th>
                                    <th>Cell Phone</th>
                                    <th>Address</th>
                                    <th>Volunteer Position</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modal_edit_delete" tabindex="-1" aria-labelledby="modalEditDeleteLabel"
            aria-hidden="true">
            <form id="form_member" data-toggle="validator">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalEditDeleteLabel">Add Member</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="formAlert" class="alert d-none alert-dismissible fade show" role="alert"></div>
                            <fieldset>
                                <legend>Personal Information</legend>
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" name="firstName" id="firstName"
                                            placeholder="First Name" value="" required="true" maxlength="50"
                                            data-error="First name is required.">
                                        <div class="help-block with-errors"></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" name="lastName" id="lastName"
                                            placeholder="Last Name" value="" required="true" maxlength="50"
                                            data-error="Last name is required.">
                                        <div class="help-block with-errors"></div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-lg-12">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="displayFullName"
                                                id="displayFullName" value="1">
                                            <label class="form-check-label" for="displayFullName">Display your
                                                <strong>full name</strong> on website.<br /><em>If unselected your name
                                                    will be displayed as <strong>Firstname L.</strong></em></label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <input type="text" class="form-control" name="address1" id="address1"
                                            placeholder="Address" value="" required="true" maxlength="255"
                                            data-error="Address is required.">
                                        <div class="help-block with-errors"></div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <input type="text" class="form-control" name="address2" id="address2"
                                            placeholder="Address 2" value="" maxlength="255">
                                        <div class="help-block with-errors"></div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" name="city" id="city" placeholder="City"
                                            value="" required="true" maxlength="100">
                                        <div class="help-block with-errors"></div>
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" name="state" id="state"
                                            placeholder="State" value="PA" disabled="true" required="true"
                                            maxlength="2">
                                        <div class="help-block with-errors"></div>
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="tel" class="form-control" name="zip" id="zip"
                                            placeholder="Zip Code" value="" required="true" data-minlength="5"
                                            maxlength="5">
                                        <div class="help-block with-errors"></div>
                                    </div>
                                </div>
                        	</fieldset>
							<div class="row mb-3">
							</div>
							<fieldset>
								<legend>Contact Information</legend>
                                <div class="row mb-3" id="textContainer">
                                    <div class="col-sm-12">
						        	    <label for="txtCellPhoneNbr" class="form-label">Cell Phone / Text Notification Nbr</label>
                                        <input type="tel" class="form-control" name="text" id="text"
                                            placeholder="Cell Phone Number" value="" data-minlength="10" maxlength="13">
                                        <div class="help-block with-errors"></div>
                                    </div>
                                </div>
                                <div class="row mb-3 emailContainers" id="emailContainer1">
                                    <div class="col-sm-12">
    						        	<label for="email" class="form-label">Email Address(es)</label>
                                        <div class="input-group">
                                            <input type="email" class="form-control email1" name="email[]" id="email[]"
                                                placeholder="Email Address" maxlength="100">
                                            <span class="input-group-text">
                                                <a href="#noscroll" id="email1"
                                                    onclick="deleteEmail('emailContainer1');"><span
                                                        class="fa fa-remove"></span></a>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="addRow">
                                            <span class="fa fa-plus" aria-hidden="true"></span> Add New Email
                                        </button>
                                    </div>
                                </div>
                            </fieldset>
                            <div class="row mb-3">
                            </div>
                            <fieldset>
                                <legend>Band Information</legend>
                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <label for="Instrument" class="form-label">Instrument(s)</label>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="baritone"
                                                value="baritone" name="instrument[]">
                                            <label class="form-check-label" for="baritone">Baritone</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="bassClarinet"
                                                value="bassClarinet" name="instrument[]">
                                            <label class="form-check-label" for="bassClarinet">Bass Clarinet</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="bassoon" value="bassoon"
                                                name="instrument[]">
                                            <label class="form-check-label" for="bassoon">Bassoon</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="clarinet"
                                                value="clarinet" name="instrument[]">
                                            <label class="form-check-label" for="clarinet">Clarinet</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="flute" value="flute"
                                                name="instrument[]">
                                            <label class="form-check-label" for="flute">Flute</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="frenchHorn"
                                                value="frenchHorn" name="instrument[]">
                                            <label class="form-check-label" for="frenchHorn">French Horn</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="saxophone"
                                                value="saxophone" name="instrument[]">
                                            <label class="form-check-label" for="saxophone">Saxophone</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="trombone"
                                                value="trombone" name="instrument[]">
                                            <label class="form-check-label" for="trombone">Trombone</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="trumpet" value="trumpet"
                                                name="instrument[]">
                                            <label class="form-check-label" for="trumpet">Trumpet</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="tuba" value="tuba"
                                                name="instrument[]">
                                            <label class="form-check-label" for="tuba">Tuba</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="percussion"
                                                value="percussion" name="instrument[]">
                                            <label class="form-check-label" for="percussion">Percussion</label>
                                        </div>
                                    </div>
                            </fieldset>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" id="uid" name="uid" value="" />
                            <button type="submit" class="btn btn-primary">Save changes</button>
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
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
    <?php require '../includes/common_datatables.php'; ?>
    <script type="text/javascript" src="<?=asset('/kcb-js/members.js')?>"></script>
</body>

</html>
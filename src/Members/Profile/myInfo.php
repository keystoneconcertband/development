<?php
require_once __DIR__ . '/../../Shared/Classes/protectedMember.class.php';
require_once __DIR__ . '/../../Shared/asset.php';
new ProtectedMember();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require __DIR__ . '/../../../templates/partials/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band member area">

    <title>My Personal Information - Keystone Concert Band</title>

    <?php require __DIR__ . '/../../../templates/partials/common_css.php'; ?>
    <link rel="stylesheet" href="<?= asset('/assets/css/member.css') ?>">
</head>

<body>

    <?php require __DIR__ . '/../../../templates/partials/nav.php'; ?>
    <div class="container">
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-lg-12">
                <div class="mb-4 pb-2 border-bottom">
                    <h2>My Information</h2>
                </div>
                <div id="pageAlert" class="alert d-none alert-dismissible fade show" role="alert"></div>
                <div class="p-4 mb-4 bg-light rounded-3">
                    <form id="memberInfo" class="needs-validation" novalidate>
                        <fieldset>
                            <legend>Personal Information</legend>
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" name="firstName" id="firstName"
                                        placeholder="First Name" required="true" maxlength="50">
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" name="lastName" id="lastName"
                                        placeholder="Last Name" required="true" maxlength="50">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-lg-12">
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" id="displayFullName" value="1"
                                            name="displayFullName">Display your
                                                <strong>full name</strong> on website. <em>If unselected your name
                                                    will be displayed as <strong><span id="spanFirstname">Firstname</span> <span id="spanLastInitial">L</span>.</strong></em>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="text" class="form-control" name="address1" id="address1"
                                        placeholder="Address" required="true" maxlength="255">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="text" class="form-control" name="address2" id="address2"
                                        placeholder="Address 2" maxlength="255">
                                </div>
                            </div>
							<div class="row mb-3">
								<div class="col-sm-6">
									<input type="text" class="form-control" name="city" id="city" placeholder="City"
										value="" required="true" maxlength="100">
								</div>
								<div class="col-sm-2">
									<input type="text" class="form-control" name="state" id="state"
										placeholder="State" value="PA" disabled="true" required="true"
										maxlength="2">
								</div>
								<div class="col-sm-4">
									<input type="tel" class="form-control" name="zip" id="zip"
										placeholder="Zip Code" value="" required="true" minlength="5"
										maxlength="5">
								</div>
							</div>
						</fieldset>
                        <div class="row mb-3">
                        </div>
                        <fieldset>
                            <legend>Contact Information</legend>
                            <div class="row mb-3">
                                <div class="col-sm-12">
						        	<label for="txtCellPhoneNbr" class="form-label">Cell Phone / Text Notification Nbr</label>
                                    <input type="tel" class="form-control" name="text" id="text"
                                        placeholder="Cell Phone Number" minlength="10" maxlength="13">
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
                                    <button type="button" class="btn btn-light btn-sm" id="addRow">
                                        <span class="fa fa-plus" aria-hidden="true"></span> Add New Email
                                    </button>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                                    <input type="text" class="form-control" name="emergency_contact_name" id="emergency_contact_name"
                                        placeholder="Emergency Contact Name" value="" maxlength="255">
                                </div>
                                <div class="col-sm-6">
                                    <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone</label>
                                    <input type="tel" class="form-control" name="emergency_contact_phone" id="emergency_contact_phone"
                                        placeholder="Emergency Contact Phone" value="" minlength="10" maxlength="13">
                                </div>
                            </div>
						</fieldset>
							<div class="row mb-3">
                        </div>
                        <fieldset>
                            <legend>Band Information</legend>
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <label for="Instrument" class="form-label">Instrument(s)</label><br>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" id="baritone" value="baritone"
                                            name="instrument[]">
                                        <label class="form-check-label" for="baritone">Baritone</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" id="bassClarinet"
                                            value="bassClarinet" name="instrument[]">
                                        <label class="form-check-label" for="bassClarinet">Bass Clarinet</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" id="bassoon" value="bassoon"
                                            name="instrument[]">
                                        <label class="form-check-label" for="bassoon">Bassoon</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" id="clarinet" value="clarinet"
                                            name="instrument[]">
                                        <label class="form-check-label" for="clarinet">Clarinet</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" id="flute" value="flute"
                                            name="instrument[]">
                                        <label class="form-check-label" for="flute">Flute</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" id="frenchHorn"
                                            value="frenchHorn" name="instrument[]">
                                        <label class="form-check-label" for="frenchHorn">French Horn</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" id="saxophone" value="saxophone"
                                            name="instrument[]">
                                        <label class="form-check-label" for="saxophone">Saxophone</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" id="trombone" value="trombone"
                                            name="instrument[]">
                                        <label class="form-check-label" for="trombone">Trombone</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" id="trumpet" value="trumpet"
                                            name="instrument[]">
                                        <label class="form-check-label" for="trumpet">Trumpet</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" id="tuba" value="tuba"
                                            name="instrument[]">
                                        <label class="form-check-label" for="tuba">Tuba</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" id="percussion"
                                            value="percussion" name="instrument[]">
                                        <label class="form-check-label" for="percussion">Percussion</label>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <div class="row mb-3">
                        </div>
                        <div class="row mb-3">
                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-primary">Submit</button>
                                <div id="msgSubmit" class="h4 d-none"></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php require __DIR__ . '/../../../templates/partials/footer.php'; ?>
    </div> <!-- /container -->

    <?php require __DIR__ . '/../../../templates/partials/common_js.php'; ?>
    <script type="text/javascript" src="<?=asset('/assets/js/shared.js')?>"></script>
    <script type="text/javascript" src="<?=asset('/assets/js/myInfo.js')?>"></script>
</body>

</html>

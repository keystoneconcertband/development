<!DOCTYPE html>
<html lang="en">
  <head>
	<?php require_once 'includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band is always looking for musicians to join us. Are you interested in joining our group?">

    <title>Join the Band - Keystone Concert Band</title>

	<?php require_once 'includes/common_css.php'; ?>
	
  </head>

  <body>

	<?php require_once 'includes/nav.php'; ?>
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="bs-component">
					<div class="jumbotron">
						<h1>Join</h1>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<div class="page-header">
					<h2>Join the Keystone Concert Band</h2>
				</div>
				<div class="well bs-component">
					The Keystone Concert Band is always looking for musicians. We accept musicians of all backgrounds and 
					from all over the greater Harrisburg area. Do you play a band instrument? 
					Are you interested in joining the Keystone Concert Band? 
					We would love to hear from you! Feel free to contact us by filling out the form below or 
					e-mailing us at <a href="mailto:info@keystoneconcertband.com">info@keystoneconcertband.com</a>.
				</div>
				<h3>Frequently Asked Questions</h3>
				<ul>
                    <li><strong>When do you meet?</strong><br />
                        We meet on Tuesday nights from 7:00 PM to 9:30 PM at the Mechanicsburg Middle School (Room 212) located at 
                        <a href="https://maps.google.com/maps?q=Mechanicsburg+Area+Middle+School">500 Compton Road, Mechanicsburg, PA 17055</a>.
                    </li>
                    <li><strong>During practice, is there a break?</strong><br />
                        Yes. We take a 10-minute break at 8:00PM.
                    </li>
                    <li><strong>Where do most of the concerts take place?</strong><br />
                        Almost all concerts take place in the greater Harrisburg region including Hummelstown, Hershey,
                        New Cumberland, Mechanicsburg, Harrisburg, and Camp Hill. Check out our <a
                            href="calendar.php">calendar and concerts
                            page</a> to see our current concert locations.
                    </li>
                    <li><strong>Does it cost anything to join?</strong><br />
                        No. We are a non-profit organization and rely fully on donations and fundraisers. We require no
                        dues or fees. You will be required to buy a KCB polo shirt to play at our concerts at a cost of
                        $25. We have one or two fundraisers each year. Participation is not required but is strongly
                        recommended in order to sustain the band.
                    </li>
                    <li><strong>Do I have to audition to join?</strong><br />
                        No, but we request that you contact us via the form below. You are expected to be able to play
                        at a high school level.
                    </li>
                    <li><strong>Is there an age requirement?</strong><br />
                        With a few exceptions, we ask that our younger members be at least in 9th grade. If you are in
                        7th or 8th grade and are considering joining, we ask that your band teacher or private
                        instructor to provide us with a recommendation.</li>
                </ul>
                <h3>Contact Us</h3>
                <form id="frmJoin" data-bs-toggle="validator">
                    <fieldset>
                        <legend></legend>
                        <div class="mb-3">
                            <div class="col-lg-12">
                                If you are interested in joining the band, please fill out the form below with your
                                contact information so we can get back to you.<br />
                                <em>Fields marked with an * are required.</em>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="txtName" class="col-lg-2 col-form-label">* Name</label>
                            <div class="col-lg-10">
                                <input type="text" class="form-control" id="txtName" name="txtName" placeholder="Name"
                                    required="true">
                                <div class="form-text"></div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="txtPhone" class="col-lg-2 col-form-label">Phone Number</label>
                            <div class="col-lg-10">
                                <input type="tel" class="form-control" id="txtPhone" name="txtPhone"
                                    placeholder="Phone Number" data-minlength="10" maxlength="10">
                                <div class="form-text"></div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="txtEmail" class="col-lg-2 col-form-label">* Email Address</label>
                            <div class="col-lg-10">
                                <input type="text" class="form-control" id="txtEmail" name="txtEmail"
                                    placeholder="Email Address" required="true">
                                <div class="form-text"></div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="txtInstrument" class="col-lg-2 col-form-label">* Instrument(s)</label>
                            <div class="col-lg-10">
                                <input type="text" class="form-control" id="txtInstrument" name="txtInstrument"
                                    placeholder="Instrument(s)" required="true">
                                <div class="form-text"></div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="txtYears" class="col-lg-2 col-form-label">Years Playing</label>
                            <div class="col-lg-10">
                                <input type="text" class="form-control" id="txtYears" name="txtYears"
                                    placeholder="Years of experience">
                                <div class="form-text"></div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="txtComments" class="col-lg-2 col-form-label">Comments</label>
                            <div class="col-lg-10">
                                <textarea class="form-control" rows="3" id="txtComments" name="txtComments"
                                    placeholder="Any additional information"></textarea>
                                <div class="form-text"></div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-lg-2 col-form-label">Newsletter</label>
                            <div class="col-lg-10">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="chkEmail" id="chkEmail" value="yes">
                                    <label class="form-check-label" for="chkEmail">
                                        Please send me updates about upcoming concerts and band events
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <div class="col-lg-10 offset-lg-2">
                                <button type="submit" class="btn btn-primary">Submit</button>
                                <div id="msgSubmit" class="h4 d-none"></div>
                            </div>
                        </div>
                    </fieldset>
                </form>
			</div>
		</div>
		<?php require_once 'includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require_once 'includes/common_js.php'; ?>
	<script type="text/javascript" src="/3rd-party/bootstrap-validator-0.11.9/js/bootstrap-validator-0.11.9.min.js"></script>
	<script type="text/javascript" src="kcb-js/join.js"></script>
  </body>
</html>
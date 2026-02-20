<!DOCTYPE html>
<html lang="en">
  <head>
	<?php require_once 'includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band is always looking for new venues to play at. Can we play at your event?">

    <title>Book a concert - Keystone Concert Band</title>

	<?php require_once 'includes/common_css.php'; ?>
	
  </head>

  <body>

	<?php require_once 'includes/nav.php'; ?>
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="bs-component">
					<div class="jumbotron">
						<h1>Book</h1>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<div class="page-header">
					<h2>Book a concert</h2>
				</div>
				The Keystone Concert Band plays a wide variety of music, and has performed in many different locations. 
				We've played at the State Capitol, nursing homes, church picnics, community events, ice cream festivals, 
				and even weddings, in addition to our regular public concerts. <br><br>
				For more information on booking the Keystone Concert Band for your event, contact us below using the form. 
				Please keep in mind that it is best to contact us at least 3-6 months before your event, 
				as we are filling dates as much as a year in advance.
				<h3>Contact Us</h3>
				<form id="frmBook" data-bs-toggle="validator">
				  <fieldset>
				    <legend></legend>
				    <div class="mb-3">
						<div class="col-lg-12">
							If you are interested in booking the band, please fill out the form below with your contact information so we can get back to you.<br>
							<i>Fields marked with an * are required.</i>
						</div>
				    </div>
				    <div class="mb-3 row">
				      <label for="txtName" class="col-lg-2 col-form-label">* Name</label>
				      <div class="col-lg-10">
				        <input type="text" class="form-control" id="txtName" name="txtName" placeholder="Name" required="true">
						<div class="form-text"></div>
				      </div>
				    </div>
				    <div class="mb-3 row">
				      <label for="txtPhone" class="col-lg-2 col-form-label">Phone Number</label>
				      <div class="col-lg-10">
				        <input type="tel" class="form-control" id="txtPhone" name="txtPhone" placeholder="Phone Number" maxlength="10">
						<div class="form-text"></div>
				      </div>
				    </div>
				    <div class="mb-3 row">
				      <label for="txtEmail" class="col-lg-2 col-form-label">* Email Address</label>
				      <div class="col-lg-10">
				        <input type="text" class="form-control" id="txtEmail" name="txtEmail" placeholder="Email Address" required="true" maxlength="255">
						<div class="form-text"></div>
				      </div>
				    </div>
				    <div class="mb-3 row">
				      <label for="txtComments" class="col-lg-2 col-form-label">* Booking Information</label>
				      <div class="col-lg-10">
				        <textarea class="form-control" rows="3" id="txtComments" name="txtComments" required="true"></textarea>
						<div class="form-text"></div>
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
	<script type="text/javascript" src="kcb-js/book.js"></script>
  </body>
</html>
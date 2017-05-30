<!DOCTYPE html>
<html lang="en">
  <head>
	<? require 'includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band is always looking for new venues to play at. Can we play at your event?">

    <title>Book a concert - Keystone Concert Band</title>

	<? require 'includes/common_css.php'; ?>
	
  </head>

  <body>

	<? require 'includes/nav.php'; ?>
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
				<form class="form-horizontal">
				  <fieldset>
				    <legend></legend>
				    <div class="form-group">
						<div class="col-lg-12">
							If you are interested in booking the band, please fill out the form below with your contact information so we can get back to you.<br>
							<i>Fields marked with an * are required.</i>
						</div>
				    </div>
				    <div class="form-group">
				      <label for="inputName" class="col-lg-2 control-label">* Name</label>
				      <div class="col-lg-10">
				        <input type="text" class="form-control" id="inputName" placeholder="Name">
				      </div>
				    </div>
				    <div class="form-group">
				      <label for="inputPhone" class="col-lg-2 control-label">Phone Number</label>
				      <div class="col-lg-10">
				        <input type="text" class="form-control" id="inputPhone" placeholder="Phone Number">
				      </div>
				    </div>
				    <div class="form-group">
				      <label for="inputEmail" class="col-lg-2 control-label">* Email Address</label>
				      <div class="col-lg-10">
				        <input type="text" class="form-control" id="inputEmail" placeholder="Email Address">
				      </div>
				    </div>
				    <div class="form-group">
				      <label for="txtComments" class="col-lg-2 control-label">* Comments/Questions</label>
				      <div class="col-lg-10">
				        <textarea class="form-control" rows="3" id="txtComments"></textarea>
				        <span class="help-block"></span>
				      </div>
				    </div>
				    <div class="form-group">
				      <div class="col-lg-10 col-lg-offset-2">
				        <button type="submit" class="btn btn-primary">Submit</button>
				      </div>
				    </div>
				  </fieldset>
				</form>
			</div>
		</div>
		<? require 'includes/footer.php'; ?>
	</div> <!-- /container -->

	<? require 'includes/common_js.php'; ?>
  </body>
</html>

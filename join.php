<!DOCTYPE html>
<html lang="en">
  <head>
	<? require 'includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band is always looking for new members! Will you join us?">

    <title>Join the band - Keystone Concert Band</title>

	<? require 'includes/common_css.php'; ?>
    <link rel="stylesheet" href="/css/font-awesome.min.css"/>
    <link rel="stylesheet" href="/css/checkboxes.min.css"/>
	
	<style type="text/css">
		.row .col-lg-12 ul li {
			margin-bottom: 10px;
		}
		</style>
  </head>

  <body>

	<? require 'includes/nav.php'; ?>
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
					<h2>Join us</h2>
				</div>
				The Keystone Concert Band is always looking for solid, dependable musicians to become part of our organization. 
				Band members range in age from 16 to 80, and have varying levels of experience. 
				If you would be interested in playing, please contact us below!
				<br><br>
				The Band rehearses each Wednesday at <a href='https://www.google.com/maps?f=q&hl=en&geocode&q=3700+Rutherford+St,+Harrisburg,+PA+17111+(Band+Practice+Facility)&sll=40.260657,-76.827071&sspn=0.005731,0.007821&ie=UTF8&t=h&z=16&iwloc=addr' target="_blank">Good Shepherd Lutheran Church in Paxtang</a>, from 7:30 until 9:30PM, and we play around 10-15 concerts per year. Musical selections vary widely, from classical and movie themes, to "old time" medleys and marches.</p>
				<h3>Frequently Asked Questions:</h3>
				<ul>
					<li><strong>How often do you practice?</strong><br>
					We practice once a week on Wednesday nights, all year long. We are typically off during 
					the holiday season, but typically we practice every week starting in February through December.
					</li>
					<li><strong>What time is practice and where is the location?</strong><br>
					Practice begins sharply at 7:30PM. Please try to arrive 10-15 minutes early to warm up and setup your stand and instrument. 
					Practice is in the basement of the
					<a href="http://maps.google.com/maps?f=q&amp;hl=en&amp;geocode=&amp;q=3700+Rutherford+St,+Harrisburg,+PA+17111+(Band+Practice+Facility)&amp;sll=40.260657,-76.827071&amp;sspn=0.005731,0.007821&amp;ie=UTF8&amp;t=h&amp;z=16&amp;iwloc=addr" target="_blank">
					Good Shepherd Lutheran Church</a>.
					</li>
					<li><strong>What do I need to bring to practice?</strong><br>
					Your instrument, a stand, and any other pieces of equipment that may be required, such as mutes or reeds.
					Chairs and percussion instruments are provided. Members are asked to keep the area around their chairs 
					clean and wipe up any water from the floor after rehearsal.
					</li>
					<li><strong>During practice, is there a break?</strong><br>
					Yes. We typically take a 5-10 minute break at 8:30 to give the band members a short break, 
					a little bit of water or soda and a chance to mingle and get to know the other musicians in the band.
					</li>
					<li><strong>Where do most of the concerts take place?</strong><br>
					Most of the concerts take place in the greater Harrisburg region including Hummelstown, Hershey, 
					Mechanicsburg, Harrisburg and Camp Hill. Take a look at our <a href="calendar.php">calendar and concerts page</a> 
					to see our current concert locations.
					</li>
					<li><strong>Does it cost anything to join?</strong><br>
					No. We are a non-profit organization and rely fully on donations and fundraisers. 
					We require no dues or fees. You will be required to buy a KCB shirt to play at our concerts 
					at a discounted rate ($10.00). We also have one or two fundraisers each year, but no one is required to participate.
					</li>
					<li><strong>Do I have to audition to join?</strong><br>
					No, but we 	do like for you to contact us via the form below. Let us know what your past experience has been.
					</li>
					<li><strong>Is there an age requirement?</strong><br>
					We ask that our younger members be at least in 9th grade to play in our band. If you are in 8th grade 
					and are considering joining we ask that your band teacher or private instructor give us a recommendation. 
					We have members still in high school and college who are playing next to our older members who have retired 
					from the work force!</li>
				</ul>
				<h3>Contact Us</h3>
				<form class="form-horizontal" id="frmJoin" data-toggle="validator">
				  <fieldset>
				    <legend></legend>
				    <div class="form-group">
						<div class="col-lg-12">
							If you are interested in joining the band, please fill out the form below with your contact information so we can get back to you.<br>
							<i>Fields marked with an * are required.</i>
						</div>
				    </div>
				    <div class="form-group">
				      <label for="txtName" class="col-lg-2 control-label">* Name</label>
				      <div class="col-lg-10">
				        <input type="text" class="form-control" id="txtName" name="txtName" placeholder="Name" required="true">
						<div class="help-block with-errors"></div>
				      </div>
				    </div>
				    <div class="form-group">
				      <label for="txtPhone" class="col-lg-2 control-label">Phone Number</label>
				      <div class="col-lg-10">
				        <input type="text" class="form-control" id="txtPhone" name="txtPhone" placeholder="Phone Number">
						<div class="help-block with-errors"></div>
				      </div>
				    </div>
				    <div class="form-group">
				      <label for="txtEmail" class="col-lg-2 control-label">* Email Address</label>
				      <div class="col-lg-10">
				        <input type="text" class="form-control" id="txtEmail" name="txtEmail" placeholder="Email Address" required="true">
						<div class="help-block with-errors"></div>
				      </div>
				    </div>
				    <div class="form-group">
				      <label for="txtEmail" class="col-lg-2 control-label">* Instrument(s) played</label>
				      <div class="col-lg-10">
						<div class="checkbox checkbox-success checkbox-inline" style="margin-left:10px;">
						    <input type="checkbox" id="baritone" value="baritone" name="chkInstrument[]">
						    <label for="baritone"> Baritone</label>
						</div>
						<div class="checkbox checkbox-success checkbox-inline">
						    <input type="checkbox" id="bassClarinet" value="bassClarinet" name="chkInstrument[]">
						    <label for="bassClarinet"> Bass Clarinet</label>
						</div>
						<div class="checkbox checkbox-success checkbox-inline">
						    <input type="checkbox" id="bassoon" value="bassoon" name="chkInstrument[]">
						    <label for="bassoon"> Bassoon</label>
						</div>
						<div class="checkbox checkbox-success checkbox-inline">
						    <input type="checkbox" id="clarinet" value="clarinet" name="chkInstrument[]">
						    <label for="clarinet"> Clarinet</label>
						</div>
						<div class="checkbox checkbox-success checkbox-inline">
						    <input type="checkbox" id="flute" value="flute" name="chkInstrument[]">
						    <label for="flute"> Flute</label>
						</div>
						<div class="checkbox checkbox-success checkbox-inline">
						    <input type="checkbox" id="frenchHorn" value="frenchHorn" name="chkInstrument[]">
						    <label for="frenchHorn"> French Horn</label>
						</div>
						<div class="checkbox checkbox-success checkbox-inline">
						    <input type="checkbox" id="saxophone" value="saxophone" name="chkInstrument[]">
						    <label for="saxophone"> Saxophone</label>
						</div>
						<div class="checkbox checkbox-success checkbox-inline">
						    <input type="checkbox" id="trombone" value="trombone" name="chkInstrument[]">
						    <label for="trombone"> Trombone</label>
						</div>
						<div class="checkbox checkbox-success checkbox-inline">
						    <input type="checkbox" id="trumpet" value="trumpet" name="chkInstrument[]">
						    <label for="trumpet"> Trumpet</label>
						</div>
						<div class="checkbox checkbox-success checkbox-inline">
						    <input type="checkbox" id="tuba" value="tuba" name="chkInstrument[]">
						    <label for="tuba"> Tuba</label>
						</div>
						<div class="checkbox checkbox-success checkbox-inline">
						    <input type="checkbox" id="percussion" value="percussion" name="chkInstrument[]">
						    <label for="percussion"> Percussion</label>
						</div>
						<div class="help-block with-errors"></div>
				      </div>
				    </div>
				    <div class="form-group">
				      <label for="txtPlayLength" class="col-lg-2 control-label">* How long have you been playing?</label>
				      <div class="col-lg-10">
				        <textarea class="form-control" rows="3" id="txtPlayLength" name="txtPlayLength" required="true"></textarea>
						<div class="help-block with-errors"></div>
				      </div>
				    </div>
				    <div class="form-group">
				      <label for="txtComments" class="col-lg-2 control-label">Additional Comments/Questions</label>
				      <div class="col-lg-10">
				        <textarea class="form-control" rows="3" id="txtComments" name="txtComments"></textarea>
						<div class="help-block with-errors"></div>
				      </div>
				    </div>
				    <div class="form-group">
				      <div class="col-lg-10 col-lg-offset-2">
				        <button type="submit" class="btn btn-primary">Submit</button>
						<div id="msgSubmit" class="h4 hidden"></div>
				      </div>
				    </div>
				  </fieldset>
				</form>
			</div>
		</div>
		<? require 'includes/footer.php'; ?>
	</div> <!-- /container -->

	<? require 'includes/common_js.php'; ?>
	<script type="text/javascript" src="/bootstrap-validator-0.11.9/js/bootstrap-validator-0.11.9.min.js"></script>
	<script type="text/javascript" src="kcb-js/join.js"></script>
  </body>
</html>

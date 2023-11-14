<!DOCTYPE html>
<html lang="en">

<head>
    <? require 'includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band is always looking for new members! Will you join us?">

    <title>Join the band - Keystone Concert Band</title>

    <? require 'includes/common_css.php'; ?>
    <link rel="stylesheet" href="/css/checkboxes.min.css" />

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
                The Keystone Concert Band is always looking for solid, dependable musicians to become part of our
                organization. We welcome all wind band musicians, but have greater need for clarinet, bass clarinet,
                euphonium, tuba, trombone, alto sax, baritone sax, trumpet, and percussion to fill out our sections. 
                Band members range from teens to folks in their 80s, with varying levels of experience. 
                If you would be interested in playing, please contact us below!<br />
                <br />
                The Band rehearses each Wednesday at <a
                    href='https://www.google.com/maps?f=q&hl=en&geocode&q=3700+Rutherford+St,+Harrisburg,+PA+17111+(Band+Practice+Facility)&sll=40.260657,-76.827071&sspn=0.005731,0.007821&ie=UTF8&t=h&z=16&iwloc=addr'
                    target="_blank">Good Shepherd Lutheran Church in Paxtang</a>, from 7:00PM until 9:00PM, and we play
                around 8-15 concerts per year. Musical selections vary widely, and include original wind
                band compositions, marches, movie music, Broadway, and popular music medleys.</p>
                <h3>Frequently Asked Questions:</h3>
                <ul>
                    <li><strong>How often do you practice?</strong><br />
                        We rehearse weekly, from early February through mid-December. We are off the last half of
                        December and all of January.
                    </li>
                    <li><strong>What time is practice and where is the location?</strong><br />
                        We rehearse Wednesdays from 7:00PM to 9:00PM. Please arrive 15 minutes early to warm up and
                        prepare for practice. Rehearsal is in the community room (downstairs) of the <a
                            href="http://maps.google.com/maps?f=q&amp;hl=en&amp;geocode=&amp;q=3700+Rutherford+St,+Harrisburg,+PA+17111+(Band+Practice+Facility)&amp;sll=40.260657,-76.827071&amp;sspn=0.005731,0.007821&amp;ie=UTF8&amp;t=h&amp;z=16&amp;iwloc=addr"
                            target="_blank">Good
                            Shepherd Lutheran Church</a> at 3700 Rutherford Street in the Paxtang area of Harrisburg.
                    </li>
                    <li><strong>Where do I park for rehearsal?</strong><br />
                        You may park behind the church in the parking lot off of Montour St. There is also
                        plenty of on street parking along Montour, Wilhelm and Rutherford Streets.<br />
                        <img src="images/parking-good-shepherd.png" width="579" class="img-responsive" />
                    </li>
                    <li><strong>What do I need to bring?</strong><br />
                        Your instrument, a music stand, and any other pieces of equipment that may be required, such as
                        mutes, reeds, or drumsticks. Chairs and percussion instruments are provided. Members are asked
                        to keep the area around their chairs clean and wipe up any water from the floor after rehearsal.
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
                <form class="form-horizontal" id="frmJoin" data-toggle="validator">
                    <fieldset>
                        <legend></legend>
                        <div class="form-group">
                            <div class="col-lg-12">
                                If you are interested in joining the band, please fill out the form below with your
                                contact information so we can get back to you.<br />
                                <em>Fields marked with an * are required.</em>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="txtName" class="col-lg-2 control-label">* Name</label>
                            <div class="col-lg-10">
                                <input type="text" class="form-control" id="txtName" name="txtName" placeholder="Name"
                                    required="true">
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="txtPhone" class="col-lg-2 control-label">Phone Number</label>
                            <div class="col-lg-10">
                                <input type="tel" class="form-control" id="txtPhone" name="txtPhone"
                                    placeholder="Phone Number" data-minlength="10" maxlength="10">
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="txtEmail" class="col-lg-2 control-label">* Email Address</label>
                            <div class="col-lg-10">
                                <input type="text" class="form-control" id="txtEmail" name="txtEmail"
                                    placeholder="Email Address" required="true">
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
                                    <input type="checkbox" id="bassClarinet" value="bassClarinet"
                                        name="chkInstrument[]">
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
                            <label for="txtPlayLength" class="col-lg-2 control-label">* How long have you been
                                playing?</label>
                            <div class="col-lg-10">
                                <textarea class="form-control" rows="3" id="txtPlayLength" name="txtPlayLength"
                                    required="true"></textarea>
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="txtComments" class="col-lg-2 control-label">Additional
                                Comments/Questions</label>
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
    <script type="text/javascript" src="/3rd-party/bootstrap-validator-0.11.9/js/bootstrap-validator-0.11.9.min.js">
    </script>
    <script type="text/javascript" src="kcb-js/join.js"></script>
</body>

</html>

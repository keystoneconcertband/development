<!DOCTYPE html>
<html lang="en">
  <head>
	<? require 'includes/common_meta.php'; ?>
    <meta name="description" content="The leaders of the Keystone Concert Band. Our Conductors, Donna Deaven and John Hope.">

    <title>Conductors - Keystone Concert Band</title>

	<? require 'includes/common_css.php'; ?>
	
	<style type="text/css">
		.row .col-lg-12 ul li {
			margin-bottom: 10px;
		}
		<? // Need to add style to add padding to bottom of LI's and remove the double BR's. ?>
		</style>
  </head>

  <body>

	<? require 'includes/nav.php'; ?>
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="bs-component">
					<div class="jumbotron">
						<h1>Conductors</h1>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<div class="page-header">
					<h2>Conductors</h2>
				</div>
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-4">
							<img alt="KCB Conductors - Chris Mack, Donna Deaven" src="images/conductors-1x.png" srcset="images/conductors-2021.png" class="img-responsive"> 
							<p><small>From left to right: Chris Mack, Donna Deaven</small></p>
						</div>
						<div class="col-md-8">
							<ul class="nav nav-pills">
							  <li class="active">
							    <a data-toggle="tab" href="#donna">Donna Deaven</a>
							  </li>
							  <li>
							    <a data-toggle="tab" href="#chris">Chris Mack</a>
							  </li>
							</ul>
							<div id="conductorsTabContent" class="tab-content">
							  <div class="tab-pane fade active in" id="donna">
							    <h3>Donna Deaven, Principal Conductor</h3>
								Donna J. Deaven has more than 28 years of experience in conducting bands, 
								orchestras, and small ensembles, and 31 years of experience as a private 
								teacher and professional musician.
								<br><br>
								Ms. Deaven graduated with a Bachelor of Science degree in Music Education 
								and a Master of Music degree in saxophone performance from West Chester University, 
								where she studied with Ted Hegvik.
								<br><br>
								Ms. Deaven's previous affiliations include the New 
								Singer Band of Mechanicsburg, the West Shore Symphony Orchestra, the 
								Hershey Symphony Orchestra, Harrisburg Area Community College, the Harrisburg 
								Community Theater, York Little Theater, the Assistant Conductor of the New Cumberland Town Band,
								Keystone Saxophone Quartet, Flutes of Fancy Flute Ensemble, Wednesday 
								Club, Harrisburg Chamber Ensemble, and "Donna and the Daytimers.".
								<br><br>
								In addition to conducting, Ms. Deaven's education and experience include studying and 
								performing on the saxophone, flute, clarinet and percussion. She also maintains 
								a private woodwind teaching studio in Mechanicsburg.
							  </div>
							  <div class="tab-pane fade" id="chris">
			 					<h3>Chris Mack, Assistant Conductor</h3>
								Christopher Mack began studying piano at age 5 and percussion at age 10. He currently holds a 
								Bachelors of Music degree in Music Education from James Madison University and a Masters of Music 
								degree in Wind Band Conducting from Messiah College. He attended Cumberland Valley High School 
								and was an active musician in the marching band, indoor drumline, wind band, concert band, 
								symphony orchestra, choir, and musical theater. Outside of high school, Chris participated in the 
								Studio One Percussion Ensemble under the direction of Dan Delong and the Harrisburg Youth Symphony 
								Orchestra under the direction of Gregory Woodbridge. He also had the opportunity to tour with American 
								Music Abroad as a percussionist in France, Switzerland, Austria, and Germany. As a high school senior, 
								Chris received the John Philip Sousa Award for Outstanding Musicianship.
								<br><br>
								As an undergraduate music major, Chris joined the Keystone Concert Band as a percussionist in 2012 and 
								began practicing conducting with the band in preparation for student teaching in Virginia. During his 
								time at James Madison University, Chris conducted the JMU Percussion Ensemble and met professional 
								percussionists Tommy Igo, Leigh Howard Stevens, Gordon Stout, and Kevin Bobo, the later of whom he 
								performed with. During his graduate studies, Chris would lead through the first movement of Gordon 
								Jacob's An Original Suite with the Greater Harrisburg Concert Band as part of a Conductor's Symposium 
								at Messiah College in the summer of 2019.
								<br><br>
								Chris continues to lead the Keystone Concert Band as the third conductor alongside Donna and John.
							  </div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<? require 'includes/footer.php'; ?>
	</div> <!-- /container -->

	<? require 'includes/common_js.php'; ?>
  </body>
</html>

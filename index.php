<?php
	include_once('includes/class/kcbPublic.class.php');
	global $homepage;
	$homepage = new KCBPublic();
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<?php require_once 'includes/common_meta.php';	?>
		<meta name="description" content="Keystone Concert Band is an organization to foster, promote, and increase the musical knowledge and appreciation of the general public by operating and maintaining a concert band and by presenting performances of music.">
		<title>Keystone Concert Band</title>
		<?php require_once 'includes/common_css.php'; ?>
	</head>
	<body>
		<div id="fb-root"></div>
		<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v16.0&appId=183258391082442&autoLogAppEvents=1" nonce="XBkyxwt4"></script>
		<?php require_once 'includes/nav.php'; ?>
		<div id="myCarousel" class="carousel slide" data-ride="carousel">
			<!-- Indicators -->
			<ol class="carousel-indicators">
				<li data-target="#myCarousel" data-slide-to="0" class="active"></li>
				<li data-target="#myCarousel" data-slide-to="1"></li>
				<li data-target="#myCarousel" data-slide-to="2"></li>
			</ol>
			<div class="carousel-inner" role="listbox">
				<div class="active item">
					<div class="fill" style="background-image:url('images/slide6.png');">
						<div class="container">
							<div class="carousel-caption">
								<h1>We need you</h1>
								<p>As a 501(c)3 organization, we rely on donations to continue performing</p>
								<p><a class="btn btn-lg btn-primary" href="donate.php" role="button">Donate today</a></p>
							</div>
						</div>
					</div>
				</div>
				<div class="item">
					<div class="fill" style="background-image:url('images/slide9.png');">
						<div class="container">
							<div class="carousel-caption">
								<h1>Play with us</h1>
								<p>Been a few years since you picked up your instrument? Play once again</p>
								<p><a class="btn btn-lg btn-primary" href="join.php" role="button">Join Us</a></p>
							</div>
						</div>
					</div>
				</div>
				<div class="item">
					<div class="fill" style="background-image:url('images/slide8.png');">
						<div class="container">
							<div class="carousel-caption">
								<h1>We can play for you</h1>
								<p>We can play your event, big or small. Just give us a call</p>
								<p><a class="btn btn-lg btn-primary" href="book.php" role="button">Book Us</a></p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
				<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
				<span class="sr-only">Previous</span>
			</a>
			<a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
				<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
				<span class="sr-only">Next</span>
			</a>
		</div><!-- /.carousel -->
		<?php
			$messages = $homepage->getHomepageMessages();
			
			foreach($messages as $msg) {
				if($msg['message_type'] == "Regular") {
					echo('<div class="alert alert-dismissible alert-warning"><button type="button" class="close" data-dismiss="alert">&times;</button>
						<h4>'. $msg['title'] .'</h4>
						<p>' . $msg['message'] . '</p></div>');
				}
				else {
					echo('<div class="alert alert-dismissible alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>
						<h4>'. $msg['title'] .'</h4>
						<p>' . $msg['message'] . '</p></div>');
				}
			}
		?>
		<div class="container marketing">
			<!-- Three columns of text below the carousel -->
			<div class="row">
				<div class="col-lg-4">
					<img class="img-circle" src="images/logo_concert.jpg" alt="Upcoming Concert Image" width="140" height="140">
					<h2>Upcoming Concert</h2>
						<?php
							$concert = $homepage->getCurrentConcert();
											
							if(!$concert) {
								echo "<h4>There are no upcoming concerts.</h4>Our concert series is done for the season. Please check back again in early Spring to see our new concert schedule!";
							}
							else {
								$location = "https://maps.googleapis.com/maps/api/staticmap?key=AIzaSyDIg8S7UeGSot2PtUc7Ufd7pRitbNnl4w4&center=" . urlencode($concert['address']) . "&zoom=14&size=340x200&sensor=false&markers=color:red|" . urlencode($concert['address']);
								$today = date("Y-m-d");
								$begin = date('Y-m-d', strtotime($concert['concertBegin']));
							
								if ($today == $begin) {
									$begin = "Today";
								}
								else {
									$begin = date('D, M d', strtotime($concert['concertBegin']));
								}
				
								echo "<h4>" . $begin . " at " . date('g:iA', strtotime($concert['concertBegin'])) . "</h4>";			
								echo "<h4><a href='https://maps.google.com/maps?q=" . urlencode($concert['address']) . "' target='_blank' style='border-bottom:none;'>" . $concert['Title'] . "</a></h4>";
								echo "<img src='" . $location . "' alt='Google Maps location of the concert.' usemap='#map' id='map'>";
								echo "<map name='map'><area shape='circle' coords='250,80,15' href='https://maps.google.com/maps?q=" . urlencode($concert['address']) . "' target='_blank' alt='Location of concert on the map'></map>";
							}
						?>
					<p><a class="btn btn-default" href="concerts.php" role="button">View more &raquo;</a></p>
					<?php if($concert) { ?>
					<a href="#show" style="font-size: 8px;" onclick="showAlerts()">Band Member?</a>
						<div id="bandAlerts" style="display:none">
							<?php
								if ($concert['pants'] == 0) {
										echo "<div><span class='glyphicon glyphicon-alert' aria-hidden='true'></span> This is a black pants concert</div>";
								}
								elseif ($concert['pants'] == 1) {
									echo "<div><span class='glyphicon glyphicon-alert' aria-hidden='true'></span> This is a tan pants concert</div>";
								}
								if ($concert['chair'] == 1) {
									echo "<div><span class='glyphicon glyphicon-alert' aria-hidden='true'></span> A chair is required at this concert</div>";
								}
							?>
						</div>
					<?php } //End if concert ?>
				</div><!-- /.col-lg- -->
				<div class="col-lg-4">
					<img class="img-circle" src="images/logo_facebook.png" alt="Facebook Image" width="140" height="140">
					<h2>Facebook</h2>
					<p>Join our Facebook page for the latest information and upcoming concerts.</p>
					<div class="fb-page" data-href="https://www.facebook.com/keystoneconcertband/" data-tabs="timeline" data-width="" data-height="" data-small-header="true" data-adapt-container-width="true" data-hide-cover="true" data-show-facepile="true">
						<blockquote cite="https://www.facebook.com/keystoneconcertband/" class="fb-xfbml-parse-ignore">
							<a href="https://www.facebook.com/keystoneconcertband/">Keystone Concert Band</a>
						</blockquote>
					</div>
				</div><!-- /.col-lg-4 -->
				<div class="col-lg-4">
					<img class="img" src="images/donate-2023.png" alt="Donate" width="140" height="140">
					<h2>Donate</h2>
					<p>As a 501(c)3 organization, we rely on donations to perform!
					</p>
					<p><a class="btn btn-default" href="donate.php" role="button">View details &raquo;</a></p>
				</div><!-- /.col-lg-4 -->
			</div><!-- /.row -->
			<?php require_once 'includes/footer.php'; ?>
		</div><!-- /.container -->

		<?php require_once 'includes/common_js.php'; ?>
		<script>
			function showAlerts()
			{
				$("#bandAlerts").toggle();
			}
		</script>
	</body>
</html>
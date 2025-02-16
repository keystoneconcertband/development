<?php 	
	include_once('includes/class/kcbPublic.class.php');
	global $cncrts;
	$cncrts = new KCBPublic();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
	<?php require_once 'includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band concert schedule.">

    <title>Concerts - Keystone Concert Band</title>

	<?php require_once 'includes/common_css.php'; ?>

  </head>

  <body>

	<?php require_once 'includes/nav.php'; ?>
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="bs-component">
					<div class="jumbotron">
						<h1>Concerts</h1>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<div class="page-header">
					<h2>Calendar</h2>
				</div>
				<iframe title="Calendar" height="600" src="https://www.google.com/calendar/embed?showTitle=0&amp;showCalendars=0&amp;showTz=0&amp;height=600&amp;wkst=1&amp;bgcolor=%23FFFFFF&amp;src=keystoneconcertband%40gmail.com&amp;color=%23A32929&amp;ctz=America%2FNew_York" style="border-width: 0" width="100%">
				</iframe>
				<div class="page-header">
					<h2>Concert Schedule</h2>
				</div>
<!--
				<div class='alert alert-info'><strong>More concerts still yet to come!</strong><br />Continue to check back again throughout the season to see any additional concerts added to the lineup!</div>
-->
				<?php
					$rowNbr = 1;
					$concerts = $cncrts->getConcertSchedule();
						
					if(!$concerts) {
						echo "<div class='alert alert-info'><strong>Looks like we don't have any concerts posted yet.</strong><br />Check back again later in the Spring!</div>";
					}
					else {
						foreach ($concerts as $concert) {		
							$disabled = '';
							$locationSmall = "https://maps.googleapis.com/maps/api/staticmap?key=AIzaSyDIg8S7UeGSot2PtUc7Ufd7pRitbNnl4w4&center=" . urlencode($concert['address']) ."&zoom=11&size=340x200&sensor=false&markers=color:red|" . urlencode($concert['address']);
							$locationMed = "https://maps.googleapis.com/maps/api/staticmap?key=AIzaSyDIg8S7UeGSot2PtUc7Ufd7pRitbNnl4w4&center=" . urlencode($concert['address']) ."&zoom=13&size=500x300&sensor=false&markers=color:red|" . urlencode($concert['address']);
							$locationLarge = "https://maps.googleapis.com/maps/api/staticmap?key=AIzaSyDIg8S7UeGSot2PtUc7Ufd7pRitbNnl4w4&center=" . urlencode($concert['address']) ."&zoom=14&size=600x400&sensor=false&markers=color:red|" . urlencode($concert['address']);
							$today = date("Y-m-d");
							$begin = date('Y-m-d', strtotime($concert['concertBegin']));
							
							if($today == $begin) {
								$begin = "Today";
							}
							else {
								$begin = date('l, M j', strtotime($concert['concertBegin']));
							}
							
							if (date("Y-m-d") > date('Y-m-d', strtotime($concert['concertBegin']))) {
								$disabled = 'text-muted';
							}
							
							echo "<div class='panel-group' id='accordion'>\n";
							echo "<div class='panel panel-default' id='panel" . $rowNbr . "'>\n";
							echo "  <div class='panel-heading " . $disabled . "'>\n";
							echo "	  <div class='panel-title'>";
							echo "      <a data-toggle='collapse' data-target='#collapse" . $rowNbr . "' href='#collapse'>\n";
							echo 			$concert['Title'];
							echo "      </a>\n";
							echo "    </div>\n";
							echo "  </div>\n";
							echo "  <div id='collapse" . $rowNbr . "' class='panel-collapse collapse ";
							
							if (!$disabled) { 
								echo "in"; // starts the panel in an open state
							}
							
							echo "'>\n";
							echo "    <div class='panel-body'>\n";
							echo "		<p class='" . $disabled . "'>\n";
							echo "      <img class='bigMap " . $disabled . "' src='" .$locationLarge . "' alt='Google Maps location of the concert.' usemap='#map' id='map' />\n";
							echo "      <h3 style='margin-top:0px;'>" . $concert['Title'] . "</h3><h4> " . $begin . " at " . date('g:iA', strtotime($concert['concertBegin'])) . ".</h4>\n";
							echo "        <a class='" . $disabled . "' href='https://maps.google.com/maps?q=" . urlencode($concert['address']) . "' target='_blank' style='border-bottom:none;'>" . $concert['address'] . "</a>\n";
							echo "      </p>\n";								
							echo "      <img class='medMap " . $disabled . "' src='" .$locationMed . "' alt='Google Maps location of the concert.' usemap='#map' id='map' />\n";
							echo "      <img class='smallMap " . $disabled . "' src='" .$locationSmall . "' alt='Google Maps location of the concert.' usemap='#map' id='map' />\n";
							echo "      <map class='" . $disabled . "' name='map'><area shape='circle' coords='250,80,15' href='https://maps.google.com/maps?q=" . urlencode($concert['address']) . "' target='_blank'></map>\n";
							echo "      <div class='band-member-notice well well-sm' style='max-width:340px;'>";
							echo "		  <h4>Band Members: </h4>";
							echo "			<ul class='list-group'>";
			  				if ($concert['pants'] == 0) {
								echo "		  <li class='list-group-item'><span class='glyphicon glyphicon-alert' aria-hidden='true'></span> This is a black pants concert</li>\n";
							}
							elseif ($concert['pants'] == 1) {
								echo "		  <li class='list-group-item'><span class='glyphicon glyphicon-alert' aria-hidden='true'></span> This is a tan pants concert</li>\n";
							}
							if ($concert['chair'] == 1) {
								echo "		  <li class='list-group-item'><span class='glyphicon glyphicon-alert' aria-hidden='true'></span> A chair is required for this concert</li>\n";
							}
							echo "		</div>";
							echo "    </div>\n";								
							echo "  </div>\n";
							echo "</div>\n";
							
							$rowNbr++;
						}
					}
				?>
			</div>
		</div>
		<?php require_once 'includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require_once 'includes/common_js.php'; ?>
  </body>
</html>

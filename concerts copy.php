<!DOCTYPE html>
<html lang="en">
  <head>
	<? include_once('includes/class/kcbBase.class.php');new kcbBase();?>
	<? include_once('includes/class/concerts.class.php');?>
	<? require 'includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band concert schedule.">

    <title>Concerts - Keystone Concert Band</title>

	<? require 'includes/common_css.php'; ?>

  </head>

  <body>

	<? require 'includes/nav.php'; ?>
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
				<iframe frameborder="0" height="600" scrolling="no" src="https://www.google.com/calendar/embed?showTitle=0&amp;showCalendars=0&amp;showTz=0&amp;height=600&amp;wkst=1&amp;bgcolor=%23FFFFFF&amp;src=keystoneconcertband%40gmail.com&amp;color=%23A32929&amp;ctz=America%2FNew_York" style="border-width: 0" width="100%">
				</iframe>
				<div class="page-header">
					<h2>Concert Schedule</h2>
				</div>
				<?
					include('db/db_config.php');
					
					$disabled = '';
					$rowNbr = 1;

					$sql = 'SELECT concertBegin, concertEnd, Title, pants, chair, address FROM KCB_Schedule WHERE deleted IS NULL AND year(concertBegin) = year(CURRENT_TIMESTAMP) ORDER BY concertBegin' or die("Error in the consult.." . mysqli_error($db));
	
					$result = $conn->query($sql);
	
					if($result === false) {
					  trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
					}
		
					while($row = $result->fetch_assoc()) {
						$disabled = '';
						$locationSmall = "http://maps.googleapis.com/maps/api/staticmap?center=" . urlencode($row['address']) ."&zoom=11&size=340x200&sensor=false&markers=color:red|" . urlencode($row['address']);
						$locationMed = "http://maps.googleapis.com/maps/api/staticmap?center=" . urlencode($row['address']) ."&zoom=13&size=500x300&sensor=false&markers=color:red|" . urlencode($row['address']);
						$locationLarge = "http://maps.googleapis.com/maps/api/staticmap?center=" . urlencode($row['address']) ."&zoom=14&size=600x400&sensor=false&markers=color:red|" . urlencode($row['address']);
						$today = date("Y-m-d");
						$concert = date('Y-m-d', strtotime($row['concertBegin']));
						
						if($today == $concert) {
							$concert = "Today";
						}
						else {
							$concert = date('l, M j', strtotime($row['concertBegin']));
						}
						
						if (date("Y-m-d") > date('Y-m-d', strtotime($row['concertBegin']))) {
							$disabled = 'text-muted';
						}
						
						echo "<div class='panel-group' id='accordion'>\n";
						echo "<div class='panel panel-default' id='panel" . $rowNbr . "'>\n";
						echo "  <div class='panel-heading " . $disabled . "'>\n";
						echo "	  <div class='panel-title'>";
						echo "      <a data-toggle='collapse' data-target='#collapse" . $rowNbr . "' href='#collapse'>\n";
						echo 			$row['Title'];
						echo "      </a>\n";
						echo "    </div>\n";
						echo "  </div>\n";
						echo "  <div id='collapse" . $rowNbr . "' class='panel-collapse collapse ";
						
						if ($disabled == '') { 
							echo "in"; // starts the panel in an open state
						}
						
						echo "'>\n";
						echo "    <div class='panel-body'>\n";
						echo "		<p class='" . $disabled . "'>\n";
						echo "      <img class='bigMap " . $disabled . "' src='" .$locationLarge . "' alt='Google Maps location of the concert.' usemap='#map' id='map' />\n";
						echo "      <h3 style='margin-top:0px;'>" . $concert . " from " . date('g:iA', strtotime($row['concertBegin'])) . " to " . date('g:iA', strtotime($row['concertEnd'])) . "</h3>\n";
						echo "        <a class='" . $disabled . "' href='http://maps.google.com/maps?q=" . urlencode($row['address']) . "' target='_blank' style='border-bottom:none;'>" . $row['address'] . "</a>\n";
						echo "      </p>\n";								
						echo "      <img class='medMap " . $disabled . "' src='" .$locationMed . "' alt='Google Maps location of the concert.' usemap='#map' id='map' />\n";
						echo "      <img class='smallMap " . $disabled . "' src='" .$locationSmall . "' alt='Google Maps location of the concert.' usemap='#map' id='map' />\n";
						echo "      <map class='" . $disabled . "' name='map'><area shape='circle' coords='250,80,15' href='http://maps.google.com/maps?q=" . urlencode($row['address']) . "' target='_blank'></map>\n";
						echo "      <div class='band-member-notice well well-sm' style='max-width:340px;'>";
						echo "		  <h4>Band Members: </h4>";
						echo "			<ul class='list-group'>";
		  				if ($row['pants'] == 0) {
							echo "		  <li class='list-group-item'><span class='glyphicon glyphicon-alert' aria-hidden='true'></span> This is a black pants concert</li>\n";
						}
						elseif ($row['pants'] == 1) {
							echo "		  <li class='list-group-item'><span class='glyphicon glyphicon-alert' aria-hidden='true'></span> This is a tan pants concert</li>\n";
						}
						if ($row['chair'] == 1) {
							echo "		  <li class='list-group-item'><span class='glyphicon glyphicon-alert' aria-hidden='true'></span> A chair is required for this concert</li>\n";
						}
						echo "		</div>";
						echo "    </div>\n";								
						echo "  </div>\n";
						echo "</div>\n";
						
						$rowNbr++;
					}
					if($result->num_rows == 0) {
						echo "Looks like we don't have any concerts posted yet. Check back again later!";
					}
				?>
			</div>
		</div>
		<? require 'includes/footer.php'; ?>
	</div> <!-- /container -->

	<? require 'includes/common_js.php'; ?>
  </body>
</html>

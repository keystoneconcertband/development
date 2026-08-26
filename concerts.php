<?php 	
	require_once 'src/Shared/Classes/kcbPublic.class.php';
	global $cncrts;
	$cncrts = new KCBPublic();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once 'templates/partials/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band concert schedule.">

    <title>Concerts - Keystone Concert Band</title>

    <?php require_once 'templates/partials/common_css.php'; ?>
</head>

<body>

    <?php require_once 'templates/partials/nav.php'; ?>
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="bs-component">
                    <div class="jumbotron">
                        <h1 class="display-5">Concerts</h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="page-header">
                    <h2>Calendar</h2>
                </div>
                <iframe title="Calendar" height="600"
                    src="https://www.google.com/calendar/embed?showTitle=0&amp;showCalendars=0&amp;showTz=0&amp;height=600&amp;wkst=1&amp;bgcolor=%23FFFFFF&amp;src=keystoneconcertband%40gmail.com&amp;color=%23A32929&amp;ctz=America%2FNew_York"
                    style="border-width: 0" width="100%">
                </iframe>
                <div class="page-header">
                    <h2>Concert Schedule</h2>
                </div>
                <?php
					$rowNbr = 1;
					$concerts = $cncrts->getConcertSchedule();
						
					if(!$concerts) {
						echo "<div class='alert alert-info'><strong>Looks like we don't have any concerts posted yet.</strong><br />Check back again later in the Spring!</div>";
					}
					else {
						echo "<div class='accordion' id='accordion'>\n";
						foreach ($concerts as $concert) {		
							$disabled = '';
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
							
							echo "  <div class='accordion-item'>\n";
							echo "    <h2 class='accordion-header'>\n";
							if($disabled) {
								echo "  <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#collapse" . $rowNbr . "' aria-expanded='false' aria-controls='collapse" . $rowNbr . "'>" . $concert['Title'] . "</button>\n";
							}
							else {
								echo "  <button class='accordion-button' type='button' data-bs-toggle='collapse' data-bs-target='#collapse" . $rowNbr . "' aria-expanded='true' aria-controls='collapse" . $rowNbr . "'>" . $concert['Title'] . "</button>\n";
							}
							echo "    </h2>\n";
								echo "    <div id='collapse" . $rowNbr . "' class='accordion-collapse collapse" . ($disabled ? '' : ' show') . "'>";
								echo "      <div class='accordion-body'>";
							echo "		    <p class='" . $disabled . "'>\n";
							echo "            <h3 style='margin-top:0px;'>" . $concert['Title'] . "</h3><h4> " . $begin . " at " . date('g:iA', strtotime($concert['concertBegin'])) . ".</h4>\n";
							echo "          </p>\n";
							echo "		    <div style='width: 100%'><iframe width='100%' height='340' frameborder='0' scrolling='no' marginheight='0' marginwidth='0' src='https://maps.google.com/maps?width=100%25&amp;height=340&amp;hl=en&amp;q=" . urlencode($concert['address']) ."&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed'></iframe></div>";
							echo "          <div class='alert alert-light text-start mx-auto d-inline-block fs-6' role='alert' style='max-width:340px;'>";
							echo "		        <h5 class='card-title'>Band Members</h5>";
							echo "			    <ul class='mb-0 list-unstyled ps-0'>";
			  				if ($concert['pants'] == 0) {
								echo "		      <li class='list-group-item'><span class='fa-solid fa-square-check me-2 mt-1' aria-hidden='true'></span> Wear <strong>black pants</strong> for this concert</li>\n";
							}
							elseif ($concert['pants'] == 1) {
								echo "		        <li class='list-group-item'><span class='fa-regular fa-square-check me-2 mt-1' aria-hidden='true'></span> Wear <strong>tan pants</strong> for this concert</li>\n";
							}
							if ($concert['chair'] == 1) {
								echo "		        <li class='list-group-item'><span class='fa-solid fa-chair me-2 mt-1' aria-hidden='true'></span> A chair is required for this concert</li>\n";
							}
							echo "			    </ul>";
							echo "		    </div>"; // end band-member-notice
								echo "      </div>\n"; // end accordion-body
							echo "    </div>\n"; // end collapse
							echo "  </div>\n"; // end accordion-item
							
							$rowNbr++;
						}
						echo "</div>\n"; // end accordion
					}
				?>
            </div>
        </div>
        <?php require_once 'templates/partials/footer.php'; ?>
    </div> <!-- /container -->

    <?php require_once 'templates/partials/common_js.php'; ?>
</body>

</html>

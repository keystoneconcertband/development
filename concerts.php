<?php 	
	include_once('includes/class/kcbPublic.class.php');
	global $cncrts;
	$cncrts = new KCBPublic();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
	<?php require_once 'includes/common_meta.php'; ?>
    <meta name="description" content="Upcoming concerts and performances by the Keystone Concert Band.">

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
						echo "<div class='accordion' id='concertAccordion'>\n";
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
							echo "    <h2 class='accordion-header' id='heading" . $rowNbr . "'>\n";
							echo "      <button class='accordion-button " . ($disabled ? 'disabled' : '') . "' type='button' data-bs-toggle='collapse' data-bs-target='#collapse" . $rowNbr . "' aria-expanded='true' aria-controls='collapse" . $rowNbr . "'>\n";
							echo "        " . $concert['Title'] . "\n";
							echo "      </button>\n";
							echo "    </h2>\n";
							echo "    <div id='collapse" . $rowNbr . "' class='accordion-collapse collapse " . (!$disabled ? 'show' : '') . "' aria-labelledby='heading" . $rowNbr . "' data-bs-parent='#concertAccordion'>\n";
							echo "      <div class='accordion-body'>\n";
							echo "        <p class='" . $disabled . "'>\n";
							echo "          <h3 style='margin-top:0px;'>" . $concert['Title'] . "</h3>\n";
							echo "          <h4>" . $begin . " at " . date('g:iA', strtotime($concert['concertBegin'])) . "</h4>\n";
							echo "          <a class='" . $disabled . "' href='https://maps.google.com/maps?q=" . urlencode($concert['address']) . "' target='_blank' style='border-bottom:none;'>" . $concert['address'] . "</a>\n";
							echo "        </p>\n";
							echo "        <div style='width: 100%'><iframe width='100%' height='340' frameborder='0' scrolling='no' marginheight='0' marginwidth='0' src='https://maps.google.com/maps?width=100%25&amp;height=340&amp;hl=en&amp;q=" . urlencode($concert['address']) ."&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed'></iframe></div>";
							echo "        <div class='band-member-notice alert alert-info' style='margin-top: 15px;'>";
							echo "          <p><strong>Band Members:</strong> Please arrive 30 minutes early for setup.</p>";
							echo "        </div>\n";
							echo "      </div>\n";
							echo "    </div>\n";
							echo "  </div>\n";
							
							$rowNbr++;
						}
						echo "</div>\n";
					}
				?>
			</div>
		</div>
		<?php require_once 'includes/footer.php'; ?>
	</div> <!-- /container -->

	<?php require_once 'includes/common_js.php'; ?>
  </body>
</html>
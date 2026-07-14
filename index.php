<?php
	include_once('includes/class/kcbPublic.class.php');
	global $homepage;
	$homepage = new KCBPublic();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once 'includes/common_meta.php';	?>
    <meta name="description"
        content="Keystone Concert Band is an organization to foster, promote, and increase the musical knowledge and appreciation of the general public by operating and maintaining a concert band and by presenting performances of music.">
    <title>Keystone Concert Band</title>
    <?php require_once 'includes/common_css.php'; ?>
</head>

<body>
    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous"
        src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v22.0&appId=183258391082442"></script>
    <?php require_once 'includes/nav.php'; ?>
    <div id="myCarousel" class="carousel slide" data-bs-ride="carousel">
        <!-- Indicators -->
        <div class="carousel-indicators">
            <button data-bs-target="#myCarousel" data-bs-slide-to="0" class="active" aria-current="true"
                aria-label="Slide 1"></button>
            <button data-bs-target="#myCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button data-bs-target="#myCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner" role="listbox">
            <div class="carousel-item active">
                <img src='images/slide6.png' class="d-block w-100" alt="...">
                <div class="carousel-caption d-block">
                    <h1>We need you</h1>
                    <p>As a 501(c)3 organization, we rely on donations to continue performing</p>
                    <p><a class="btn btn-lg btn-primary" href="donate.php" role="button">Donate today</a></p>
                </div>
            </div>
            <div class="carousel-item">
                <img src='images/slide9.png' class="d-block w-100" alt="...">
                <div class="carousel-caption d-block">
                    <h1>Play with us</h1>
                    <p>Been a few years since you picked up your instrument? Play once again</p>
                    <p><a class="btn btn-lg btn-primary" href="join.php" role="button">Join Us</a></p>
                </div>
            </div>
            <div class="carousel-item">
                <img src='images/slide8.png' class="d-block w-100" alt="...">
                <div class="carousel-caption d-block">
                    <h1>We can play for you</h1>
                    <p>We can play your event, big or small. Just give us a call</p>
                    <p><a class="btn btn-lg btn-primary" href="book.php" role="button">Book Us</a></p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#myCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#myCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    <?php
		$messages = $homepage->getHomepageMessages();
		
		foreach($messages as $msg) {
			if($msg['message_type'] == "Regular") {
				echo('<div class="alert alert-warning alert-dismissible fade show" role="alert">'
					. '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
					. '<h4><i class="fa-solid fa-circle-exclamation me-2 mt-1" aria-hidden="true"></i>' . $msg['title'] . '</h4>'
					. $msg['message'] . '</div>');
			}
			else {
				echo('<div class="alert alert-danger alert-dismissible fade show" role="alert">'
					. '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
					. '<h4><i class="fa-solid fa-bullhorn me-2 mt-1" aria-hidden="true"></i>' . $msg['title'] . '</h4>'
					. $msg['message'] . '</div>');
			}
		}
	?>
    <div class="container text-center">
        <!-- Three columns of text below the carousel -->
        <div class="row g-4 align-items-stretch homepage-feature-row">
            <div class="col-lg-4 homepage-feature">
                <img class="rounded-pill" src="images/logo_concert.jpg" alt="Upcoming Concert Image" width="140"
                    height="140">
                <h2>Upcoming Concert</h2>
                <?php
					$concert = $homepage->getCurrentConcert();
									
					if(!$concert) {
						echo "<h4>There are no upcoming concerts.</h4>Our concert series is done for the season. Please check back again in early Spring to see our new concert schedule!";
					}
					else {
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
						echo "<div style='width: 100%'><iframe width='100%' height='340' frameborder='0' scrolling='no' marginheight='0' marginwidth='0' src='https://maps.google.com/maps?width=100%25&amp;height=340&amp;hl=en&amp;q=" . urlencode($concert['address']) ."&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed'></iframe></div>";
					}
				?>
                <p><a class="btn btn-light" href="concerts.php" role="button">View more &raquo;</a></p>
                <?php if($concert) { ?>
                <a href="#" style="font-size: 0.9rem;" role="button" onclick="showAlerts(); return false;">Band
                    Member?</a>
                <div id="bandRequirements" class="d-none">
                    <?php
						$requirements = array();
						if (isset($concert['pants'])) {
							if ($concert['pants'] == 0) {
								$requirements[] = array('type' => 'pants', 'text' => 'This is a black pants concert');
							} elseif ($concert['pants'] == 1) {
								$requirements[] = array('type' => 'pants', 'text' => 'This is a tan pants concert');
							}
						}
						if (!empty($concert['chair']) && $concert['chair'] == 1) {
							$requirements[] = array('type' => 'chair', 'text' => 'A chair is required at this concert');
						}

						if (count($requirements) > 0) {
							// Font Awesome icons (require FA6 stylesheet in common_css.php)
							$pantsIcon = '<i class="fa-solid fa-square-check me-2 mt-1" aria-hidden="true"></i>';
							$chairIcon = '<i class="fa-solid fa-square-check me-2 mt-1" aria-hidden="true"></i>';

							echo '<div class="alert alert-info text-start mx-auto d-inline-block fs-6" role="alert" style="max-width:640px;">';
							echo '<ul class="mb-0 list-unstyled ps-0">';
							foreach ($requirements as $a) {
								$icon = ($a['type'] === 'chair') ? $chairIcon : $pantsIcon;
								echo '<li class="d-flex align-items-start mb-1">' . $icon . '<div>' . htmlspecialchars($a['text']) . '</div></li>';
							}
							echo '</ul></div>';
						}
					?>
                </div>
                <?php } //End if concert ?>
            </div><!-- /.col-lg- -->
            <div class="col-lg-4 homepage-feature">
                <div class="text-center">
                    <img class="rounded-pill" src="images/logo_facebook.png" alt="Facebook Image" width="140"
                        height="140">
                    <h2>Facebook</h2>
                    <p>Join our <a href="https://www.facebook.com/keystoneconcertband">Facebook page</a> for the latest
                        information and upcoming concerts.</p>
                    <div class="facebook-plugin-frame">
                        <div class="fb-page" data-href="https://www.facebook.com/keystoneconcertband"
                            data-tabs="timeline" data-width="320" data-height="500" data-small-header="true"
                            data-adapt-container-width="true" data-hide-cover="true" data-show-facepile="true">
                            <blockquote cite="https://www.facebook.com/keystoneconcertband"
                                class="fb-xfbml-parse-ignore">
                                <a href="https://www.facebook.com/keystoneconcertband">Keystone Concert Band</a>
                            </blockquote>
                        </div>
                    </div>
                </div>
            </div><!-- /.col-lg-4 -->
            <div class="col-lg-4 homepage-feature">
                <img class="img-fluid" src="images/donate-2023.png" alt="Donate" width="140" height="140">
                <h2>Donate</h2>
                <p>As a 501(c)3 organization, we rely on donations to perform!
                </p>
                <p><a class="btn btn-light" href="donate.php" role="button">View details &raquo;</a></p>
            </div><!-- /.col-lg-4 -->
        </div><!-- /.row -->
        <?php require_once 'includes/footer.php'; ?>
    </div><!-- /.container -->

    <?php require_once 'includes/common_js.php'; ?>
    <script>
    function showAlerts() {
        var el = document.getElementById('bandRequirements');
        if (el) el.classList.toggle('d-none');
    }
    </script>
</body>

</html>

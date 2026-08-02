<!-- Static navbar -->
	<nav class="navbar navbar-expand-lg fixed-top bg-primary" data-bs-theme="dark">
		<div class="container-fluid">
			<a class="navbar-brand" href="/index.php">Keystone Concert Band</a>
			<div class="navbar-brand">
				<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
			</div>
			<div id="navbar" class="collapse navbar-collapse">
				<ul class="navbar-nav me-auto">
					<li class="nav-item"><a class="nav-link <?php if ($_SERVER['PHP_SELF'] == "/index.php") { ?>active<?php } ?>" href="/index.php">Home</a></li>
					<li class="nav-item"><a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == "concerts.php") { ?>active<?php } ?>" href="/concerts.php">Concerts</a></li>
					<li class="nav-item"><a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == "donate.php") { ?>active<?php } ?>" href="/donate.php">Donate</a></li>
					<li class="nav-item"><a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == "join.php") { ?>active<?php } ?>" href="/join.php">Join</a></li>
					<li class="nav-item"><a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == "book.php") { ?>active<?php } ?>" href="/book.php">Book</a></li>
					<li class="nav-item"><a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == "conductors.php") { ?>active<?php } ?>" href="/conductors.php">Conductor</a></li>
					<li class="nav-item"><a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == "music.php" && strpos($_SERVER['PHP_SELF'], '/members/') === false) { ?>active<?php } ?>" href="/music.php">Music</a></li>
					<?php $isMemberAreaActive = (basename($_SERVER['PHP_SELF']) == "members.php" || strpos($_SERVER['PHP_SELF'], '/members/') !== false); ?>
					<?php if(isset($_SESSION["email"])) { ?>
					<li class="nav-item dropdown<?php if ($isMemberAreaActive) { ?> show<?php } ?>">
						<a class="nav-link dropdown-toggle <?php if ($isMemberAreaActive) { ?>active<?php } ?>" href="#" id="membersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="<?php echo $isMemberAreaActive ? 'true' : 'false'; ?>">Member Area</a>
						<ul class="dropdown-menu shadow-sm<?php if ($isMemberAreaActive) { ?> show<?php } ?>" aria-labelledby="membersDropdown">
							<li><a class="dropdown-item <?php if (basename($_SERVER['PHP_SELF']) == "members.php") { echo 'active'; } ?>" href="/members.php">Overview</a></li>
							<?php if(isset($_SESSION["email"])) { ?>
								<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/myInfo.php" || $_SERVER['PHP_SELF'] == "/members/index.php") { echo 'active'; } ?>" href="/members/myInfo.php">My Info</a></li>
								<?php if($_SESSION['accountType'] === 1 || $_SESSION['accountType'] === 2) { ?>
									<li><hr class="dropdown-divider"></li>
									<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == '/members/currentMembers.php') { echo 'active'; } ?>" href="/members/currentMembers.php">Current Members</a></li>
									<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/inactiveMembers.php") { echo 'active'; } ?>" href="/members/inactiveMembers.php">Inactive Members</a></li>
									<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/pendingMembers.php") { echo 'active'; } ?>" href="/members/pendingMembers.php">Pending Members</a></li>
								<?php } else { ?>
									<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/members.php") { echo 'active'; } ?>" href="/members/members.php">Members</a></li>
								<?php } ?>
								<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/documents.php") { echo 'active'; } ?>" href="/members/documents.php">Documents</a></li>
								<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/music.php") { echo 'active'; } ?>" href="/members/music.php">Music</a></li>
								<?php if($_SESSION['accountType'] === 1 || $_SESSION['accountType'] === 2) { ?>
									<li><hr class="dropdown-divider"></li>
									<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/schedule.php") { echo 'active'; } ?>" href="/members/schedule.php">Schedule</a></li>
									<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/homepageMessage.php") { echo 'active'; } ?>" href="/members/homepageMessage.php">Homepage Message</a></li>
									<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/loginStats.php") { echo 'active'; } ?>" href="/members/loginStats.php">Login Stats</a></li>
									<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/messageMembers.php") { echo 'active'; } ?>" href="/members/messageMembers.php">Message Members</a></li>
								<?php } ?>
								<li><hr class="dropdown-divider"></li>
								<li><a class="dropdown-item" href="/members/logoff.php">Logoff</a></li>
							<?php } ?>
						</ul>
					</li>
					<?php } else { ?>
					<li class="nav-item">
						<a class="nav-link <?php if ($isMemberAreaActive) { ?>active<?php } ?>" href="/members.php">Members</a>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>
	</nav>

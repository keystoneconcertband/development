<!-- Static navbar -->
	<nav class="navbar navbar-expand-lg fixed-top bg-primary" data-bs-theme="dark">
		<div class="container-fluid">
			<a class="navbar-brand" href="#">Keystone Concert Band</a>
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
				<li class="nav-item"><a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == "conductors.php") { ?>active<?php } ?>" href="/conductors.php">Conductors</a></li>
				<li class="nav-item"><a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == "members.php" || strpos($_SERVER['PHP_SELF'], '/members/') !== false) { ?>active<?php } ?>" href="/members.php">Members</a></li>
				</ul>
			</div>
		</div>
	</nav>
	<!-- secondary member nav -->
  <?php if(isset($_SESSION["email"]) && strpos($_SERVER['PHP_SELF'], '/members/') !== false) { ?>
	<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom navbar-secondary">
	  <div class="container-fluid">
		<a class="navbar-brand" href="/members/index.php">Members</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#memberNavbar" aria-controls="memberNavbar" aria-expanded="false" aria-label="Toggle navigation">
		  <span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="memberNavbar">
		  <ul class="navbar-nav me-auto mb-2 mb-lg-0">
			<li class="nav-item"><a class="nav-link <?php if ($_SERVER['PHP_SELF'] == "/members/myInfo.php") { echo 'active'; } ?>" href="/members/myInfo.php">My Info</a></li>
			<?php if($_SESSION['accountType'] === 1 || $_SESSION['accountType'] === 2) { ?>
			<li class="nav-item dropdown">
			  <a class="nav-link dropdown-toggle <?php if (in_array($_SERVER['PHP_SELF'], ['/members/members.php', '/members/inactiveMembers.php', '/members/pendingMembers.php'])) { echo 'active'; } ?>" href="#" id="membersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Members</a>
			  <ul class="dropdown-menu" aria-labelledby="membersDropdown">
				<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/members.php") { echo 'active'; } ?>" href="/members/members.php">Current</a></li>
				<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/inactiveMembers.php") { echo 'active'; } ?>" href="/members/inactiveMembers.php">Inactive</a></li>
				<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/pendingMembers.php") { echo 'active'; } ?>" href="/members/pendingMembers.php">Pending</a></li>
			  </ul>
			</li>
			<?php } else { ?>
			<li class="nav-item"><a class="nav-link <?php if ($_SERVER['PHP_SELF'] == "/members/members.php") { echo 'active'; } ?>" href="/members/members.php">Members</a></li>
			<?php } ?>
			<li class="nav-item"><a class="nav-link <?php if ($_SERVER['PHP_SELF'] == "/members/documents.php") { echo 'active'; } ?>" href="/members/documents.php">Documents</a></li>
			<li class="nav-item"><a class="nav-link <?php if ($_SERVER['PHP_SELF'] == "/members/music.php") { echo 'active'; } ?>" href="/members/music.php">Music</a></li>
			<?php if($_SESSION['accountType'] === 1 || $_SESSION['accountType'] === 2) { ?>
			<li class="nav-item dropdown">
			  <a class="nav-link dropdown-toggle <?php if (in_array($_SERVER['PHP_SELF'], ['/members/homepageMessage.php', '/members/loginStats.php', '/members/messageMembers.php'])) { echo 'active'; } ?>" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Administration</a>
			  <ul class="dropdown-menu" aria-labelledby="adminDropdown">
				<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/homepageMessage.php") { echo 'active'; } ?>" href="/members/homepageMessage.php">Homepage Message</a></li>
				<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/loginStats.php") { echo 'active'; } ?>" href="/members/loginStats.php">Login Stats</a></li>
				<!--<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/manageConcerts.php") { echo 'active'; } ?>" href="/members/manageConcerts.php">Manage Concerts</a></li>-->
				<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/messageMembers.php") { echo 'active'; } ?>" href="/members/messageMembers.php">Message Members</a></li>
			  </ul>
			</li>
			<?php } ?>
			<li class="nav-item"><a class="nav-link" href="/members/logoff.php">Logoff</a></li>
		  </ul>
		</div>
	  </div>
	</nav>
<?php } ?>

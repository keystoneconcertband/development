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
				<li class="nav-item"><a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == "cd.php") { ?>active<?php } ?>" href="/cd.php">CD</a></li>
				<li class="nav-item"><a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == "conductors.php") { ?>active<?php } ?>" href="/conductors.php">Conductors</a></li>
				<li class="nav-item"><a class="nav-link <?php if (basename($_SERVER['PHP_SELF']) == "members.php" || strpos($_SERVER['PHP_SELF'], '/members/') !== false) { ?>active<?php } ?>" href="/members.php">Members</a></li>
				</ul>
			</div>
		</div>
	</nav>
	<!-- fix this too! -->
  <?php if(isset($_SESSION["email"]) && strpos($_SERVER['PHP_SELF'], '/members/') !== false) { ?>
	<nav class="navbar navbar-default navbar-static-top" style="z-index:999;margin-bottom: 0;">
	  <div class="container-fluid">
		<div class="navbar-brand">
			<button type="button" class="navbar-toggler collapsed" data-bs-toggle="collapse" data-bs-target="#memberNavbar" aria-expanded="false" aria-controls="memberNavbar">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="/members/index.php">Members</a>
		</div>
	    <div class="collapse navbar-collapse" id="memberNavbar">
	      <ul class="nav navbar-nav">
			<li <?php if ($_SERVER['PHP_SELF'] == "/members/myInfo.php") { ?>class="av-link active"<?php }?>><a href="/members/myInfo.php">My Info</a></li>
			<?php if($_SESSION['accountType'] === 1 || $_SESSION['accountType'] === 2) { ?>
			<li class="dropdown <?php if ($_SERVER['PHP_SELF'] == "/members/members.php" || $_SERVER['PHP_SELF'] == "/members/innav-link activeMembers.php" || $_SERVER['PHP_SELF'] == "/members/pendingMembers.php") { ?>nav-link active"<?php } else { ?> " <?php } ?>">
				<a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Members <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li <?php if ($_SERVER['PHP_SELF'] == "/members/members.php") { ?>class="nav-link active"<?php }?>><a href="/members/members.php">Current</a></li>
						<li <?php if ($_SERVER['PHP_SELF'] == "/members/innav-link activeMembers.php") { ?>class="nav-link active"<?php }?>><a href="/members/innav-link activeMembers.php">Innav-link active</a></li>
						<li <?php if ($_SERVER['PHP_SELF'] == "/members/pendingMembers.php") { ?>class="nav-link active"<?php }?>><a href="/members/pendingMembers.php">Pending</a></li>
					</ul>
			</li>
			<?php } else { ?>
			<li <?php if ($_SERVER['PHP_SELF'] == "/members/members.php") { ?>class="nav-link active"<?php }?>><a href="/members/members.php">Members</a></li>
			<?php } ?>
			<li <?php if ($_SERVER['PHP_SELF'] == "/members/documents.php") { ?>class="nav-link active"<?php }?>><a href="/members/documents.php">Documents</a></li>
			<li <?php if ($_SERVER['PHP_SELF'] == "/members/music.php") { ?>class="nav-link active"<?php }?>><a href="/members/music.php">Music</a></li>
			<?php if($_SESSION['accountType'] === 1 || $_SESSION['accountType'] === 2) { ?>
			<li class="dropdown <?php if ($_SERVER['PHP_SELF'] == "/members/messageMembers.php" || $_SERVER['PHP_SELF'] == "/members/loginStats.php") { ?>nav-link active"<?php } else { ?> " <?php } ?>">
				<a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Administration <span class="caret"></span></a>
				<ul class="dropdown-menu">
					<li <?php if ($_SERVER['PHP_SELF'] == "/members/homepageMessage.php") { ?>class="nav-link active"<?php }?>><a href="/members/homepageMessage.php">Homepage Message</a></li>
					<li <?php if ($_SERVER['PHP_SELF'] == "/members/loginStats.php") { ?>class="nav-link active"<?php }?>><a href="/members/loginStats.php">Login Stats</a></li>
					<!--<li <?php if ($_SERVER['PHP_SELF'] == "/members/manageConcerts.php") { ?>class="nav-link active"<?php }?>><a href="/members/manageConcerts.php">Manage Concerts</a></li>-->
					<li <?php if ($_SERVER['PHP_SELF'] == "/members/messageMembers.php") { ?>class="nav-link active"<?php }?>><a href="/members/messageMembers.php">Message Members</a></li>
				</ul>
			</li>
			<?php } ?>
			<li><a href="/members/logoff.php">Logoff</a></li>
	      </ul>
	    </div>
	  </div>
	</nav>
<?php } ?>

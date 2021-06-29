<!-- Static navbar -->
	<nav class="navbar fixed-top navbar-expand-md navbar-dark bg-primary">
		<a class="navbar-brand" href="/index.php">Keystone Concert Band</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarKCB" aria-controls="navbarKCB" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarKCB">
			<ul class="navbar-nav mr-auto">
				<li class="nav-item<? if ($_SERVER['PHP_SELF'] == "/index.php") { echo " active"; } ?>"><a class="nav-link" href="/index.php">Home</a></li>
				<li class="nav-item<? if (basename($_SERVER['PHP_SELF']) == "concerts.php") { echo " active"; } ?>"><a class="nav-link" href="/concerts.php">Concerts</a></li>
				<li class="nav-item<? if (basename($_SERVER['PHP_SELF']) == "donate.php") { echo " active"; } ?>"><a class="nav-link" href="/donate.php">Donate</a></li>
				<li class="nav-item<? if (basename($_SERVER['PHP_SELF']) == "join.php") { echo " active"; } ?>"><a class="nav-link" href="/join.php">Join</a></li>
				<li class="nav-item<? if (basename($_SERVER['PHP_SELF']) == "book.php") { echo " active"; } ?>"><a class="nav-link" href="/book.php">Book</a></li>
				<li class="nav-item<? if (basename($_SERVER['PHP_SELF']) == "cd.php") { echo " active"; } ?>"><a class="nav-link" href="/cd.php">CD</a></li>
				<li class="nav-item<? if (basename($_SERVER['PHP_SELF']) == "conductors.php") { echo " active"; } ?>"><a class="nav-link" href="/conductors.php">Conductors</a></li>
				<li class="nav-item<? if (basename($_SERVER['PHP_SELF']) == "members.php" || strpos($_SERVER['PHP_SELF'], '/members/') !== false) { echo " active"; } ?>"><a class="nav-link" href="/members.php">Members</a></li>
			</ul>
		</div> 
	</nav>
  <? if(isset($_SESSION["email"]) && strpos($_SERVER['PHP_SELF'], '/members/') !== false) { ?>
	<nav class="navbar navbar-expand-md navbar-dark bg-dark" style="margin-top:12px">
		<a class="navbar-brand" href="/members/index.php">Members</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarKCBMember" aria-controls="navbarKCBMember" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
	    <div class="collapse navbar-collapse" id="navbarKCBMember">
			<ul class="navbar-nav mr-auto">
				<li class="nav-item<? if ($_SERVER['PHP_SELF'] == "/members/myInfo.php") { echo " active";}?>"><a class="nav-link" href="/members/myInfo.php">My Info</a></li>
				<? if(isset($_SESSION['office'])) { ?>
					<li class="nav-item dropdown<? if ($_SERVER['PHP_SELF'] == "/members/members.php" || $_SERVER['PHP_SELF'] == "/members/inactiveMembers.php" || $_SERVER['PHP_SELF'] == "/members/pendingMembers.php") { echo " active"; } ?>">
						<a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Members</a>
						<div class="dropdown-menu">
							<a class="dropdown-item<? if ($_SERVER['PHP_SELF'] == "/members/members.php") { echo " active";}?>" href="/members/members.php">Current</a>
							<a class="dropdown-item<? if ($_SERVER['PHP_SELF'] == "/members/inactiveMembers.php") { echo " active";}?>" href="/members/inactiveMembers.php">Inactive</a>
							<a class="dropdown-item<? if ($_SERVER['PHP_SELF'] == "/members/pendingMembers.php") { echo " active";}?>" href="/members/pendingMembers.php">Pending</a>
						</div>
					</li>
				<? } else { ?>
					<li class="nav-item<? if ($_SERVER['PHP_SELF'] == "/members/members.php") { echo " active";}?>"><a class="nav-link" href="/members/members.php">Members</a></li>
				<? } ?>
				<li class="nav-item<? if ($_SERVER['PHP_SELF'] == "/members/documents.php") { echo " active";}?>"><a class="nav-link" href="/members/documents.php">Documents</a></li>
				<li class="nav-item<? if ($_SERVER['PHP_SELF'] == "/members/music.php") { echo " active";}?>"><a class="nav-link" href="/members/music.php">Music</a></li>
				<? if (isset($_SESSION["office"])) { ?>				
					<li class="nav-item dropdown<? if ($_SERVER['PHP_SELF'] == "/members/messageMembers.php" || $_SERVER['PHP_SELF'] == "/members/homepageMessage.php" || $_SERVER['PHP_SELF'] == "/members/loginStats.php") { echo " active"; } ?>">
						<a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Administration</a>
						<div class="dropdown-menu">
							<a class="dropdown-item<? if ($_SERVER['PHP_SELF'] == "/members/homepageMessage.php") { echo " active";}?>" href="/members/homepageMessage.php">Homepage Message</a>
							<a class="dropdown-item<? if ($_SERVER['PHP_SELF'] == "/members/loginStats.php") { echo " active";}?>" href="/members/loginStats.php">Login Stats</a>
							<!--<a class="dropdown-item<? if ($_SERVER['PHP_SELF'] == "/members/manageConcerts.php") { echo " active";}?>" href="/members/manageConcerts.php">Manage Concerts</a>-->
							<a class="dropdown-item<? if ($_SERVER['PHP_SELF'] == "/members/messageMembers.php") { echo " active";}?>" href="/members/messageMembers.php">Message Members</a>
						</div>
					</li>
				<? } ?>
				<li class="nav-item"><a class="nav-link" href="/members/logoff.php">Logoff</a></li>
			</ul>
		</div>
	</nav>
<? } ?>

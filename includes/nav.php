	<nav class="navbar navbar-expand-lg navbar-default navbar-static-top" style="z-index:999;margin-bottom: 0;">
	  <div class="container-fluid">
		<a class="navbar-brand" href="/members/index.php">Members</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#memberNavbar" aria-controls="memberNavbar" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
	    <div class="collapse navbar-collapse" id="memberNavbar">
	      <ul class="navbar-nav me-auto">
			<li class="nav-item <?php if ($_SERVER['PHP_SELF'] == "/members/myInfo.php") { ?>active<?php }?>"><a class="nav-link" href="/members/myInfo.php">My Info</a></li>
			<?php if($_SESSION['accountType'] === 1 || $_SESSION['accountType'] === 2) { ?>
			<li class="nav-item dropdown <?php if ($_SERVER['PHP_SELF'] == "/members/members.php" || $_SERVER['PHP_SELF'] == "/members/inactiveMembers.php" || $_SERVER['PHP_SELF'] == "/members/pendingMembers.php") { ?>active<?php } ?>">
				<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Members</a>
				<ul class="dropdown-menu">
					<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/members.php") { ?>active<?php }?>" href="/members/members.php">Current</a></li>
					<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/inactiveMembers.php") { ?>active<?php }?>" href="/members/inactiveMembers.php">Inactive</a></li>
					<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/pendingMembers.php") { ?>active<?php }?>" href="/members/pendingMembers.php">Pending</a></li>
				</ul>
			</li>
			<?php } else { ?>
			<li class="nav-item <?php if ($_SERVER['PHP_SELF'] == "/members/members.php") { ?>active<?php }?>"><a class="nav-link" href="/members/members.php">Members</a></li>
			<?php } ?>
			<li class="nav-item <?php if ($_SERVER['PHP_SELF'] == "/members/documents.php") { ?>active<?php }?>"><a class="nav-link" href="/members/documents.php">Documents</a></li>
			<li class="nav-item <?php if ($_SERVER['PHP_SELF'] == "/members/music.php") { ?>active<?php }?>"><a class="nav-link" href="/members/music.php">Music</a></li>
			<?php if($_SESSION['accountType'] === 1 || $_SESSION['accountType'] === 2) { ?>
			<li class="nav-item dropdown <?php if ($_SERVER['PHP_SELF'] == "/members/messageMembers.php" || $_SERVER['PHP_SELF'] == "/members/loginStats.php") { ?>active<?php } ?>">
				<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Administration</a>
				<ul class="dropdown-menu">
					<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/messageMembers.php") { ?>active<?php }?>" href="/members/messageMembers.php">Message Members</a></li>
					<li><a class="dropdown-item <?php if ($_SERVER['PHP_SELF'] == "/members/loginStats.php") { ?>active<?php }?>" href="/members/loginStats.php">Login Stats</a></li>
				</ul>
			</li>
			<?php } ?>
			<li class="nav-item"><a class="nav-link" href="/members/logout.php">Logout</a></li>
		</ul>
	    </div>
	  </div>
	</nav>
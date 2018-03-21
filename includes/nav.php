<!-- Static navbar -->
	<nav class="navbar navbar-inverse navbar-fixed-top">
		<div class="container-fluid">
		  <div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="#">Keystone Concert Band</a>
		  </div>
		  <div id="navbar" class="navbar-collapse collapse">
		    <ul class="nav navbar-nav">
		      <li <? if ($_SERVER['PHP_SELF'] == "/index.php") { ?>class="active"<? } ?>><a href="/index.php">Home</a></li>
		      <li <? if (basename($_SERVER['PHP_SELF']) == "concerts.php") { ?>class="active"<? } ?>><a href="/concerts.php">Concerts</a></li>
		      <li <? if (basename($_SERVER['PHP_SELF']) == "donate.php") { ?>class="active"<? } ?>><a href="/donate.php">Donate</a></li>
		      <li <? if (basename($_SERVER['PHP_SELF']) == "join.php") { ?>class="active"<? } ?>><a href="/join.php">Join</a></li>
		      <li <? if (basename($_SERVER['PHP_SELF']) == "book.php") { ?>class="active"<? } ?>><a href="/book.php">Book</a></li>
		      <li <? if (basename($_SERVER['PHP_SELF']) == "cd.php") { ?>class="active"<? } ?>><a href="/cd.php">CD</a></li>
		      <li <? if (basename($_SERVER['PHP_SELF']) == "conductors.php") { ?>class="active"<? } ?>><a href="/conductors.php">Conductors</a></li>
		 	  <li <? if (basename($_SERVER['PHP_SELF']) == "members.php" || strpos($_SERVER['PHP_SELF'], '/members/') !== false) { ?>class="active"<? } ?>><a href="/members.php">Members</a></li>
		    </ul>
		  </div><!--/.nav-collapse -->
		</div><!--/.container-fluid -->
	</nav>
  <? if(isset($_SESSION["email"]) && strpos($_SERVER['PHP_SELF'], '/members/') !== false) { ?>
	<nav class="navbar navbar-default navbar-static-top" style="z-index:999;margin-bottom: 0;">
	  <div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#memberNavbar" aria-expanded="false" aria-controls="memberNavbar">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="/members/index.php">Members</a>
		</div>
	    <div class="collapse navbar-collapse" id="memberNavbar">
	      <ul class="nav navbar-nav">
			<li <? if ($_SERVER['PHP_SELF'] == "/members/myInfo.php") { ?>class="active"<?}?>><a href="/members/myInfo.php">My Info</a></li>
			<? if(isset($_SESSION['office'])) { ?>
			<li class="dropdown <? if ($_SERVER['PHP_SELF'] == "/members/members.php" || $_SERVER['PHP_SELF'] == "/members/inactiveMembers.php" || $_SERVER['PHP_SELF'] == "/members/pendingMembers.php") { ?>active"<?} else { ?> " <? } ?>">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Members <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li <? if ($_SERVER['PHP_SELF'] == "/members/members.php") { ?>class="active"<?}?>><a href="/members/members.php">Current</a></li>
						<li <? if ($_SERVER['PHP_SELF'] == "/members/inactiveMembers.php") { ?>class="active"<?}?>><a href="/members/inactiveMembers.php">Inactive</a></li>
						<li <? if ($_SERVER['PHP_SELF'] == "/members/pendingMembers.php") { ?>class="active"<?}?>><a href="/members/pendingMembers.php">Pending</a></li>
					</ul>
			</li>
			<? } else { ?>
			<li <? if ($_SERVER['PHP_SELF'] == "/members/members.php") { ?>class="active"<?}?>><a href="/members/members.php">Members</a></li>
			<? } ?>
			<li <? if ($_SERVER['PHP_SELF'] == "/members/documents.php") { ?>class="active"<?}?>><a href="/members/documents.php">Documents</a></li>
			<li <? if ($_SERVER['PHP_SELF'] == "/members/music.php") { ?>class="active"<?}?>><a href="/members/music.php">Music</a></li>
			<? if (isset($_SESSION["office"])) { ?>
			<li class="dropdown <? if ($_SERVER['PHP_SELF'] == "/members/messageMembers.php" || $_SERVER['PHP_SELF'] == "/members/loginStats.php") { ?>active"<?} else { ?> " <? } ?>">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Administration <span class="caret"></span></a>
				<ul class="dropdown-menu">
					<!--<li <? if ($_SERVER['PHP_SELF'] == "/members/homepageMessage.php") { ?>class="active"<?}?>><a href="/members/homepageMessage.php">Homepage Message</a></li>-->
					<li <? if ($_SERVER['PHP_SELF'] == "/members/loginStats.php") { ?>class="active"<?}?>><a href="/members/loginStats.php">Login Stats</a></li>
					<!--<li <? if ($_SERVER['PHP_SELF'] == "/members/manageConcerts.php") { ?>class="active"<?}?>><a href="/members/manageConcerts.php">Manage Concerts</a></li>-->
					<li <? if ($_SERVER['PHP_SELF'] == "/members/messageMembers.php") { ?>class="active"<?}?>><a href="/members/messageMembers.php">Message Members</a></li>
				</ul>
			</li>
			<? } ?>
			<li><a href="/members/logoff.php">Logoff</a></li>
	      </ul>
	    </div>
	  </div>
	</nav>
<? } ?>

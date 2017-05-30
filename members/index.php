<?
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);

	session_start();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
	<? require '../includes/common_meta.php'; ?>
    <meta name="description" content="The Keystone Concert Band member area">

    <title>Member Area - Keystone Concert Band</title>

	<? require '../includes/common_css.php'; ?>
	
	<style type="text/css">
		.row .col-lg-12 ul li {
			margin-bottom: 10px;
		}
		</style>
  </head>

  <body>

	<? require '../includes/nav.php'; ?>
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="bs-component">
					<div class="jumbotron">
						<h1>KCB Members</h1>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<div class="page-header">
					<h2>Members</h2>
				</div>
				<?
					echo 'Login successful' . '<br>';
					echo 'Email: ' . $_SESSION["email"] . '<br>';
					echo 'Auth Cd GUID: ' . $_SESSION["auth_cd_guid"] . '<br>';	
						
					if(isset($_COOKIE["KCB_Cookie"])) {
						$pieces = explode(",", $_COOKIE["KCB_Cookie"]);
					    $cookieEmail = $pieces[0];
					    $cookieAuthCd = $pieces[1];
					    
					    echo 'cookie email: ' . $cookieEmail . '<br>';
					    echo 'cookie auth Cd: ' . $cookieAuthCd . '<br>';
					}
				?>
			</div>
		</div>
		<? require '../includes/footer.php'; ?>
	</div> <!-- /container -->

	<? require '../includes/common_js.php'; ?>
  </body>
</html>

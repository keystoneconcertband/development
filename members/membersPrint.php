<?php
include_once("../includes/class/protectedMember.class.php");
include_once("../includes/class/member.db.class.php");

$pMbr = new ProtectedMember();
$members = $pMbr->getActiveMembers();

function formatAddress($mbr) {
	$address = "";
	if($mbr['address1'] !== '') {
		$address .= $mbr['address1'] . '<br />';

		if($mbr['address2']) {
			$address .= $mbr['address2'] . '<br />';
		}	
		
		$address .= $mbr['city'] . ', ' . $mbr['state'] . ' ' . $mbr['zip'];				
	}
	
	return $address;
}

function formatCommaArrays($arr) {
	$response = "";
	$explodedArr = explode(',', $arr);
	
	if($arr !== "") {
		foreach($explodedArr as $result) {
			$response .= $result . "<br />";
		}
	}
	
	return $response;
}

function formatPhone($number) {
	return preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $number);
}
?>
<html>
	<head>
		<title>KCB Members</title>
		<link href="/3rd-party/bootswatch-3.4.1/yeti/bootstrap.min.css" rel="stylesheet">
		<style type="text/css" media="print,screen" >
		thead {
		   display:table-header-group;
		}
		tbody {
		   display:table-row-group;
		}
		</style>
	</head>
	<body>
		<table class="table table-hover">
			<thead>
				<th>Name</th>
				<th>Email Address(es)</th>
				<th>Instrument</th>
				<th>Cell Phone</th>
				<th>Home Phone</th>
				<th>Address</th>
				<th>Volunteer Position</th>
			</thead>
			<tbody>
			<?php
				foreach($members as $mbr) {
					echo "<tr>";
					echo "<td>" . $mbr['fullName'] . "</td>";
					echo "<td>" . formatCommaArrays($mbr['email']) . "</td>";
					echo "<td>" . formatCommaArrays($mbr['instrument']) . "</td>";
					echo "<td>" . formatPhone($mbr['text']) . "</td>";
					echo "<td>" . formatAddress($mbr) . "</td>";
					echo "<td>" . $mbr['office'] . "</td>";
					echo "</tr>";
				}
			?>
			</tbody>
	</body>
	<?php require '../includes/common_js.php'; ?>
	<script>
		$(document).ready(function() {
			console.log("test");
			window.print();
			setTimeout(window.close, 0);
		});
	</script>
</html>
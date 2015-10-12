<?php
	$link = mysqli_connect("freekibble-click-counter-db.clltdiskvizr.us-west-2.rds.amazonaws.com", "freekibble", "freekibbleclick","freekibble");

	$date = $_POST["date"];
	$site_id = $_POST["site_id"];
	$plus = $_POST["plus"];
	$clicks = $_POST["clicks"];
	$value = $_POST["value"];
	
	$sql = "update clicks_total_day set clicks = " . $clicks . ", value = " . $value . " where site_id = '" . $site_id . "' and date(`day`) = '" . $date . "' and plus = '" . $plus . "'";
	$link->query($sql);
	$sql = "select clicks from clicks_total_day where site_id = '" . $site_id . "' and date(`day`) = '" . $date . "' and plus = '" . $plus . "'";
	$result = $link->query($sql);
	$row = $result->fetch_array($result);
	echo '{"RETURN" : "UPDATE COMPLETE - ' . $row["clicks"] . '"}';
?>
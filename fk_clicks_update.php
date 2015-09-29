<?php
	$link = mysql_connect('localhost', 'freekibble', 'freekibbleclick');
	if (!$link) {
    	die('Could not connect: ' . mysql_error());
	}	
    mysql_select_db('opencart');

	$date = $_REQUEST["date"];
	$site_id = $_REQUEST["site_id"];
	$plus = $_REQUEST["plus"];
	$clicks = $_REQUEST["clicks"];
	$value = $_REQUEST["value"];
	
	$sql = "update clicks_total_day set clicks = " . $clicks . ", value = " . $value . " where site_id = '" . $site_id . "' and date(`day`) = '" . $date . "' and plus = '" . $plus . "'";
	mysql_query($sql);
	$sql = "select clicks from clicks_total_day where site_id = '" . $site_id . "' and date(`day`) = '" . $date . "' and plus = '" . $plus . "'";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	echo '{"RETURN" : "UPDATE COMPLETE - ' . $row["clicks"] . '"}';
?>
<?php
	$date = $_REQUEST["date"];
	$site_id = $_REQUEST["site_id"];
	$plus = $_REQUEST["plus"];
	$clicks = $_REQUEST["clicks"];
	$value = $_REQUEST["value"];
	
	$sql = "update clicks_total_day set clicks = " . $clicks . ", value = " . $value . " where site_id = '" . $site_id . "' and date(`day`) = '" . $date . "' and plus = '" . $plus . "'";
	$wpdb->get_results($sql);
	$sql = "select clicks from clicks_total_day where site_id = '" . $site_id . "' and date(`day`) = '" . $date . "' and plus = '" . $plus . "'";
	$row = $wpdb->get_results($sql);
	echo '{"RETURN" : "UPDATE COMPLETE - ' . $row->clicks . '"}';
?>
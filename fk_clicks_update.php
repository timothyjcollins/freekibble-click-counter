<?php
date_default_timezone_set('America/Los_Angeles');
$http_origin = $_SERVER['HTTP_ORIGIN'];
if ( $http_origin == "http://www.freekibble.com" || $http_origin == "http://freekibble.wpengine.com") {  
  header("Access-Control-Allow-Origin: $http_origin");
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');

$link = mysqli_connect('127.0.0.1','freekibble','Qou0iZDEGOZKqG32','wp_freekibble');
if ( !$link ) {
		die('Could not connect: ' . mysqli_connect_error());
}
mysqli_select_db($link, 'wp_freekibble');

$date = $_REQUEST["date"];
$site_id = $_REQUEST["site_id"];
$plus = $_REQUEST["plus"];
$clicks = $_REQUEST["clicks"];
$value = $_REQUEST["value"];

$sql = "update clicks_total_day set clicks = " . $clicks . ", value = " . $value . " where site_id = '" . $site_id . "' and date(`day`) = '" . $date . "' and plus = '" . $plus . "'";
mysqli_query($link,$sql);
$sql = "select clicks from clicks_total_day where site_id = '" . $site_id . "' and date(`day`) = '" . $date . "' and plus = '" . $plus . "'";
$result = mysqli_query($link,$sql);
$row = mysqli_fetch_assoc($result);
echo '{"RETURN" : "UPDATE COMPLETE - ' . $row["clicks"] . '"}';

?>
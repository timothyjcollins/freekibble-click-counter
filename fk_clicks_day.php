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
$today = date('Y-m-d', time());
$sql = "select plus,day(`day`) as year_date,site_id, sum(clicks) as total_clicks, sum(value) as total_sum, sum(correct) as total_correct ";
$sql .= "from clicks_total_day ";
$sql .= "where month(`day`) = month('" . $today . "') and year(`day`) = year('" . $today . "') ";
$sql .= "group by day(`day`), site_id,plus ";
$sql .= "order by day(`day`), site_id ";
$result = mysqli_query($link,$sql);
$current = ""; 
$ctr = 1;
$jsonstr = "";
$tot_plus = 0;
while($row = mysqli_fetch_assoc($result)){
	if($row["year_date"] != $current){
		if($ctr != 1){
			$jsonstr = rtrim($jsonstr, ",");
			$jsonstr .= '],';
			$jsonstr .= '"PLUS" : "'. $tot_plus . '"';
			$jsonstr .= '}';
			$tot_plus = 0;
		}	
		$ctr = $ctr + 1; 
		$jsonstr .= ',{"TIMESTAMP" : "' . $row["year_date"] . '", "values" : [';	
		$current = $row["year_date"];
	}
	$jsonstr .= '{"SITE_ID" : "' . $row["site_id"] . '",';
	$jsonstr .= '"PLUS" : "' . $row["plus"] . '",';	
	$jsonstr .= '"CLICKS" : "' . $row["total_clicks"] . '",';	
	$jsonstr .= '"VALUE" : "' . $row["total_sum"] . '",';	
	$jsonstr .= '"CORRECT" : "' . $row["total_correct"] . '"},';
	if($row["plus"] == "YES"){
		$tot_plus = $tot_plus + $row["total_sum"];
	}
}
$jsonstr = ltrim($jsonstr, ",");
$jsonstr = rtrim($jsonstr, ",");
$jsonstr = "[" . $jsonstr . '], "PLUS" : "' . $tot_plus . '"}]';
echo $jsonstr;
?>
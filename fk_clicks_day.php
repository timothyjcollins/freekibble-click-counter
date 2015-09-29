<?php
	$link = mysqli_connect("freekibble-click-counter.clltdiskvizr.us-west-2.rds.amazonaws.com:3306", "freekibble", "freekibbleclick", "freekibble");
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

	$sql = "select plus,day(`day`) as year_date,site_id, sum(clicks) as total_clicks, sum(value) as total_sum, sum(correct) as total_correct ";
	$sql .= "from clicks_total_day ";
	$sql .= "where month(`day`) = month(NOW()) and year(`day`) = year(NOW()) ";
	$sql .= "group by day(`day`), site_id,plus ";
	$sql .= "order by day(`day`), site_id ";
	$result = $link->query($sql);
	$current = "";
	$ctr = 1;
	$jsonstr = "";
	$tot_plus = 0;
	while($row = $result->fetch_array()){
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
	if ( $http_origin == "http://freekibble.com" || $http_origin == "http://freekibble.wpengine.com") {  
	  header("Access-Control-Allow-Origin: $http_origin");
	}
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
	header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');
	echo $jsonstr;
?>
<?php
	$link = mysql_connect('localhost', 'opencart', 'sbxe2813');
	if (!$link) {
    	die('Could not connect: ' . mysql_error());
	}
	//$http_origin = $_SERVER["HTTP_REFERER"];
	//if ( $http_origin == "http://freekibble.com" || $http_origin == "http://freekibble.wpengine.com") {  
	  	header("Access-Control-Allow-Origin: http://freekibble.com");
	//}
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
	header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');
	
    mysql_select_db('opencart');
	
	if($_GET["pa"] == "RECORD"){
		//Can also return All time total by site (dog/cat/litter)
		$site_id = $_REQUEST["site_id"];
		if(isset($_REQUEST["user_id"])){
			$user_id = $_REQUEST["user_id"];
			$user_ip = $_REQUEST["user_ip"];	
			$correct = $_REQUEST["correct"];
			$plus = $_REQUEST["plus"]; // YES or NO
			$litter = $_REQUEST["litter"]; // YES or NO
		}else{
			$user_id = "";	
		}
		
		if($user_id != ""){
			$value = 10;
			if($plus == "YES"){
				$value = 20;	
			}
			if($site_id == "3"){
				$value = 0.125;	
			}
			if($_REQUEST["double_value"] == "YES"){
				$value = $value + $value;	
			}
			$sql = "insert into clicks (user_id, user_ip, site_id, timestamp, correct, plus_member,click_value) values ('" . $user_id . "','". $user_ip . "','" . $site_id . "',NOW(), '" . $correct . "','" . $plus . "'," . $value . ")";
			mysql_query($sql);
			$sql = "select count(id) as cnt from clicks_total where site_id = '" . $site_id . "'";
			$result = mysql_query($sql);
			$row = mysql_fetch_array($result);
			if($row["cnt"] == 0){
				mysql_query("insert into clicks_total (site_id,total,total_value) values ('" . $site_id . "',1," . $value . ")");	
			}else{
				$sql = "update clicks_total set total = total + 1, total_value = total_value + " . $value . " where site_id = '". $site_id . "'";
				mysql_query($sql);
			}
			$sql = "select id, count(id) as cnt from clicks_total_day where day = DATE(NOW()) and plus= '" . $_REQUEST["plus"] . "' and site_id = '" . $site_id . "'";
			$result_total = mysql_query($sql);
			$row_total = mysql_fetch_array($result_total);
			if($row_total["cnt"] == 0){
				$sql = "insert into clicks_total_day (site_id, day,clicks,`value`,correct,incorrect,plus) values ('" . $site_id . "', DATE(NOW()),0,0,0,0,'" . $_REQUEST["plus"] . "')";
				mysql_query($sql);
				$sql = "select id as cnt from clicks_total_day where day = DATE(NOW()) and site_id = '" . $site_id . "'";
				$result_total2 = mysql_query($sql);
				$row_total2 = mysql_fetch_array($result_total2);
				$total_id = $row_total2["id"];
			}else{
				$total_id = $row_total["id"];	
			}
			if($correct == "YES"){
				$sql_bit = "correct = correct + 1";	
			}else{
				$sql_bit = "incorrect = incorrect + 1";
			}
			$sql = "update clicks_total_day set clicks = clicks + 1, value = value + " . $value . "," . $sql_bit . " where id = " . $total_id;
			//echo $sql;
			mysql_query($sql);
			if($_REQUEST["correct"] == 1){
				$sql = "update clicks_total_day set correct = correct + 1 where id = " . $total_id;	
			}else{
				$sql = "update clicks_total_day set incorrect = incorrect + 1 where id = " . $total_id;
			}
			mysql_query($sql);
			
			$sql = "select count(id) as cnt from clicks_per_user where site_id = " . $site_id . " and user_id = " . $user_id;
			$result = mysql_query($sql);
			$row = mysql_fetch_array($result);
			if($row["cnt"] == 0){
				$sql = "insert into clicks_per_user (user_id,clicks,site_id,correct) values (" . $user_id . ",1," . $site_id . ",0)";
				mysql_query($sql);	
			}else{
				$sql = "update clicks_per_user set clicks = clicks + 1 where site_id = " . $site_id . " and user_id = " . $user_id;
				mysql_query($sql);
			}
			//echo $sql;
			if($correct == "YES"){
				$sql = "update clicks_per_user set correct = correct + 1 where site_id = " . $site_id . " and user_id = " . $user_id;
				mysql_query($sql);
			}
		}
		$sql = "select sum(clicks) as clicks, sum(value) as value,sum(correct) as correct from clicks_total_day where site_id = '". $site_id . "'";
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);

		$sql = "select sum(clicks) as clicks, sum(value) as value,sum(correct) as correct from clicks_total_day where day = DATE(NOW()) and site_id = '". $site_id . "'";
		$result_today = mysql_query($sql);
		$row_today = mysql_fetch_array($result_today);

		echo '[';
		echo '{"CLICKCOUNT" : "' . $row["clicks"] . '","CLICKVALUE" : "' . $row["value"] . '","CLICKCORRECT" : "' . $row["correct"] . '",';
		$clickpercentcorrect_today = ($row_today["correct"]/$row_today["clicks"])*100;
		echo '"CLICKCOUNT_TODAY" : "' . $row_today["clicks"] . '","CLICKVALUE_TODAY" : "' . $row_today["value"] . '","CLICKCORRECT_TODAY" : "' . $row_today["correct"] . '", "CLICKPERCENTCORRECT_TODAY" : "' . $clickpercentcorrect_today . '"}';
		echo ']';
	}
	if($_GET["pa"] == "REPORT_TOTALS"){
		$sql = "select * from clicks_total";
		$result = mysql_query($sql);
		$json =  "[";
		while($row = mysql_fetch_array($result)){
			$json .=  '{"SITE_ID" : "' . $row["site_id"] . '",';	
			$json .=  '"TOTAL_CLICKS" : "' . $row["total"] . '"},';	
		}	
		$json = substr_replace($json ,"",-1);
		$json .=  "]";
		echo $json;
	}
	if($_GET["pa"] == "REPORT_TIME_PERIOD"){
		$sql = "select site_id,`day`,sum(clicks) as clicks,sum(`value`) as `value`,sum(correct) as correct,sum(incorrect) as incorrect from clicks_total_day where `day` between '" . $_GET["start"] . "' and '" . $_GET["end"] . "'";
		$result = mysql_query($sql);
		$json =  "[";
		while($row = mysql_fetch_array($result)){
			$json .=  '{"SITE_ID" : "' . $row["site_id"] . '",';	
			$json .=  '"TIMESTAMP" : "' . $row["day"] . '",';	
			$json .=  '"CLICKS" : "' . $row["clicks"] . '",';	
			$json .=  '"VALUE" : "' . $row["value"] . '",';	
			$json .=  '"INCORRECT" : "' . $row["incorrect"] . '",';	
			$json .=  '"CORRECT" : "' . $row["correct"] . '"},';	
		}	
		$json = substr_replace($json ,"",-1);
		$json .=  "]";
		echo $json;
	}
	if($_GET["pa"] == "REPORT_TIME_PERIOD_USER"){
		$sql = "select * from clicks where timestamp between '" . $_GET["start"] . "' and '" . $_GET["end"] . "' and user_id = '" . $_GET["user_id"] . "'";
		$result = mysql_query($sql);
		$json =  "[";
		while($row = mysql_fetch_array($result)){
			$json .=  '{"SITE_ID" : "' . $row["site_id"] . '",';	
			$json .=  '"USER_ID" : "' . $row["user_id"] . '",';	
			$json .=  '"USER_IP" : "' . $row["user_ip"] . '",';	
			$json .=  '"TIMESTAMP" : "' . $row["timestamp"] . '",';	
			$json .=  '"CORRECT" : "' . $row["correct"] . '"},';
		}	
		$json = substr_replace($json ,"",-1);
		$json .=  "]";
		echo $json;
	}
	if($_GET["pa"] == "CREATE"){
		$sql = "CREATE TABLE `clicks` (`id` int(11) NOT NULL AUTO_INCREMENT,`user_id` varchar(255) DEFAULT NULL,`user_ip` varchar(255) DEFAULT NULL,`site_id` varchar(255) DEFAULT NULL,`timestamp` datetime DEFAULT NULL,`correct` varchar(5),plus_member varchar(5),  DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=5227 DEFAULT CHARSET=latin1;";
		mysql_query($sql);
		$sql = "CREATE TABLE `clicks_total` (`id` int(11) NOT NULL AUTO_INCREMENT,`site_id` varchar(255) DEFAULT NULL,`total` int(11) DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;";
		mysql_query($sql);	
	}
	if($_REQUEST["pa"] == "REPORT_TOTAL_BY_SITE"){
		//Total for site (dog/cat/litter) by year, by month, by day
		//Total for plus users (combines both kat and dog) by year, by month, by day
		$start = $_GET["start"];
		$end = $_GET["end"];
		$group_by = $_GET["group"];
		$only_plus = $_GET["plus"];
		if($only_plus != "YES"){
			$sql = "select site_id, sum(clicks) as total_clicks, sum(`value`) as total_value," . $group_by . "(`day`) as date_total from clicks_total_day where `day` between '" . $start . "' and '" . $end . "' and site_id != '' group by site_id," . $group_by . "(`day`)";
		}else{
			$sql = "select site_id, sum(clicks) as total_clicks, sum(`value`) as total_value," . $group_by . "(`day`) as date_total from clicks_total_day where `day` between '" . $start . "' and '" . $end . "' and site_id != '' and plus_member = 'YES' group by site_id," . $group_by . "(`day`)";
		}
		$result = mysql_query($sql);
		$json =  "[";
		while($row = mysql_fetch_array($result)){
			$json .=  '{"SITE_ID" : "' . $row["site_id"] . '",';	
			$json .=  '"DATE" : "' . $row["date_total"] . '",';	
			$json .=  '"TOTAL_CLICKS" : "' . $row["total_clicks"] . '",';	
			$json .=  '"TOTAL_VALUE" : "' . $row["total_value"] . '"},';	
		}	
		$json = substr_replace($json ,"",-1);
		$json .=  "]";
		echo $json;
	}
	if($_REQUEST["pa"] == "REPORT_ALL_TIME_SITE_TOTAL"){
		//All time total by site (dog/cat/litter)
		$site_id = $_REQUEST["site_id"];
		$sql = "select sum(clicks) as clicks, sum(`value`) as `value` from clicks_total_day where site_id = ". $site_id;
		if(isset($_REQUEST["plus"])){
			if($_REQUEST["plus"] == "YES"){
				$sql .= " and plus = 'YES'";	
			}
		}
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		echo '[{"CLICKCOUNT" : "' . $row["clicks"] . '", "CLICKVALUE" : "' . $row["value"] . '"}]';
	}		
	if($_REQUEST["pa"] == "REPORT_ALL_TIME_SITE_TOTAL_USER"){
		//All time total by site (dog/cat/litter)
		$site_id = $_REQUEST["site_id"];
		$userid = $_REQUEST["user_id"];
		$sql = "select * from clicks_per_user where site_id = " . $site_id . " and user_id = " . $userid;
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		$click_percent = ($row["correct"]/$row["clicks"]) * 100;
		echo '[{';
		echo '"CLICKCOUNT" : "' . $row["clicks"] . '",';
		echo '"CLICKCOUNTCORRECT" : "' . $row["correct"] . '",';
		echo '"CLICKPERCENTCORRECT" : "' . $click_percent . '"';
		echo '}]';
	}		
	if($_REQUEST["pa"] == "REPORT_ALL_TIME_SITE_TOTAL_PLUS"){
		//All time total for plus members
		$site_id = $_REQUEST["site_id"];
		$sql = "select sum(click_value) as click_value from clicks_total where plus_member = 'YES' and site_id = '". $site_id . "'";
		$result = mysql_query($sql);
		echo "[";
		while($row = mysql_fetch_array($result)){
			echo '{"CLICKCOUNT" : "' . $row["total"] . '"}';
		}
		echo "]";
	}		
?>

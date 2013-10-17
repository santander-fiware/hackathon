<?php

	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: GET, POST'); 

	if (!isset($_GET['id']))
		exit();
	mysql_connect("localhost", "root", "root") or die(mysql_error());
	mysql_select_db("bigdata") or die(mysql_error());
	
	$result = mysql_query("SELECT * FROM traffic_history WHERE id='".$_GET['id']."' ORDER BY day_year DESC, time_hour DESC LIMIT 30") or die (mysql_error());
	
	$graphic = array(); 
	$graphic['title'] = array('text' => $_GET['id'].' sensor traffic data', 'x' => -20);
	$graphic['yAxis'] = array('title' => array('text' => "Traffic intensity"), 
							  'plotlines' => array('value' => 0, 'width' => 1, 'color' => "#808080"));
	$graphic['tooltip'] = array('valueSuffix' => "Cars/min");
	$graphic['legend'] = array('layout' => "vertical", 'align' => "right", 'verticalAlign' => "middle", 'borderWidth' => 0);
	
	$series = array();
	$seriesMember =	array('name' => "Sensor data", 'data' => array());
	$xAxis = array();
	while($row = mysql_fetch_array($result))
	{
		array_push($seriesMember['data'], (int)$row['value']);
		array_push($xAxis, getDateFromDay($row['day_year'], 2013)->format('M-d')." ".$row['time_hour']);
	}
	$graphic['xAxis'] = array('categories' => array_reverse($xAxis));
	
	array_push($series, array_reverse($seriesMember));
	$graphic['series'] = $series;
    echo json_encode($graphic);
	
	function getDateFromDay($dayOfYear, $year) {
		$date = DateTime::createFromFormat('z Y', strval($dayOfYear) . ' ' . strval($year));
		return $date;
	}
	
?>
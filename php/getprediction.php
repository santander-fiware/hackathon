<?php
	
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: GET, POST'); 

	if (!isset($_GET['day_week']))
		exit();
	if (!isset($_GET['day_year']))
		exit();
	if (!isset($_GET['time_hour']))
		exit();
		
	mysql_connect("localhost", "root", "root") or die(mysql_error());
	mysql_select_db("bigdata") or die(mysql_error());
	
	$result = mysql_query("SELECT * FROM traffic_weight") or die (mysql_error());
	
	$weights = array();
    $scaling = array(); 
	while($row = mysql_fetch_array($result))
	{
		$weights[$row['id']] = array($row['zero_coef'], $row['day_week'] , $row['day_year'], $row['time_hour']);
		$scaling[$row['id']] = array('mean' => array($row['dw_mean'], $row['dy_mean'], $row['th_mean']),
												 'variance' => array($row['dw_var'], $row['dy_var'], $row['th_var']));
	}
	
	$result = mysql_query("SELECT * FROM traffic_sensors") or die (mysql_error());
	$sensors = array();
	$t = array($_GET['day_week'], $_GET['day_year'], $_GET['time_hour']);
	while($row = mysql_fetch_array($result))
	{
		
		$w = $weights[$row['id']];
		$s = $scaling[$row['id']];
		array_push($sensors, array('id' => $row['id'], 'lat' => $row['lat'], 'lng' => $row['lng'], 'count' => predict(scale($t, $s), $w)));
	}
    echo json_encode($sensors);
	
	
	function hypothesis($x, $weights)
	{
		$score = $weights[0]; // free weight
		$k = sizeof($x);
		// Calculate dot product
		for ($i = 0; $i < $k; $i++)
			$score += $weights[$i+1] * $x[$i];
		// Run through the sigmoid (logistic) function
		return $score;
	}
	 
	function predict($input, $weights)
	{
		$output = hypothesis($input, $weights);
	
		if(ceil($output) < 1){return 1;} else{return ceil($output);}
	}
	 
	function scale($input, $scaling)
	{
		foreach ($input as $f => &$value) {
			$value = ($value - $scaling['mean'][$f]) /
						$scaling['variance'][$f];
		}
		return $input;
	}
?>
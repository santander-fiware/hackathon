<?php
	
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: GET, POST'); 

	if (!isset($_GET['count']))
		exit();
		
	$maxValue = 99;
	$maxCount = 20;
	
	$count = $_GET['count'];
	if ($count < 1)
		$count = 1;
	
	if ($maxCount < $count)
		$count = $maxCount;
	
	$red = ceil($count / $maxCount * $maxValue); 
	$green = ceil((1 - $count / $maxCount) * $maxValue);
	// create a new cURL resource
	$ch = curl_init();

	// set URL and other appropriate options
	curl_setopt($ch, CURLOPT_URL, "http://130.206.80.45:5371/m2m/v2/services/HACKSANTANDER/devices/RGBS:48:99:26:0006/command");
	
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
	
	$data = array('commandML' => "<paid:command name=\"SET\"><paid:cmdParam name=\"FreeText\"> <swe:Text><swe:value>FIZCOMMAND ".$red."-".$green."-00</swe:value></swe:Text></paid:cmdParam></paid:command>");
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

	// grab URL and pass it to the browser
	curl_exec($ch);

	// close cURL resource, and free up system resources
	curl_close($ch);

?>
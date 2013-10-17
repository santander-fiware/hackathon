<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST'); 

error_reporting(E_ALL);

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

$time_start = microtime_float();

set_time_limit (0); 

 if (!isset($_GET['sensor']))
	exit();
 
mysql_connect("localhost", "root", "root") or die(mysql_error());
mysql_select_db("bigdata") or die(mysql_error());




$training = array();
$labels = array();

$result = mysql_query("SELECT * FROM traffic_history WHERE id='".$_GET['sensor']."'") or die (mysql_error());

while($row = mysql_fetch_array($result))
{
	array_push($training, array($row['day_week'], $row['day_year'], $row['time_hour']));
	array_push($labels, $row['value']);
}

 
define('NUM_FEATURES', 3);

$NUM_SAMPLES = sizeof($training);

if ($NUM_SAMPLES < 1)
	exit();

$weights = array();
for ($j=0; $j < NUM_FEATURES+1; $j++)
    $weights[$j] = mt_rand()/mt_getrandmax()*0.0;
 
$scaling = calc_feature_scaling($training);
 
$learning_rate = 0.1;
$steps = 2000; 

$temp = array(); 
for ($n = 0; $n < $steps; $n++) {
 
    for ($j = 0; $j < NUM_FEATURES+1; $j++) {
        $sum_m = 0.0;
        for ($i = 0; $i < $NUM_SAMPLES; $i++) {
            $scaled_data = scale($training[$i], $scaling);
            $h = hypothesis($scaled_data, $weights);
            // The first weight has a dummy 1 "feature" value
            $part = ($h - $labels[$i]) * ($j==0 ? 1.0 : $scaled_data[$j-1]);
            $sum_m = $sum_m + $part;
        }
        $temp[$j] = $weights[$j] - $learning_rate * $sum_m/$NUM_SAMPLES;
    }
 
    $weights = $temp;
}
 
//echo "Weights: ", vector_to_str($weights), "\n";

$result = mysql_query("INSERT INTO traffic_weight (id, zero_coef, day_week, day_year, time_hour, dw_mean, dw_var, dy_mean, dy_var, th_mean, th_var) VALUES ('".$_GET['sensor']."', ".$weights[0].", ".$weights[1].", ".$weights[2].", ".$weights[3].", ".$scaling['mean'][0].", ".$scaling['variance'][0].", ".$scaling['mean'][1].", ".$scaling['variance'][1].", ".$scaling['mean'][2].", ".$scaling['variance'][2].")
ON DUPLICATE KEY UPDATE
id='".$_GET['sensor']."', zero_coef=".$weights[0].", day_week=".$weights[1].", day_year=".$weights[2].", time_hour=".$weights[3].", dw_mean=".$scaling['mean'][0].", dw_var=".$scaling['variance'][0].", dy_mean=".$scaling['mean'][1].", dy_var=".$scaling['variance'][1].", th_mean=".$scaling['mean'][2].", th_var=".$scaling['variance'][2]) or die (mysql_error());

$time_end = microtime_float();
$time = $time_end - $time_start;
for ($f = 0; $f < NUM_FEATURES; $f++) 
{
	echo $scaling['mean'][$f]."\n";
	echo $scaling['variance'][$f]."\n";
} 
echo "Done in $time seconds\n";

 
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
    return $output;
}
 
function scale($input, $scaling)
{
    foreach ($input as $f => &$value) {
        $value = ($value - $scaling['mean'][$f]) /
                    $scaling['variance'][$f];
    }
    return $input;
}
 
function calc_feature_scaling($data)
{
    $mins = array_fill(0, NUM_FEATURES, INF);
    $maxs = array_fill(0, NUM_FEATURES, -INF);
    $sums = array_fill(0, NUM_FEATURES, 0);
    $scaling = array('mean' => array(),
                     'variance' => array());
    $N = sizeof($data);
    foreach ($data as $i => $row) {
        foreach ($row as $f => $value) {
            if ($value > $maxs[$f])
                $maxs[$f] = $value;
            if ($value < $mins[$f])
                $mins[$f] = $value;
            $sums[$f] += $value;
        }
    }
 
    for ($f = 0; $f < NUM_FEATURES; $f++) {
        $scaling['mean'][$f] = $sums[$f] / $N;
        $scaling['variance'][$f] = $maxs[$f] - $mins[$f];
        if ($scaling['variance'][$f] == 0)
            throw new Exception("Feature #$f has the same value in all the samples, invalid data");
    }
 
    return $scaling;
}
 
function vector_to_str($x)
{
    return '['.implode(", ", $x).']';
}
 
?>
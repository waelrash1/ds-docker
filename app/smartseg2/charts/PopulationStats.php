<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/src/jpgraph.php');
require_once ('jpgraph/src/jpgraph_bar.php');
require_once('../config.php');
require_once('../includes/functions.php');

$data = $_GET["data"];
$opt = $_GET["opt"];
$feature = $_GET["feature"];
$size = $_GET["size"];

$width = 400;
$height = 300;
$titleExtension = "";
$ttlMargin = 38;
if( $size === "small" ){
	$width = 200;
	$height = 150;
	$titleExtension = "\n(Population)";
	$ttlMargin = 38;
}
//Uncomment lines below if more sizes added to use
// elseif( $size === "large"){
	// $width = 400;
	// $height = 300;
	// $ttlMargin = 38;
// }
// else{
	// throw new Exception('No Default Value. Choose small/large');
// }

$jsonObj = json_decode(file_get_contents("../" . $data));

//need to calculate(0-100%) for y-axis or at least number of people for the moment
//need to decide intervals for arbitary data set for x axis ( currently 10 equal groupings)
//
$datax = array();

//get all data for x axis
foreach( $jsonObj as $key => $val ){
	try{
		$temp = $val[0];
		array_push($datax, $temp[$opt]);
	}
	catch(Exception $e){
		break;
	}
}

//get max value in that data
$maxTest = max($datax);
//get total number of data entries
$memberSize = count($datax);
// decide on interval which is max value divided by 10 and taken the ceiling
$interval = $maxTest/10;

if( $interval !== (float)0 ){
	$interval = ceil($interval);
}
else{
	$interval = 1;
}

//we need to save highest index because we CANNOT count() it afterwards 
//(example array(1 => 2, 34=> 45, 138 => 800); rest of the indexes need to be set to 0 customer count but still exist for correct histogram)
$highestIndex = 0;
//we need data1x for tick marks
$data1x = array();
$data1y = array();
foreach($datax as $record){
	$index = (int)($record/$interval);	//would be great is could use bit-shift, but only works for powers of 2 intervals and checking will waste advantage anyway
	if(isset($data1y[$index])){ 
		$data1y[$index] ++;
	}
	else{
		$data1y[$index] = 1;
		$data1x[$index] = $index*$interval;
	}
	
	if( $index > $highestIndex ){
		$highestIndex = $index;
	}
}

unset($datax);
//fill everything between 0 and highest index of interval with 0 if not set
for($i = 0; $i < $highestIndex+1; $i++){
	if(!isset($data1y[$i])){ 
		$data1y[$i] = 0;
		$data1x[$i] = $i*$interval;
	}
}

$graph = new Graph($width,$height);    
$graph->SetScale("intlin", 0 , 100);				//we need int scale for readability, the bars represent data BETWEEN ticks not the tick value itself like on text scale

//we need one extra tick because we use integer scale
$last = $data1x[count($data1x)-1];		//we get last element
array_push($data1x, $last+$interval);	//we push last lelement+interval to data1x
$graph->xaxis->SetTickLabels($data1x);	//data1x is passed by value
unset($data1x);							//we free data1x since it's not used for anything else but is reaonably sized array (never too large but still)

//we set ticks to dense to show odd tick marks in case our x-axis scale ends with odd mark ( otherwise a meaningless integer will be shown as last mark )
$graph->SetTickDensity(TICKD_NORMAL , TICKD_DENSE);
$graph->img->SetMargin(50,20,20,40);

$dc = count($data1y);
for( $i = 0; $i < $dc; $i++){
	toPercent($data1y[$i], $memberSize);
}
// Create the bar plots
$bplot  = new BarPlot($data1y);
//set width to resemble the bar allignment from java program ( leave 10% of space between each bar for readibility, can be changed at will )
$bplot->SetWidth(0.9);


//need to resolve titles
$title = strrchr($feature, '_');
if($title){
	$title = substr($title, 1);
}
else{
	$title = $feature;
}
	 
$graph->title->Set($title.$titleExtension);			//$defaultNames[$feature]);
$graph->yaxis->title->Set("Percent %");
 
$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->SetTitlemargin($ttlMargin);

// Display the graph
$graph->Add($bplot);

// Set color for the frame of each bar (must be AFTER adding to graph)
$bplot->SetColor("AntiqueWhite3");
$bplot->SetFillColor ( "antiquewhite" );
$graph->SetBackgroundGradient($app_background_color, $app_background_color, GRAD_HOR, BGRAD_FRAME);

$graph->Stroke();
?>

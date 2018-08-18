<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/src/jpgraph.php');
require_once ('jpgraph/src/jpgraph_bar.php');
require_once('../config.php');

//horizontal cluster graph
$filename = $_GET["filename"];
$t = $_GET["t"];
$jsonObj = json_decode(file_get_contents("../" . $filename));

$features = $jsonObj->features;		//for some reason exactly same file made for news sample with features key doesn't see it when executed
$datay = array();
$datax = array();

//check that it's not for all segments
if( $t == $jsonObj->n_clusters ){
	$t = "population";
}

//saving to single array to sort
$orderedFeatures = array();
foreach($features as $feature){
	$title = strrchr($feature, '_');
	if($title && $title !== "_TOTAL"){
		$title = substr($title, 1);
		if( $t === "population"){
			//array_push($datay,$jsonObj->cluster_stats->$t->$feature/$jsonObj->cluster_stats->population->size);
			$orderedFeatures[$title] = $jsonObj->cluster_stats->$t->$feature/$jsonObj->cluster_stats->population->size;
		}
		else{
			//array_push($datay,$jsonObj->cluster_stats->$t->$feature->average);
			$orderedFeatures[$title] = $jsonObj->cluster_stats->$t->$feature->average;
		}
		//array_push($datax,$title);
	}
}

//sorting the single array
arsort($orderedFeatures);

//splitting into 2 arrays for labels and values
foreach( $orderedFeatures as $dx=>$dy ){
	array_push($datax, $dx);
	array_push($datay, $dy);
}

// Set the basic parameters of the graph
$graph = new Graph(400,400);
$graph->SetScale('textlin');
 
// Rotate graph 90 degrees and set margin
$graph->Set90AndMargin(90,20,40,20);

if( count($datay) > 0 ){	 
	// Setup title
	$graph->title->Set("Overall Feature Counts");
	 
	// Setup X-axis
	$graph->xaxis->SetTickLabels($datax);
	 
	// Some extra margin looks nicer
	$graph->xaxis->SetLabelMargin(10);
	$graph->SetBackgroundGradient($app_background_color, $app_background_color, GRAD_HOR, BGRAD_FRAME);
	 
	// Label align for X-axis
	$graph->xaxis->SetLabelAlign('right','center');

	// for y axis
	$graph->yaxis->SetLabelAlign('bottom','bottom', 'center');
	 
	// Add some grace to y-axis so the bars doesn't go
	// all the way to the end of the plot area
	$graph->yaxis->scale->SetGrace(20);
	 
	// Now create a bar pot
	$bplot = new BarPlot($datay);
	//$bplot->SetShadow(); caused pixels outside of frame
	 
	//You can change the width of the bars if you like
	//$bplot->SetWidth(0.5);
	 
	// We want to display the value of each bar at the top
	$bplot->value->Show();
	$bplot->value->SetFont(FF_ARIAL,FS_BOLD,12);
	$bplot->value->SetAlign('left','center');
	$bplot->value->SetColor('black','darkred');
	$bplot->value->SetFormat('%.1f mkr');
	 
	// Add the bar to the graph
	$graph->Add($bplot);
	$bplot->SetFillColor($t === "population" ? "antiquewhite" : $colours[$t]);
	$bplot->SetColor($t === "population" ? "AntiqueWhite3" : $outline_colours[$t]);
}
else{
	$graph->Set90AndMargin(0,0,0,0);
	// Setup title
	$graph->title->Set("No Features To Overview\nChoose other set of features to view the graph");
	$graph->xaxis->HideLabels($aHide=true);
	$graph->yaxis->HideLabels($aHide=true);
	// Now create a bar pot
	$bplot = new BarPlot(array(0));
	$bplot->value->Show(false);
	$graph->Add($bplot);
}
// .. and stroke the graph
$graph->Stroke();
?>

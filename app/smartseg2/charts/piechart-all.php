<?php // content="text/plain; charset=utf-8"
require_once('../includes/functions.php');
require_once('jpgraph/src/jpgraph.php');
require_once('jpgraph/src/jpgraph_pie.php');
require_once('../config.php');

$filename = $_GET["filename"];
$json = decode_json_file("../" . $filename);

// Some data
$data = array();
$all = $json->cluster_stats->population->size;
for ($i = 0; $i < $json->n_clusters; $i++) {
  array_push($data, $json->cluster_stats->$i->size);
}
$data = array_reverse($data);

$fillColours = array_reverse(array_slice($colours, 0, $json->n_clusters));

// Create the Pie Graph. 
$graph = new PieGraph(300,300);
$graph->SetMarginColor($app_background_color);

// Create
$p1 = new PiePlot($data);
$p1->SetShadow();
$graph->Add($p1);

$p1->SetCenter(150,150);
$p1->SetStartAngle(90);
$p1->SetLabelType(PIE_VALUE_PER);
$lbl = array("Seg 1\n%.1f%%","Seg 2\n%.1f%%","Seg 3\n%.1f%%","Seg 4\n%.1f%%","Seg 5\n%.1f%%","Seg 6\n%.1f%%","Seg 7\n%.1f%%","Seg 8\n%.1f%%","Seg 9\n%.1f%%","Seg 10\n%.1f%%");
$p1->SetLabels(array_reverse(array_slice($lbl,0,$json->n_clusters)), 1);
$p1->SetGuideLines(true, true, true);
$p1->SetGuideLinesAdjust(2);
$p1->SetSliceColors($fillColours);
$graph->Stroke();

?>

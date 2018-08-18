<?php
$brief = 1;
?>
  <ul id="segmentlist">
    <li><a href="#" onclick="javascript:showpane('segmentsummary', <?php echo $json->n_clusters; ?>)" id="segmentsummarylink">Summary</a></li>
    <li></li>
<?php
  for ($t = 0; $t < $json->n_clusters; $t++) {
    $percent_in = 100 * count($json->clusters->$t) / $totalMembers;
?>
    <li>
      <a href="#" onclick="javascript:showpane('segment<?php echo ($t + 1); ?>', <?php echo $json->n_clusters; ?>)" id="segment<?php echo ($t + 1); ?>link"><span>Segment <?php echo ($t + 1); ?></span> [<?php echo number_format($percent_in, 1); ?>%]</a>
      <?php include("includes/segdescription.php"); ?>

    </li>
<?php
  }
?>
    <li></li>
    <li><a href="#" onclick="javascript:showpane('summaryfeatures', <?php echo $json->n_clusters; ?>)" id="summaryfeatureslink">Features</a></li>
    <li></li>
    <li><a href="#" onclick="javascript:showpane('population', <?php echo $json->n_clusters; ?>)" id="populationlink">Population</a></li>
  </ul>
<?php
$brief = 0;
?>

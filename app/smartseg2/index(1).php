<?php
include("includes/initialize.php");
require_once("includes/functions.php");
?>
<html>
<?php include("includes/headers.php"); ?>
  <body>
<?php include("includes/menu.php"); ?>

<?php
if (isset($_POST["clusterer"]) || isset($_GET["clusterer"])) {
  include("includes/run.clustering.php");

  $json = decode_json_file("segmenter/" . $filename);
  $jsondata = decode_json_file("segmenter/" . $datafilename);
?>
    <div id="globalview">
<?php
  $totalMembers = 0;
  for ($t = 0; $t < $json->n_clusters; $t++){
    $totalMembers += count($json->clusters->$t);
  }

  include("includes/globalview.php");
?>
    </div>
    <div id="rightpane">
      <div id="segmentsummary" style="display: block;">
<?php
  include("includes/segmentsummary.php");
?>
      </div>
<?php
  for ($t = 0; $t < $json->n_clusters; $t++) {
?>
      <div class="segmentdetails" id="segment<?php echo ($t + 1); ?>" style="display: none;">
<?php include("includes/clusterinfo.php"); ?>
        <div class="fixfloat"></div>
      </div>
<?php
  }
?>
      <div id="summaryfeatures" style="display: none;">
<?php include("includes/featuresinfo.php"); ?>
        <div class="fixfloat"></div>
      </div>
      <div id="population" style="display: none;">
<?php include("includes/populationinfo.php"); ?>
        <div class="fixfloat"></div>
      </div>
    </div>
  </body>
</html>
<?php
}
else {
  include("includes/guidelines.php");
}
?>

<?php
require_once("config.php");
require_once("includes/functions.php");

$ct = $t; 
$clusterCount = $json->n_clusters;
$memberCount = count($json->clusters->$ct);

$feats = $json->features;
$featprobs = array();
foreach ($feats as $featid => $feat) {
  $featprobs[$feat] = array("in" => 0, "all" => 0);
}

foreach ($features as $featid => $feature) {
  foreach ($json->clusters->$ct as $custid) {
    echo $jsondata->{$custid}[0][$featid] . "<br />"; exit(0);
  }
}

list($rangemin, $rangemax) = impulseLimits($json);

$start = 0;
if ($ct != $clusterCount) {
  $start = $ct;
  $clusterCount = $ct + 1;
}
 
$allZscores = array();
for($ct = $start; $ct < $clusterCount; $ct++){
  $features = array();
  $zscores = array();
  foreach ($json->cluster_zscores->$ct as $feature => $zscore) {
    if ($feature != "size" && (($f == 0) || (strrchr($feature, '_') !== "_TOTAL"))) {
      array_push($features, str_replace("TransTypePerMonth_", "", $feature));
      array_push($zscores, $zscore);
    }
  }
  array_push($allZscores, $zscores);
}
?>
    <svg id="impulse<?php echo $ct; ?>"></svg>
    <script>
      nv.addGraph(function() {
        var impulsechart_<?php echo $ct; ?> = nv.models.multiBarHorizontalChart()
            .x(function(d) { return d.label })
            .y(function(d) { return d.value })
            .showValues(false)
            .showControls(false)
            .showLegend(false)
            .forceY([<?php echo $rangemin; ?>,<?php echo $rangemax; ?>])
            .color(d3.scale.histocolors_<?php echo $t; ?>().range())
            .tooltips(true)
            .width(700).height(500);

        impulsechart_<?php echo $ct; ?>.yAxis
            .tickFormat(d3.format(',.2f'));

        impulsechart_<?php echo $ct; ?>.margin().left = 100

        d3.select('#impulse<?php echo $ct; ?>')
            .datum(impulsedata_<?php echo $ct; ?>)
            .transition().duration(500)
            .call(impulsechart_<?php echo $ct; ?>);

//        d3.selectAll(".nv-bar").on("click", function () {
//            alert("clicked");
//        });

        d3.selectAll("#impulse<?php echo $ct; ?> g.tick text").on("click", function(d) {
            showHisto(d, "<?php echo $ct; ?>");
//            alert("clicked on " + d + " on segment <?php echo $ct; ?>");
        });

        nv.utils.windowResize(impulsechart_<?php echo $ct; ?>.update);

        return impulsechart_<?php echo $ct; ?>;
      });

      d3.scale.histocolors_<?php echo $t; ?> = function() {
        var myColors<?php echo $t; ?> = ["<?php echo $colours[$t]; ?>"];

        return d3.scale.ordinal().range(myColors<?php echo $t; ?>);
      }

      function impulsedata_<?php echo $ct; ?>() {
        return [
        {
          key: "Segment <?php echo ($t+1); ?>",
          values: [
<?php
foreach ($features as $key => $feature) {
?>
            {"label":"<?php echo $feature; ?>","value":<?php echo $allZscores[0][$key]; ?>},
<?php
}
?>
          ]
        }
        ];
      }
    </script>

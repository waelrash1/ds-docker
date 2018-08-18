<?php
require_once("config.php");
require_once("includes/functions.php");

list($rangemin, $rangemax) = impulseLimits($json);

$allZscores = array();
$segments = array();
for ($cluster = 0; $cluster < $json->n_clusters; $cluster++) {
  array_push($segments, "Segment #" . ($cluster + 1));
  foreach ($json->cluster_zscores->$cluster as $feature => $zscore) {
    if ($feature == $currfeature) {
      array_push($allZscores, $zscore);
    }
  }
}

?>
    <svg id="impulse_<?php echo $featureshort; ?>"></svg>
    <script>
      nv.addGraph(function() {
        var impulsechart_<?php echo $featureshort; ?> = nv.models.multiBarHorizontalChart()
            .x(function(d) { return d.label })
            .y(function(d) { return d.value })
            .showValues(false)
            .showControls(false)
            .showLegend(false)
            .forceY([<?php echo $rangemin; ?>,<?php echo $rangemax; ?>])
            .color(d3.scale.histocolors_<?php echo $featureshort; ?>().range())
            .tooltips(true)
            .width(700).height(500);

        impulsechart_<?php echo $featureshort; ?>.yAxis
            .tickFormat(d3.format(',.2f'));

        impulsechart_<?php echo $featureshort; ?>.margin().left = 100

        d3.select('#impulse_<?php echo $featureshort; ?>')
            .datum(impulsedata_<?php echo $featureshort; ?>)
            .transition().duration(500)
            .call(impulsechart_<?php echo $featureshort; ?>);

        nv.utils.windowResize(impulsechart_<?php echo $featureshort; ?>.update);

        return impulsechart_<?php echo $featureshort; ?>;
      });

      d3.scale.histocolors_<?php echo $featureshort; ?> = function() {
        var myColors<?php echo $featureshort; ?> = ["<?php echo $colours[$featureshort]; ?>"];

        return d3.scale.ordinal().range(myColors<?php echo $featureshort; ?>);
      }

      function impulsedata_<?php echo $featureshort; ?>() {
        return [
        {
          key: "<?php echo $currfeature; ?>",
          values: [
<?php
foreach ($segments as $key => $segment) {
?>
            {"label":"<?php echo $segment; ?>","value":<?php echo $allZscores[$key]; ?>},
<?php
}
?>
          ]
        }
        ];
      }
    </script>

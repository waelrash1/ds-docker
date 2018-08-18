<?php
require_once("includes/functions.php");
require_once("config.php");

// TODO: Incorporate angle in pie chart
//$previous = 0;
//for ($s = 0; $s < $t; $s++) {
//  $previous += $json->cluster_stats->$s->size;
//}
$in = $json->cluster_stats->$t->size;
$all = $json->cluster_stats->population->size;
$out = $all - $in;
$data = array($out, $in);

//$angle = 90 - 360 * $previous / $all;
//$angle = ($angle >= 0)?$angle:($angle + 360);
?>
    <svg id="pie<?php echo $t; ?>" style="width: 250px; height: 250px;"></svg>
    <script>
      nv.addGraph(function() {
        var chart<?php echo $t; ?> = nv.models.pieChart()
          .x(function(d) { return d.label })
          .y(function(d) { return d.value })
          .showLabels(true)
          .color(d3.scale.myColors<?php echo $t; ?>().range())
          .showLegend(false)
          .tooltips(true)
          .width(250).height(250);

        d3.select("#pie<?php echo $t; ?>")
          .datum(data<?php echo $t; ?>())
          .transition().duration(1200)
          .call(chart<?php echo $t; ?>);

        return chart<?php echo $t; ?>;
      });

      d3.scale.myColors<?php echo $t; ?> = function() {
        var myColors<?php echo $t; ?> = ["<?php echo $colours[$t]; ?>", "#fec"];//"#faebd7"];

        return d3.scale.ordinal().range(myColors<?php echo $t; ?>);
      }

      function data<?php echo $t; ?>() {
        return [
//        {
//        key: "Segment sizes",
//        values: [
          {"label":"Segment <?php echo ($t + 1); ?>","value":<?php echo $in; ?>},
          {"label":"Rest", "value":<?php echo $out; ?>} 
//        ]
//        }
        ];
      }
    </script>

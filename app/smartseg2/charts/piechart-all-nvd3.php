<?php
require_once("includes/functions.php");
require_once("config.php");
$jsonall = decode_json_file($ifilename);

$data = array();
for ($s = 0; $s < $jsonall->n_clusters; $s++) {
  array_push($data, $jsonall->cluster_stats->$s->size);
}
?>
    <svg id="pieall" style="width: 300px; height: 300px;"></svg>
    <script>
      nv.addGraph(function() {
        var chart_pieall = nv.models.pieChart()
          .x(function(d) { return d.label })
          .y(function(d) { return d.value })
          .showLabels(true)
          .color(d3.scale.myColors_pieall().range())
          .showLegend(false)
          .tooltips(true)
          .width(300).height(300);

        d3.select("#pieall")
          .datum(data_pieall())
          .transition().duration(1200)
          .call(chart_pieall);

        d3.selectAll(".nv-slice").on("click", function () {
          slicetext = this.getElementsByClassName("nv-label")[0].textContent;
          segid = slicetext.replace(" ", "").toLowerCase();
          showpane(segid);
        });

        return chart_pieall;
      });

      d3.scale.myColors_pieall = function() {
        var myColors_pieall = [<?php for ($k = 0; $k < 29; $k++) { echo "\"" . $colours[$k] . "\", "; } echo "\"" . $colours[29] . "\""; ?>];

        return d3.scale.ordinal().range(myColors_pieall);
      }

      function data_pieall() {
        return [
//        {
//        key: "Segment sizes",
//        values: [
<?php
  $segm = 0;
  foreach ($data as $value) {
    $segm++;
?>
          {"label":"Segment <?php echo $segm; ?>","value":<?php echo $value; ?>},
<?php
  }
?>
//        ]
//        }
        ];
      }
    </script>

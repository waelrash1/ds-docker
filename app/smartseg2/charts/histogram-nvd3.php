<?php
require_once("includes/functions.php");
require_once("config.php");

$t = $it;

$opt = $iopt;
$feature = $ifeature;
$size = $isize;

$width = 700;
$height = 500;
$ttlMargin = 38;
if ($size === "small") {
  $width = 200;
  $height = 150;
}

$clusterUsers = $json->clusters->$t;
$memberSize = $json->cluster_stats->population->size;

$datax = array();

foreach ($jsondata as $key => $val) {
  $temp = $val[0];
  array_push($datax, $temp[$opt]);
}

$maxTest = max($datax);
$interval = $maxTest / 10;

if ($interval !== (float)0) {
  $interval = ceil($interval);
}
else {
  $interval = 1;
}

$highestIndex = 0;
$data1x = array();
$data1y = array();
foreach($datax as $record){
  $index = (int)($record / $interval);
  if (isset($data1y[$index])) { 
    $data1y[$index]++;
  }
  else {
    $data1y[$index] = 1;
    $data1x[$index] = $index * $interval;
  }

  if ($index > $highestIndex) {
    $highestIndex = $index;
  }
}

$data2y = array();
$limit = count($clusterUsers);
$highestIndex = 0;
for ($i = 0; $i < $limit; $i++) {
  $index = (int)($jsondata->{$clusterUsers[$i]}[0][$opt] / $interval);
  if (isset($data2y[$index])) { 
    $data2y[$index]++;
  }
  else {
    $data2y[$index] = 1;
  }

  if ($index > $highestIndex) {
    $highestIndex = $index;
  }
}

$last = $data1x[count($data1x)-1];
array_push($data1x, $last+$interval);

$dc = count($data1y);
for ($i = 0; $i < $dc; $i++) {
  toPercent($data1y[$i], $memberSize);
}
$dc = count($data2y);
for ($i = 0; $i < $dc; $i++) {
  toPercent($data2y[$i], $limit);
}

sort($data1x);
?>
    <svg id="histogram_<?php echo ($t + 1); ?>_<?php echo str_replace("TransTypePerMonth_", "", $feature); ?><?php $output = ($size == "small")?"_" . $size:""; echo $output; ?>" style="width: <?php echo $width; ?>; height: <?php echo $height; ?>;"></svg>
    <script>
      nv.addGraph(function() {
        var chart_<?php echo $t; ?>_<?php echo $feature; ?>_<?php echo $size; ?> = nv.models.multiBarChart()
            .tooltips(true)
            .stacked(false)
            .showControls(false)
            .showLegend(false)
            .reduceXTicks(false)
            .color(d3.scale.myColors_<?php echo $t; ?>_<?php echo $feature; ?>_<?php echo $size; ?>().range())
            .width(<?php echo $width; ?>).height(<?php echo $height; ?>);

        chart_<?php echo $t; ?>_<?php echo $feature; ?>_<?php echo $size; ?>.xAxis
            .tickFormat(d3.format(',f'));

        chart_<?php echo $t; ?>_<?php echo $feature; ?>_<?php echo $size; ?>.yAxis
            .tickFormat(d3.format(',.1f'));

        d3.select('#histogram_<?php echo ($t + 1); ?>_<?php echo str_replace("TransTypePerMonth_", "", $feature); ?><?php $output = ($size == "small")?"_" . $size:""; echo $output; ?>')
            .datum(histodata_<?php echo $t; ?>_<?php echo $feature; ?>_<?php echo $size; ?>())
            .transition().duration(500).call(chart_<?php echo $t; ?>_<?php echo $feature; ?>_<?php echo $size; ?>);

        nv.utils.windowResize(chart_<?php echo $t; ?>_<?php echo $feature; ?>_<?php echo $size; ?>.update);

        return chart_<?php echo $t; ?>_<?php echo $feature; ?>_<?php echo $size; ?>;
      });

      d3.scale.myColors_<?php echo $t; ?>_<?php echo $feature; ?>_<?php echo $size; ?> = function() {
        var myColors<?php echo $t; ?> = ["<?php echo $colours[$t]; ?>", "#faebd7"];

        return d3.scale.ordinal().range(myColors<?php echo $t; ?>);
      }

      function histodata_<?php echo $t; ?>_<?php echo $feature; ?>_<?php echo $size; ?>() {
        return [
        {
          key: "Segment <?php echo ($t+1); ?>",
          values: [
<?php
foreach ($data1x as $key => $x) {
?>
            {x:<?php echo $x; ?>,y:<?php echo isset($data2y[$key])?$data2y[$key]:"0"; ?>},
<?php
}
?>
          ]
        },
        {
          key: "Population",
          values: [
<?php
foreach ($data1x as $key => $x) {
?>
            {x:<?php echo $x; ?>,y:<?php echo isset($data1y[$key])?$data1y[$key]:"0"; ?>},
<?php
}
?>
          ]
        }
        ];
      }
    </script>

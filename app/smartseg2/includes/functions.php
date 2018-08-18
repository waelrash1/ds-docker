<?php
function decode_json_file($filename) {
  return json_decode(str_replace(" NaN", " 0", file_get_contents($filename)));
}

function toPercent(&$number, $total) {
  $number = $number / $total * 100;
}

$aMin = -9999.0; 
$aMax = 9999.0;
function impulseLimits(&$json) {
  if ($GLOBALS['aMin'] === -9999.0) {
    $zvalues = array();
    for ($i = 0; $i < $json->n_clusters; $i++) {
      foreach ($json->features as $feat) {
        array_push($zvalues, $json->cluster_zscores->$i->$feat);
      }
    }

    $GLOBALS["aMin"] = floor(min($zvalues) - 0.05);
    $GLOBALS["aMax"] = ceil(max($zvalues) + 0.05);
  }

  return array($GLOBALS["aMin"], $GLOBALS["aMax"]);
}
?>

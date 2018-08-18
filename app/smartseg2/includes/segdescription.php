<?php
$output = "";

$stdDevs = array();
$stdFeatures = array();
if ($brief == 0) {
  $output .= "Customers in this segment have ";
}
else {
  $output .= "<ul>";
}

foreach ($json->cluster_zscores->$t as $feature => $zscore) {
  if ($feature !== 'size') {
    array_push($stdDevs, $zscore);
    array_push($stdFeatures, $feature);
  }
}

$below = array_keys($stdDevs, min($stdDevs));
$above = array_keys($stdDevs, max($stdDevs));

$tempAvg = ($json->cluster_stats->population->$stdFeatures[$below[0]])/($json->cluster_stats->population->size);
$tempBelow = $json->cluster_stats->$t->$stdFeatures[$below[0]]->average;

$title = str_replace("TransTypePerMonth_", "", $stdFeatures[$below[0]]);
if (!$title) {
  $title = $stdFeatures[$below[0]];
}

if ($stdDevs[$below[0]] > 0) {
  $result = $tempBelow - $tempAvg;
  $pcnt = $result*100/($tempBelow);
  if ($brief == 0) {
    $output .= "values for <strong>" . ($title === "Gender" ? $title." (male)" : $title) . "</strong> that are <strong>";
    if ($stdDevs[$below[0]] > 1) {
      $tester .= "significantly ";
    }
    $output .= "higher</strong> than the population average, ";
  }
  else {
    $output .= "<li><img src=\"img/high.png\" />" . (($stdDevs[$below[0]] > 1)?"Very ":"") . "High " . ($title === "Gender" ? $title." (male)" : $title) . "</li>";
  }
}
elseif ($stdDevs[$below[0]] < 0) {
  $result = $tempAvg - $tempBelow;
  $pcnt = $result*100/$tempAvg;
  if ($brief == 0) {
    $output .= "values for <strong>" . ($title === "Gender" ? $title." (male)" : $title) . "</strong> that are <strong>";
    if ($stdDevs[$below[0]] < -1) {
      $output .= "significantly ";
    }
    $output .= "lower</strong> than the population average, ";
  }
  else {
    $output .= "<li><img src=\"img/low.png\" />" . (($stdDevs[$below[0]] < -1)?"Very ":"") . "Low " . ($title === "Gender" ? $title." (male)" : $title) . "</li>";
  }
}

$tempAvg = ($json->cluster_stats->population->$stdFeatures[$above[0]])/($json->cluster_stats->population->size);
$tempAbove = $json->cluster_stats->$t->$stdFeatures[$above[0]]->average;
	
$title = str_replace("TransTypePerMonth_", "", $stdFeatures[$above[0]]);
if (!$title) {
  $title = $stdFeatures[$above[0]];
}

if ($stdDevs[$above[0]] > 0) {
  $result = $tempAbove - $tempAvg;
  $pcnt = $result*100/($tempAbove);
  if ($brief == 0) {
    $output .= " and values for <strong>" . ($title === "Gender" ? $title." (female)" : $title) . "</strong> that are <strong>";
    if ($stdDevs[$above[0]] > 1) {
      $output .= "significantly ";
    }
    $output .= "higher</strong> than the population average.";
  }
  else {
    $output .= "<li><img src=\"img/high.png\" />" . (($stdDevs[$above[0]] > 1)?"Very ":"") . "High " . ($title === "Gender" ? $title." (male)" : $title) . "</li>";
  }
}
elseif ($stdDevs[$above[0]] < 0) {
  $result = $tempAvg - $tempAbove;
  $pcnt = $result*100/$tempAvg;
  if ($brief == 0) {
    $output .= " and values for <strong>" . ($title === "Gender" ? $title." (female)" : $title) . "</strong> that are <strong>";
    if ($stdDevs[$above[0]] < -1) {
      $output .= "significantly ";
    }
    $output .= "lower</strong> than the population average.";
  }
  else {
    $output .= "<li><img src=\"img/low.png\" />" . (($stdDevs[$above[0]] < -1)?"Very ":"") . "Low " . ($title === "Gender" ? $title." (male)" : $title) . "</li>";
  }
}

if ($brief == 1) {
  $output .= "</ul>";
}

if (isset($tabs) && $tabs == 0) {
  echo "<p>" . $output . "</p>";
}
else {
  echo $output;
  $description = $output;
}
?>

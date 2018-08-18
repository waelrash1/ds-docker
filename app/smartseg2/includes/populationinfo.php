        <fieldset class="summary">
          <legend><strong>Population: <?php echo $json->cluster_stats->population->size; ?></strong></legend>
          <span class="content">
            <fieldset class="averages">
              <p><strong>Averages for key features:</strong></p>
              <ul>
<?php
  $flag = true;
  foreach ($json->cluster_stats->population as $feature => $value) {
    if ($feature != "size" && !strstr($feature, "TransTypePerMonth_")) {
      $flag = false;
      if ($feature == "Gender") {
?>
                <li><strong><?php echo $feature; ?>:</strong> <?php echo $value / $json->cluster_stats->population->size > 0.5 ? "More Female than Male": "More Male than Female"; ?></li>
<?php	}
		else{
?>
                <li><strong><?php echo $feature; ?>:</strong> <?php echo number_format($value / $json->cluster_stats->population->size, 1); ?></li>
			
<?php
      }
    }
  }
  
  if ($flag) { 
?>
				<li><strong>Examine the graph on the right</strong></li>
<?php
  }
  unset($flag);
?>
              </ul>
            </fieldset>
            <img src="charts/horizontalHistogram.php?filename=segmenter/<?php echo $filename ?>&t=population" alt="Horizontal histogram: Please Wait..." title="Overall Feature Counts" class="radar"/>
          </span>
        </fieldset>
<!--
        <fieldset class="statistics">
          <legend><strong>Statistics</strong></legend>
          <span class="content">
<?php  
  $index = 0;
  foreach ($json->features as $feature) {
    echo "          <img src=\"charts/PopulationStats.php?data=segmenter/" . $filenameD . "&opt=" . $index . "&feature=".$feature."&size=large\" alt=\"Stats: Please Wait...\" />\n";
    $index++;
  }
?>
          </span>
        </fieldset>
-->

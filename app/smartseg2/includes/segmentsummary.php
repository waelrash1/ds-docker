        <fieldset class="summary">
          <legend><strong>Overall Population: <?php echo $totalMembers; ?></strong></legend>
          <span class="content">
            <span class="left">
<?php
    $ifilename = "segmenter/" . $filename;
    include("charts/piechart-all-nvd3.php");
?>
            </span>
            <span class="middle">
              <p>The following is a summary of the key features for the <?php echo $json->n_clusters; ?> segments generated for the <?php echo $totalMembers; ?> customers:</p>
              <ul class="description">
<?php
  for ($seg = 0; $seg < $json->n_clusters; $seg++) {
    $t = $seg;
    echo "<li><a href=\"#\" onclick=\"javascript:showpane('segment" . ($t + 1) . "'," . $json->n_clusters . ")\"><strong>Segment #" . ($t + 1) . "</strong></a>: ";
    include("includes/segdescription.php");
    echo "</li>\n";
  }
?>
              </ul>
              <p>Click on the segment you would like to visualise in detail.</p>
            </span>
          </span>
        </fieldset>

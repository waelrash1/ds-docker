<?php
$percent_in = 100 * count($json->clusters->$t) / $totalMembers;
?>
        <fieldset class="summary">
          <legend><strong>Population of Segment #<?php echo ($t + 1); ?>: <?php echo ($t !== $json->n_clusters)?count($json->clusters->$t) . " / " . $totalMembers . " (" . number_format($percent_in, 1) . "%)":$totalMembers; ?></strong></legend>
          <span class="content">
            <span class="left">
<?php
  include("charts/piechart-nvd3.php");
?>
              <fieldset class="description">
<?php
  include("includes/segdescription.php");
?>
              </fieldset>

              <fieldset class="actions">
                <legend>Split segment</legend>
                <form class="split" method="post" action="./">
                  <input type="hidden" name="i" value="<?php echo $filename; ?>" />
                  <input type="hidden" name="n" value="<?php echo $t; ?>" />
                  <input type="hidden" name="db" value="<?php echo $db; ?>" />
                  <input type="hidden" name="clusterer" value="<?php echo $c; ?>" />
                  <input type="hidden" name="features" value="<?php echo $f; ?>" />
                  <select name="k">
                    <option value="-1">Split into...</option>
                    <option value="-1">Auto</option>
<?php for ($splitsegs = 2; $splitsegs <= 5; $splitsegs++) { ?>
                    <option value="<?php echo $splitsegs; ?>"><?php echo $splitsegs; ?> segments</option>
<?php } ?>
                  </select>
                  <input type="submit" name="submit" value="OK" />
                </form>
              </fieldset>
            </span>
            <span class="middle">
<?php
  include("charts/impulse-nvd3.php");

  $orderedFeatures = array();
  $orderedIndexes = array();
  $featureCount = count($json->features);
  for ($i = 0; $i < $featureCount; $i++) {
    $orderedFeatures[$json->features[$i]] = $json->cluster_zscores->$t->{$json->features[$i]}; // we save number at feature name
    $orderedIndexes[$json->features[$i]] = $i; // we save original position at feature name
  }
  $orderedFeatures = array_map("abs", $orderedFeatures); // make all value differences absolute
  arsort($orderedFeatures);

  foreach ($orderedFeatures as $key => $value) {
    $it = $t;
    $ifilename = "segmenter/" . $filename;
    $ifeature = $key;
    $isize = "large";
    $iopt = $orderedIndexes[$key];
    $datafilename = "segmenter/" . $filenameD;
?>
              <span class="histo" id="histo_<?php echo ($t + 1); ?>_<?php echo str_replace("TransTypePerMonth_", "", $ifeature); ?>">
                <a class="back" href="javascript:backToImpulse('<?php echo str_replace("TransTypePerMonth_", "", $ifeature); ?>', '<?php echo ($t + 1); ?>')">&larr; Back</a>
<?php
        echo "              <h4>" . str_replace("TransTypePerMonth_", "", $key) . "</h4>\n";
        include("charts/histogram-nvd3.php");
?>
              </span>
<?php
  }
  unset($orderedFeatures,$orderedIndexes);
?>
            </span>
          </span>
        </fieldset>

        <fieldset class="summary">
          <legend><strong>Summary of Features</strong></legend>
          <span class="content">
            <span class="left">
              <ul id="featurelist">
<?php
  foreach ($json->features as $feature) {
    $featureshort = str_replace("TransTypePerMonth_", "", $feature);
    echo "                <li><a href=\"javascript:showFeature('" . $featureshort . "')\" class=\"featurelink\" id=\"featurelink_" . $featureshort . "\">" . $featureshort . "</a></li>\n";
  }
?>
              </ul>
            </span>
            <span class="middle">
<?php
  foreach ($json->features as $feature) {
    $featureshort = str_replace("TransTypePerMonth_", "", $feature);
    $currfeature = $feature;
?>
              <span class="featureimpulse" id="featureimpulse_<?php echo $featureshort; ?>" style="display: none;">
                <h4><?php echo $featureshort; ?></h4>
<?php
    include("charts/feature-impulse-nvd3.php");
?>
              </span>
<?php
  }
?>
            </span>
          </span>
        </fieldset>

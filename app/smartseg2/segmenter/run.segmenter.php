<?php
$fvalues = array("0", "1", "2");
$kvalues = array("-1", "2", "3", "4", "5", "6", "7", "8", "9", "10");
$subkvalues = array("-1", "2", "3", "4", "5");
foreach ($fvalues as $f) {
  foreach ($kvalues as $k) {
    echo "F: " . $f . " K: " . $k . "... ";
    $filename = "output/clustering_mobiledata_0_" . $k . "_" . $f . ".json";
//    shell_exec("python smartseg-segmenter.py -d mobiledata -c 0 -k " . $k . " -f " . $f . " -o " . $filename);
    if (file_exists($filename)) {
      echo "OK!";
//      $json = json_decode(file_get_contents($filename));
//      if (isset($json->silhouette)) {
//        echo "OK!";
//      }
    }
    else {
      shell_exec("wget \"http://localhost/smartseg2/?clusterer=0&db=mobiledata&k=" . $k . "&features=" . $f . "\"");
    }
    echo "\n";

    $max = $k;
    if ($k == "-1") {
      $max = 2;
    }
    if ($k == "-1" && $f == "2") {
      $max = 3;
    }
    for ($clus = 0; $clus < $max; $clus++) {
      foreach ($subkvalues as $subk) {
        echo "   F: " . $f . " K: " . $subk . " N: " . $clus . "...";
        $subfilename = str_replace(".json", "_n" . $clus . "_" . $subk . ".json", $filename);
        if (file_exists($subfilename)) {
          echo "OK!";
        }
        else {
          shell_exec("wget \"http://localhost/smartseg2/?clusterer=0&db=mobiledata&k=" . $subk . "&features=" . $f . "&n=" . $clus . "&i=" . $filename . "\"");
        }
        echo "\n";

        if (preg_match("|\"n\_clusters\": ([^,]*),|U", file_get_contents($subfilename), $newmax)) {
          $newmax = $newmax[1];
          for ($subclus = 0; $subclus < $newmax; $subclus++) {
            foreach ($subkvalues as $subsubk) {
              echo "   F: " . $f . " K: " . $subsubk . " N: " . $subclus . "...";
              $subsubfilename = str_replace(".json", "_n" . $subclus . "_" . $subsubk . ".json", $subfilename);
              if (file_exists($subsubfilename)) {
                echo "OK!";
              }
              else {
                shell_exec("wget \"http://localhost/smartseg2/?clusterer=0&db=mobiledata&k=" . $subsubk . "&features=" . $f . "&n=" . $subclus . "&i=" . $subfilename . "\"");
              }
              echo "\n";
            }
          }
        }
      }
    }
  }
}
?>

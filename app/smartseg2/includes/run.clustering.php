<?php
if (isset($_POST["db"])) {
  $db = $_POST["db"];
  $c = $_POST["clusterer"];
  $k = $_POST["k"];
  $f = $_POST["features"];

//  print_r($_POST);
}
else {
  $db = $_GET["db"];
  $c = $_GET["clusterer"];
  $k = $_GET["k"];
  $f = $_GET["features"];

//  print_r($_GET);
}

if (!isset($_POST["i"]) && !isset($_GET["i"])) {
  $filename = "output/clustering_" . $db . "_" . $c . "_" . $k . "_" . $f . ".json";
  $datafilename = str_replace(".json", "-data.json", $filename);

  if (!file_exists("segmenter/" . $filename)) {
    shell_exec("cd segmenter&&python smartseg-segmenter.py -d " . $db . " -c " . $c . " -k " . $k . " -f " . $f . " -o " . $filename . "&&cd ..;");
  }
}
else {
  if (isset($_POST["i"])) {
    $i = $_POST["i"];
    $n = $_POST["n"];
  }
  else {
    $i = $_GET["i"];
    $n = $_GET["n"];
  }

  $filename = str_replace(".json", "_n" . $n . "_" . $k . ".json", $i);
  $datafilename = str_replace(".json", "-data.json", $filename);

  if (!file_exists("segmenter/" . $filename)) {
    echo shell_exec("cd segmenter&&python smartseg-segmenter.py -d " . $db . " -c " . $c . " -k " . $k . " -f " . $f . " -i " . $i . " -n " . $n . " -o " . $filename . "&&cd ..;");
  }
}
?>

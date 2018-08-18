<?php
include("includes/initialize.php");
if (isset($_FILES["file"]["tmp_name"]) && ($_FILES["file"]["tmp_name"] != "") && ($_FILES["file"]["error"] == 0)) {
  $filename = $_FILES["file"]["name"];
  $dbname = explode(".", $filename);
  $dbname = $dbname[0];

  $dbs = $mysqli->query("SHOW DATABASES");
  $exists = 0;
  while ($db = $dbs->fetch_assoc()) {
    $dbn = $db["Database"];
    if ($dbn == $dbname) {
      echo "Sorry, the database '" . $dbname . "' already exists. Use a different name.\n";
      exit(0);
    }
  }

  $mysqli->query("CREATE DATABASE " . $dbname);
  $mysqli->select_db($dbname);

  $fileh = fopen($_FILES["file"]["tmp_name"], "r");
  $header = fgetcsv($fileh);

  $fields = array("Cust_ID", "Age", "Gender", "LifeTime", "TimeSinceLastTrans", "AvgTransValue");
  $query = "CREATE TABLE AnalyticsBaseTable (Cust_ID INT(11), Age INT(11), Gender VARCHAR(16), LifeTime INT(11), TimeSinceLastTrans INT(11), AvgTransValue FLOAT, ";
  foreach ($header as $field) {
    if (strstr($field, "TransTypePerMonth_") && ($field != "TransTypePerMonth_TOTAL")) {
      $query .= $field . " FLOAT, ";
      array_push($fields, $field);
    }
  }
  $query .= "TransTypePerMonth_TOTAL FLOAT)";
  array_push($fields, "TransTypePerMonth_TOTAL");
  $mysqli->query($query);

  while ($line = fgetcsv($fileh)) {
    $query = "INSERT INTO AnalyticsBaseTable VALUES (";
    $query .= $line[0] . "," . $line[1] . ",'" . $line[2] . "'," . $line[3] . "," . $line[4] . ",";
    for ($i = 5; $i < count($line) - 1; $i++) {
      $query .= $line[$i] . ",";
    }
    $query .= $line[count($line) - 1] . ")";

    $mysqli->query($query);
  }
  fclose($fileh);

  header("Location: /smartseg2/");
}
else {
?>
<html>
  <head>
    <title>SmartSeg - File Upload</title>
    <link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
  </head>
  <body>
    <h1>SmartSeg: File Upload</h1>
    <p>You can upload your own data by using the following form:</p>
    <div id="menu">
      <form id="upload" method="post" action="fileupload.php" enctype="multipart/form-data">
        <label for="file">Upload:</label>
        <input type="file" name="file" id="file" />
        <input type="submit" name="submit" value="Upload" />
      </form>
    </div>
    <p id="gobackfromupload"><a href="/smartseg2/">Go back to SmartSeg without uploading a file</a></p>
  </body>
</html>
<?php
}
?>

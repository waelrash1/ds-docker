    <div id="menu">
      <form id="parameters" method="post" action="./">
        <input type="hidden" name="clusterer" value="0" />
        <select name="db">
          <option value="news_consumers_en_sample">Database...</option>
<?php
  $skipdbs = array("news_consumers_en_sample", "news_consumers_es_sample", "TESTDB", "classicmodels", "climote", "crm_sample", "climote_trial", "information_schema", "ceader_clusterer", "mysql", "news_consumers_en", "news_consumers_es", "performance_schema", "test");

  $databases = $mysqli->query("SHOW DATABASES");
  while ($database = $databases->fetch_assoc()) {
    $dbname = $database["Database"];
    if (!in_array($dbname, $skipdbs)) {
?>
          <option value="<?php echo $dbname; ?>"<?php echo (isset($_POST["db"]) && $_POST["db"] == $dbname)?" selected":""; ?>><?php echo $dbname; ?></option>    
<?php
    }
  }
?>
        </select>
        <select name="k">
          <option value="-1">Num. segments...</option>
          <option value="-1"<?php echo (isset($_POST["k"]) &&  $_POST["k"] == -1)?" selected":""; ?>>Auto</option>
<?php for ($i = 2; $i <= 10; $i++) { ?>
          <option value="<?php echo $i; ?>"<?php echo (isset($_POST["k"]) && $_POST["k"] == $i)?" selected":"";?>><?php echo $i; ?> segments</option>
<?php } ?>
        </select>
        <select name="features">
          <option value="0">Features...</option>
          <option value="0"<?php echo (isset($_POST["features"]) && $_POST["features"] == 0)?" selected":""; ?>>RFM</option>
          <option value="1"<?php echo (isset($_POST["features"]) && $_POST["features"] == 1)?" selected":""; ?>>Transactions</option>
          <option value="2"<?php echo (isset($_POST["features"]) && $_POST["features"] == 2)?" selected":""; ?>>All</option>
        </select>
        <input type="submit" name="submit" value="OK" />
      </form>
      <p id="gotoupload"><a href="fileupload.php">Upload file</a></p>
    </div>

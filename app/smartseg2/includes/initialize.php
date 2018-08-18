<?php
$configini = parse_ini_file("segmenter/config.ini");
$mysqli = new mysqli('dbase', 'root', 'hardeight');
$mysqli = new mysqli($configini["Server"], $configini["Username"], $configini["Password"]);
?>

<?php
date_default_timezone_set("America/New_York");

try {

	$con = new PDO("mysql:dbname=calendar;host=localhost", "USERNAME", "PASSWORD"); // Change username and password to your database username and password

	$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

}

catch(PDOException $e) {

	echo "Connection failed: " . $e->getMessage();

}

?>
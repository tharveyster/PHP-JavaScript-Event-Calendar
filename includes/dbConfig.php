<?php
date_default_timezone_set("America/New_York");

//Get Heroku ClearDB connection information
$cleardb_url = parse_url(getenv("CLEARDB_DATABASE_URL"));
$cleardb_server = $cleardb_url["host"];
$cleardb_username = $cleardb_url["user"];
$cleardb_password = $cleardb_url["pass"];
$cleardb_db = substr($cleardb_url["path"],1);
$active_group = 'default';
$query_builder = TRUE;

try {

	$con = new PDO("mysql:dbname=$cleardb_db;host=$cleardb_server", $cleardb_username, $cleardb_password); // Change username and password to your database username and password

	$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

}

catch(PDOException $e) {

	echo "Connection failed: " . $e->getMessage();

}

?>
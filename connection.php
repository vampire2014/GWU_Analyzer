<?php
//database information
include_once $_SERVER['DOCUMENT_ROOT'] . '/wp-config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/wp-includes/wp-db.php';

global $wpdb;

$dbhost = $wpdb->dbhost;
$dbuser = $wpdb->dbuser;
$dbpass = $wpdb->dbpassword;
$dbname = $wpdb->dbname;

//connect to database
mysql_connect($dbhost, $dbuser, $dbpass) or die("Could not connect database".mysql_error());
mysql_select_db($dbname) or die("Could not select database".mysql_error());
?>
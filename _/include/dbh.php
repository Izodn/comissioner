<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/environment.php';
	global $env;
	if( empty($env['DEVELOPMENT']) || $env['DEVELOPMENT'] !== "1" ) {
		error_reporting(0);
	}
	$dsn = 'mysql:dbname='.$env['DATABASE'].';host='.$env['DATABASE_LOCATION'].'';
	$user = $env['USER'];
	$password = $env['PASS'];
	$dbh = new PDO($dsn, $user, $password);
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	global $dbh;
?>
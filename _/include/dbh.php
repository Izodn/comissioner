<?php
	global $env;
	$dsn = 'mysql:dbname='.$env['DATABASE'].';host='.$env['DATABASE_LOCATION'];
	$user = $env['USER'];
	$password = $env['PASS'];
	$dbh = new PDO($dsn, $user, $password);
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	global $dbh;
?>
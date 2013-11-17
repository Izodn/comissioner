<?php
	if( empty($_SESSION['ENV']['ERROR_REPORTING']) || $_SESSION['ENV']['ERROR_REPORTING'] !== "1" )
		error_reporting(0);
	$dsn = 'mysql:dbname='.$_SESSION['ENV']['DATABASE'].';host='.$_SESSION['ENV']['DATABASE_LOCATION'].'';
	$user = $_SESSION['ENV']['USER'];
	$password = $_SESSION['ENV']['PASS'];
	$authUser= md5($_SESSION['ENV']['SUPERUSER']);
	$dbh = new PDO($dsn, $user, $password);
	global $dbh;
?>
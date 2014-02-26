<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	global $dbh;
	$query = "SELECT iUserId FROM COM_USER LIMIT 0,1";
	$runQuery = $dbh->prepare($query);
	$runQuery->execute();
	$result = $runQuery->fetch(PDO::FETCH_ASSOC);
	if( $result === false ) { //No users found, need superuser setup
		if( $_SERVER['PHP_SELF'] !== '/_/setup.php') { //If not on setup page, redirect
			header('Location: /_/setup.php');
			die(); //Prevent further script execution
		}
	}
?>
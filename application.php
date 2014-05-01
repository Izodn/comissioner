<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/environment.php';
	global $env;
	if( empty($env['DEVELOPMENT']) || $env['DEVELOPMENT'] !== "1" ) {
		error_reporting(0);
		ini_set('display_errors', '0');
	}
	else {
		ini_set('display_errors', '1');
		error_reporting(-1);
	}
	$neededEnv = array('DATABASE_LOCATION','DATABASE','USER','PASS','IMAGE_LIB','UPLOAD_SIZE_LIMIT');
	$neededEnvLen = count($neededEnv);
	$needVarsErrMsg = 'The envVars file is missing the following needed variables: ';
	$missingCount = 0;
	$needVars = false;
	for($a=0;$a<$neededEnvLen;$a++) {
		if( !isset($env[$neededEnv[$a]]) || $env[$neededEnv[$a]]==='' ) {
			if( $missingCount > 0 )
				$needVarsErrMsg .= ', ';
			$needVarsErrMsg .= $neededEnv[$a];
			$needVars=true;
			$missingCount++;
		}
	}
	if( $needVars === true ) { //Show error, and die
		echo $needVarsErrMsg.'.';
		die(); //Prevent script exec
	}
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
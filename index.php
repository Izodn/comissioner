<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/user.php';
	session_start();
	if( !isset($_SESSION['userObj']) ) //If not logged in
		header('Location: login.php');
?>
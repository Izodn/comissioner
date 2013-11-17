<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
	if( globalOut('redirectHandler') )
	{
		$url = globalOut('redirectHandler');
		globalClear('redirectHandler');
		header('Location: '.$url.'');
	}
	else
	{
		die('There was an error with the redirect process!');
	}
?>
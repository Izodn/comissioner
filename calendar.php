<?php
	$getParam = 'url';
	if(empty($_GET[$getParam]))
		die('No url given');
	$url = str_replace($_SERVER['PHP_SELF'].'?'.$getParam.'=', '', $_SERVER['REQUEST_URI']);
	$file = file_get_contents($url);
	header('Content-Type: text/calendar; charset=utf-8');
	header('Pragma:');
	header('Cache-Control: no-cache, must-revalidate');
	header('Content-disposition: attachment; filename=calendar.ics');
	echo $file;
?>
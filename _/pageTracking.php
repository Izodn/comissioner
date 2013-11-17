<?php
	$_PAGE = array();
	if(!isset($_SERVER['HTTP_REFERER']))
	{
		$_PAGE['Last'] = '';
	}
	else
	{
		$_PAGE['Last'] = $_SERVER['HTTP_REFERER'];
	}
	$pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
	if ($_SERVER["SERVER_PORT"] != "80")
	{
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} 
	else 
	{
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	$_PAGE['Current'] = $pageURL;
	global $_PAGE;
?>
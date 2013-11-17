<?php
	/*	THIS FILE IS MEANT TO ENABLE
	*	VARIALBE OVERIDES.
	*	USE LIKE SO:
	*	$_SERVER['DOCUMENT_ROOT'] = '/var/www2/';
	*/
	if($_SERVER['HTTP_HOST'] == 'vampirika.com')
	{
		error_reporting(0);
	}
?>
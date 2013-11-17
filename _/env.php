<?php
	if(!isset($_SESSION)) {
		session_start();
	}
	$file = file($_SERVER['DOCUMENT_ROOT'].'/_/environment.txt');
	if(!$file)
	{
		die('Could not start environment');
	}
	foreach($file as $a)
	{
		if( $a[0] !== "#" ) //If not comment
		{
			$a = trim($a);
			$strArr = explode(' => ', $a);
			if(count($strArr) == 2)
			{
				$_SESSION['ENV'][$strArr[0]] = $strArr[1];
			}
		}
	}
?>
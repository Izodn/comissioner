<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/environmentVars.php';
	if(!isset($_SESSION)) {
		session_start();
	}
	$envVars = getEnvVars();
	$envVarArr = explode("\n", $envVars);
	foreach($envVarArr as $a)
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
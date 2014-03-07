<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/envVars.php';
	$envString = getEnvString();
	$envArr = explode("\n", $envString);
	$envArrLen = count($envArr);
	for($i=0;$i<$envArrLen;$i++) {
		$tmp = explode(" = ", $envArr[$i]);
		if(count($tmp) === 2)
			$env[trim($tmp[0])] = trim($tmp[1]);
	}
	global $env;
?>
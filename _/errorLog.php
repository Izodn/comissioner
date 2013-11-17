<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
	function writeLog($shortVal, $longVal = null, $sev = '')
	{
		if($longVal == null)
		{
			$longVal = dump($_SESSION);
		}
		if((getType($shortVal) == 'array' || getType($shortVal) == 'object') || (getType($longVal) == 'array' || getType($longVal) == 'object'))
		{
			echo "Can't send array into function \"writeLog()\".\n<br>\n";
			return false;
		}
		global $dbh;
		$null = null;
		$longVal = dump(debug_backtrace())."\n<br>\n<br>\n".dump($_SERVER)."\n<br>\n<br>\n".$longVal;
		$query = "
			INSERT INTO
				errorLogs
			(CSHORTVALUE, CLONGVALUE, IUSERID, ICLIENTID)
			VALUES
			(?, ?, ?, ?)
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $shortVal);
		$runQuery->bindParam(2, $longVal);
		if(globalOut('user_key3'))
		{
			$runQuery->bindParam(3, globalOut('user_key3'));
			$runQuery->bindParam(4, $null);
		}
		elseif(globalOut('user_key2'))
		{
			
			$runQuery->bindParam(3, $null);
			$runQuery->bindParam(4, globalOut('user_key2'));
		}
		else
		{
			$runQuery->bindParam(3, $null);
			$runQuery->bindParam(4, $null);
		}
		$runQuery->execute();
		if($sev == 'HARD')
		{
			globalDestroy();
			header('Location: /error_page.php');
			exit();
		}
	}
?>
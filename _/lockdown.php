<?php
	function lockdownCheck()
	{
		global $dbh;
		//return false; //USE TO AVOID LOCKDOWN CHECK
		$query = "
			SELECT
				CALLOWEDUSER
			FROM
				lockdown
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->execute();
		$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
		if($runQueryArray != '' || $runQueryArray != array())
		{
			return $runQueryArray[0];
		}
		else
		{
			return false;
		}
	}
?>
<?php
	function safe($originalText) //USED TO FILTER OUT BAD CHARACTERS INTO ACCEPTABLE CHARACTERS
	{	
		global $dbh;
		$safeText = $originalText;
		$safeText = htmlentities($safeText);
		/* DISABLED 03/18/2013 1:37pm BY Brandon B
		if($safeText != $originalText)
		{
			if(globalOut('user_key2'))
			{
				if(globalOut('user_key3'))
				{
					$query = "
						INSERT INTO
							hack_attempt
						(IIPADDRESS, IUSERID, IHACKLOCATION, ICREATEDTIME, CHACKTEXT)
						VALUES
						(?, ?, ?, NOW(), ?)
					";
					$runQuery = $dbh->prepare($query);
					$runQuery->bindParam(1, $_SERVER["REMOTE_ADDR"], PDO::PARAM_STR, 225);
					$runQuery->bindParam(2, globalOut('user_key3'), PDO::PARAM_STR, 225);
					$runQuery->bindParam(3, $_SERVER['PHP_SELF'], PDO::PARAM_STR, 225);
					$runQuery->bindParam(4, $originalText, PDO::PARAM_STR, 225);
				}
				else
				{
					$query = "
						INSERT INTO
							hack_attempt
						(IIPADDRESS, ICLIENTID, IHACKLOCATION, ICREATEDTIME, CHACKTEXT)
						VALUES
						(?, ?, ?, NOW(), ?)
					";	
					$runQuery = $dbh->prepare($query);
					$runQuery->bindParam(1, $_SERVER["REMOTE_ADDR"], PDO::PARAM_STR, 225);
					$runQuery->bindParam(2, globalOut('user_key2'), PDO::PARAM_STR, 225);
					$runQuery->bindParam(3, $_SERVER['PHP_SELF'], PDO::PARAM_STR, 225);
					$runQuery->bindParam(4, $originalText, PDO::PARAM_STR, 225);;
				}
			}
			else
			{
				$query = "
					INSERT INTO
						hack_attempt
					(IIPADDRESS, IHACKLOCATION, ICREATEDTIME, CHACKTEXT)
					VALUES
					(?, ?, NOW(), ?)
				";	
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $_SERVER["REMOTE_ADDR"], PDO::PARAM_STR, 225);
				$runQuery->bindParam(2, $_SERVER['PHP_SELF'], PDO::PARAM_STR, 225);
				$runQuery->bindParam(3, $originalText, PDO::PARAM_STR, 225);
			}
			$runQuery->execute();
		}
		*/
		return $safeText;	
	}
?>
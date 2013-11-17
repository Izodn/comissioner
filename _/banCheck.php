<?php
	function banCheck()
	{
		//return true; //DEV USE ONLY (USED FOR STOPPING BANCHECK)
		if(globalOut('user_key2'))
		{
			global $dbh;
			if(globalOut('user_key3'))
			{
				$query = "
					SELECT
						ib.IIPADDRESS,
						ib.IISBANNED
					FROM
						ip_ban ib
					INNER JOIN
						user_detail ud ON (ud.IUSERID = ib.IUSERID)
					WHERE
						ib.IIPADDRESS = ? AND
						ib.IUSERID = ?
				";
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR, 225);
				$runQuery->bindParam(2, globalOut('user_key3'), PDO::PARAM_STR, 225);
				$runQuery->execute();
				$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
				if($runQueryArray[0] == '')
				{
					$query = "
						INSERT INTO
							ip_ban
						(IIPADDRESS, ICREATEDTIME, IISBANNED, IUSERID)
						VALUES
						(?, NOW(), '0', ?)
					";
					$runQuery = $dbh->prepare($query);
					$runQuery->bindParam(1, $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR, 225);
					$runQuery->bindParam(2, globalOut('user_key3'), PDO::PARAM_STR, 225);
					if(!$runQuery->execute())
					{
						die('Error: Software broke at Ban Check!');
					}
				}
				if($runQueryArray['IISBANNED'] != '1')
				{
					return true;
				}
				else
				{
					writeLog('A banned user connected but was stopped', null);
					die('You\'ve been banned. <br>If you think this is in error contact <a href="mailto:admin@vampirika.com">admin@vampirika.com</a>');
				}
			}
			else
			{
				$query = "
					SELECT
						ib.IIPADDRESS,
						ib.IISBANNED
					FROM
						ip_ban ib
					INNER JOIN
						client_detail cd ON (cd.ICLIENTID = ib.ICLIENTID)
					WHERE
						ib.IIPADDRESS = ? AND
						ib.ICLIENTID = ?
				";
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR, 225);
				$runQuery->bindParam(2, globalOut('user_key2'), PDO::PARAM_STR, 225);
				$runQuery->execute();
				$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
				if($runQueryArray[0] == '')
				{
					$query = "
						INSERT INTO
							ip_ban
						(IIPADDRESS, ICREATEDTIME, IISBANNED, ICLIENTID)
						VALUES
						(?, NOW(), '0', ?)
					";
					$runQuery = $dbh->prepare($query);
					$runQuery->bindParam(1, $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR, 225);
					$runQuery->bindParam(2, globalOut('user_key2'), PDO::PARAM_STR, 225);
					if(!$runQuery->execute())
					{
						die('Error: Software broke at Ban Check!');
					}
					
				}
				if($runQueryArray['IISBANNED'] != '1')
				{
					return true;
				}
				else
				{
					writeLog('A banned user connected but was stopped', null);
					die('You\'ve been banned. <br>If you think this is in error contact <a href="mailto:admin@vampirika.com">admin@vampirika.com</a>');
				}
			}
		}
		else
		{
			global $dbh;
			$query = "
				SELECT
					IIPADDRESS,
					IISBANNED
				FROM
					ip_ban
				WHERE
					IIPADDRESS = ?
			";
			$stmt = $dbh->prepare($query);
			$stmt->bindParam(1, $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR, 225);
			$stmt->execute();
			$runQueryArray = $stmt->fetch(PDO::FETCH_BOTH);
			if($runQueryArray[1] == '1')
			{
				writeLog('A banned user connected but was stopped', null);
				die('You\'ve been banned. <br>If you think this is in error contact <a href="mailto:admin@vampirika.com">admin@vampirika.com</a>');
			}
			elseif($runQueryArray[0] == '')
			{
				$query2 = "
					INSERT INTO
						ip_ban
					(IIPADDRESS, ICREATEDTIME, IISBANNED)
					VALUES
					(?, NOW(), '0')
				";
				$stmt2 = $dbh->prepare($query2);
				$stmt2->bindParam(1, $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR, 225);
				$stmt2->execute();
			}
		}
	}
?>
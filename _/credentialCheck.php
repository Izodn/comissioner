<?php
	function credentialCheck($type = '') // "if(!credentialCheck($var))" shouldn't be used
	{
		global $dbh;
		if(strtolower($type) === strtolower('user'))
		{
			$query = "
				SELECT
					CUSERNAME,
					CPASSWORD,
					CPASSWORD2,
					IISACTIVE
				FROM
					user_account
				WHERE
					IUSERID = ? AND
					CUSERNAME = ? AND
					CPASSWORD = ? AND
					CPASSWORD2 = ? AND
					IISACTIVE = 1
			";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, globalOut('user_key3'));
			$runQuery->bindParam(2, globalOut('users'));
			$runQuery->bindParam(3, globalOut('user_key1'));
			$runQuery->bindParam(4, globalOut('user_key2'));
			$runQuery->execute();
			$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
			if($runQueryArray == array() || $runQueryArray == '' || !$runQueryArray)	//THE DATABASE DOESN'T HAVE ANYTHING WITH ALL PARAMATERS MET
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		elseif(strtolower($type) === strtolower('client'))
		{
			$query = "
				SELECT
					CUSERNAME,
					CPASSWORD,
					IISACTIVE
				FROM
					client_account
				WHERE
					ICLIENTID = ? AND
					CUSERNAME = ? AND
					CPASSWORD = ? AND
					IISACTIVE = 1
			";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, globalOut('user_key2'));
			$runQuery->bindParam(2, globalOut('users'));
			$runQuery->bindParam(3, globalOut('user_key1'));
			$runQuery->execute();
			$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
			if($runQueryArray == array() || $runQueryArray == '' || !$runQueryArray)	//THE DATABASE DOESN'T HAVE ANYTHING WITH ALL PARAMATERS MET
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		else
		{
			return false;
		}
	}
?>
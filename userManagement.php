<?php 
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';	
	global $dbh;
	if(!globalOut('users') || globalOut('users') !== $authUser)
	{
		if(globalOut('user_key4') && globalOut('user_key4') == md5($_SESSION['ENV']['SUPERUSER']))
		{
		}
		else
		{
			die('You are not suppose to be here.');
		}
	}
	if(isset($_POST['lockdown']))
	{
		$query = "
			INSERT INTO
				lockdown
			(CALLOWEDUSER)
			VALUES
			((SELECT CUSERNAMEDETAIL FROM user_detail WHERE IUSERID = ?))
			
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
		$runQuery->execute();
	}
	if(isset($_POST['unlock']))
	{
		$query = "
			DELETE FROM
				lockdown
			WHERE
				CALLOWEDUSER = (
					SELECT
						CUSERNAMEDETAIL
					FROM
						user_detail
					WHERE
						IUSERID = ?
				)
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
		$runQuery->execute();
	}
	if(isset($_POST['clientEnable']))
	{
		$query1 = "
			SELECT
				ca.ICLIENTID,
				ca.IISACTIVE
			FROM
				client_account ca
			INNER JOIN 
				client_detail cd ON (cd.ICLIENTID = ca.ICLIENTID)
			WHERE
				cd.CUSERNAMEDETAIL = ?
		";
		$runQuery1 = $dbh->prepare($query1);
		$runQuery1->bindParam(1, $_POST['client'], PDO::PARAM_STR, 225);
		$runQuery1->execute();
		$runQuery1Array = $runQuery1->fetch(PDO::FETCH_BOTH);
		$query = "
		UPDATE
			client_account 
		SET
			IISACTIVE = '1'
		WHERE
			ICLIENTID IN(?)
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $runQuery1Array[0], PDO::PARAM_STR, 225);
	}
	elseif(isset($_POST['clientDisable']))
	{
		$query1 = "
			SELECT
				ca.ICLIENTID,
				ca.IISACTIVE
			FROM
				client_account ca
			INNER JOIN 
				client_detail cd ON (cd.ICLIENTID = ca.ICLIENTID)
			WHERE
				cd.CUSERNAMEDETAIL = ?
		";
		$runQuery1 = $dbh->prepare($query1);
		$runQuery1->bindParam(1, $_POST['client'], PDO::PARAM_STR, 225);
		$runQuery1->execute();
		$runQuery1Array = $runQuery1->fetch(PDO::FETCH_BOTH);
		$query = "
		UPDATE
			client_account 
		SET
			IISACTIVE = '0'
		WHERE
			ICLIENTID IN(?)
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $runQuery1Array[0], PDO::PARAM_STR, 225);
	}
	elseif(isset($_POST['clientEnableAll']))
	{
		$query = "
		UPDATE
			client_account
		SET
			IISACTIVE = '1'
		";
		$runQuery = $dbh->prepare($query);
	}
	elseif(isset($_POST['clientDisableAll']))
	{
		$query = "
		UPDATE
			client_account
		SET
			IISACTIVE = '0'
		";
		$runQuery = $dbh->prepare($query);
	}
	if(isset($_POST['clientEnable']) || isset($_POST['clientDisable']) || isset($_POST['clientEnableAll']) || isset($_POST['clientDisableAll']))
	{
		$runQuery->execute();
	}
	if(isset($_POST['userEnable']))
	{
		$query1 = "
			SELECT
				ua.IUSERID,
				ua.IISACTIVE
			FROM
				user_account ua
			INNER JOIN 
				user_detail ud ON (ud.IUSERID = ua.IUSERID)
			WHERE
				ud.CUSERNAMEDETAIL = ?
		";
		$runQuery1 = $dbh->prepare($query1);
		$runQuery1->bindParam(1, $_POST['user'], PDO::PARAM_STR, 225);
		$runQuery1->execute();
		$runQuery1Array = $runQuery1->fetch(PDO::FETCH_BOTH);
		$query = "
		UPDATE
			user_account 
		SET
			IISACTIVE = '1'
		WHERE
			IUSERID != ? AND
			IUSERID IN(?)
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
		$runQuery->bindParam(2, $runQuery1Array[0], PDO::PARAM_STR, 225);
	}
	elseif(isset($_POST['userDisable']))
	{
		$query1 = "
			SELECT
				ua.IUSERID,
				ua.IISACTIVE
			FROM
				user_account ua
			INNER JOIN 
				user_detail ud ON (ud.IUSERID = ua.IUSERID)
			WHERE
				ud.CUSERNAMEDETAIL = ?
		";
		$runQuery1 = $dbh->prepare($query1);
		$runQuery1->bindParam(1, $_POST['user'], PDO::PARAM_STR, 225);
		$runQuery1->execute();
		$runQuery1Array = $runQuery1->fetch(PDO::FETCH_BOTH);
		$query = "
		UPDATE
			user_account 
		SET
			IISACTIVE = '0'
		WHERE
			IUSERID != ? AND
			IUSERID IN(?)
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
		$runQuery->bindParam(2, $runQuery1Array[0], PDO::PARAM_STR, 225);
	}
	elseif(isset($_POST['userEnableAll']))
	{
		$query = "
		UPDATE
			user_account
		SET
			IISACTIVE = '1'
		WHERE
			IUSERID != ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);

	}
	elseif(isset($_POST['userDisableAll']))
	{
		$query = "
		UPDATE
			user_account
		SET
			IISACTIVE = '0'
		WHERE
			IUSERID != ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);

	}
	if(isset($_POST['userEnable']) || isset($_POST['userDisable']) || isset($_POST['userEnableAll']) || isset($_POST['userDisableAll']))
	{
		$runQuery->execute();
		
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>BookKeeping</title>
	</head>
	<body>
		<center>
			<?php
				if(globalOut('user_key4') && globalOut('user_key5') && globalOut('user_key6') && globalOut('user_key7') && globalOut('user_key3'))
				{
					echoLinks();
				}
				elseif(globalOut('user_key4') && globalOut('user_key5') && globalOut('user_key6') && globalOut('user_key2'))
				{
					echoClientLinks();
				}
				else
				{
					echoLinks();
				}
				echoAdminLinks();
			?>
			<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
				<br>
				<p><font color="red">red</font> = disabled</p>
				User's Username:<!--<input type="text" name="user" maxlength="64"> !-->
				<?php
					$query = "
						SELECT
							ud.CUSERNAMEDETAIL,
							ua.IISACTIVE
						FROM
							user_detail ud
						INNER JOIN
							user_account ua ON (ua.IUSERID = ud.IUSERID)
						WHERE
							ud.IUSERID != ?
						ORDER BY
							Lower(ud.CUSERNAMEDETAIL)";
					$result = $dbh->prepare($query);
					$result->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
					$result->execute();
					$count=0;
					echo '<select name="user">';
					echo '<option value="" selected></option>';
					while( ($row = $result->fetch(PDO::FETCH_BOTH)))
					{
						$var = 'returnNumber'.$count;
						$isActive = isset($row[1]) && $row[1] == 0 ? 'style="color:#FF0000"' : '' ;
						echo '<option '.$isActive.' value="'.safe($row[0]).'">'.safe($row[0]).'</option>';
						$count++;
					}
					echo '</select>';
				?>
				<input type="submit" name="userEnable" value="Enable"><input type="submit" name="userDisable" value="Disable"><br>
				<input type="submit" name="userEnableAll" value="Enable All"><input type="submit" name="userDisableAll" value="Disable All"><br>
				<?php
					if(isset($_POST['userEnable']) || isset($_POST['userDisable']))
					{
						$query2 = "
							SELECT
								ua.IISACTIVE
							FROM
								user_account ua
							INNER JOIN
								user_detail ud ON (ud.IUSERID = ua.IUSERID)
							WHERE
								ud.CUSERNAMEDETAIL = ?
						";
						$runQuery2 = $dbh->prepare($query2);
						$runQuery2->bindParam(1, $_POST['user'], PDO::PARAM_STR, 225);
						$runQuery2->execute();
						$runQuery2Array = $runQuery2->fetch(PDO::FETCH_BOTH);
						if(safe($runQuery1Array[1]) == safe($runQuery2Array[0]) && ((safe($runQuery2Array[0]) !== '1' && isset($_POST['userEnable'])) || (safe($runQuery2Array[0]) !== '0' &&isset($_POST['userDisable']))))
						{
							die('There was an error with this process');
						}
						else
						{
							echo '<font color="red">Success</font>';
						}
					}
				?>
			</form>
			<br>
			<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
				<br>
				Client's Username:<!--<input type="text" name="client" maxlength="64">!-->
				<?php
					$query = "
						SELECT
							cd.CUSERNAMEDETAIL,
							ca.IISACTIVE
						FROM
							client_detail cd
						INNER JOIN
							client_account ca ON (ca.ICLIENTID = cd.ICLIENTID)
						ORDER BY
							Lower(cd.CUSERNAMEDETAIL)";
					$result = $dbh->prepare($query);
					$result->execute();
					$count=0;
					echo '<select name="client">';
					echo '<option value="" selected></option>';
					while( ($row = $result->fetch(PDO::FETCH_BOTH)))
					{
						$var = 'returnNumber'.$count;
						$isActive = isset($row[1]) && $row[1] == 0 ? 'style="color:#FF0000"' : '' ;
						echo '<option '.$isActive.' value="'.safe($row[0]).'">'.safe($row[0]).'</option>';
						$count++;
					}
					echo '</select>';
				?>
				<input type="submit" name="clientEnable" value="Enable"><input type="submit" name="clientDisable" value="Disable"><br>
				<input type="submit" name="clientEnableAll" value="Enable All"><input type="submit" name="clientDisableAll" value="Disable All"><br>
				<?php
					if(isset($_POST['clientEnable']) || isset($_POST['clientDisable']))
					{
						$query2 = "
							SELECT
								ca.IISACTIVE
							FROM
								client_account ca
							INNER JOIN
								client_detail cd ON (cd.ICLIENTID = ca.ICLIENTID)
							WHERE
								cd.CUSERNAMEDETAIL = ?
						";
						$runQuery2 = $dbh->prepare($query2);
						$runQuery2->bindParam(1, $_POST['client'], PDO::PARAM_STR, 225);
						$runQuery2->execute();
						$runQuery2Array = $runQuery2->fetch(PDO::FETCH_BOTH);
						if(safe($runQuery1Array[1]) == safe($runQuery2Array[0]) && ((safe($runQuery2Array[0]) !== '1' && isset($_POST['clientEnable'])) || (safe($runQuery2Array[0]) !== '0' && isset($_POST['clientDisable']))))
						{
							die('There was an error with this process');
						}
						else
						{
							echo '<font color="red">Success</font>';
						}
					}
				?>
			</form>
			<?php
				if(!lockdownCheck())
				{
					?>
						<form name="" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
							<input type="submit" name="lockdown" value="Lockdown Server">
						</form>
					<?php
				}
				else
				{
					?>
						<form name="" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
							<input type="submit" name="unlock" value="Unlock Server">
						</form>
					<?php
				}
			?>
			
		</center>
	</body>
</html>
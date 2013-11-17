<?php 
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
	if(isset($_POST['submit']) && isset($_POST['username']) && isset($_POST['password']))
	{
		global $dbh;
		$username = md5($_POST['username']);
		$password = md5($_POST['password']);
		$query = "
		SELECT 
			CUSERNAME, 
			CPASSWORD, 
			ICLIENTID
		FROM 
			client_account 
		WHERE 
			CUSERNAME = ? AND
			IISACTIVE = '1'";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $username, PDO::PARAM_STR, 225);
		$runQuery->execute();
		$runQueryResult = $runQuery->fetch(PDO::FETCH_BOTH);
		if(safe($runQueryResult['CUSERNAME']) == $username && safe($runQueryResult['CPASSWORD']) == $password)
		{
			$clientid = safe($runQueryResult['ICLIENTID']);
			$username = stripslashes($username);
			globalIn('users', $username);
			globalIn('user_key1', $password);
			globalIn('user_key2', $clientid);
			$query = "
				UPDATE
					client_detail
				SET
					CLASTLOGIN = NOW()
				WHERE
					ICLIENTID = ?
			";
			$addLastLogin = $dbh->prepare($query);
			$addLastLogin->bindParam(1, $clientid, PDO::PARAM_STR, 225);
			$addLastLogin->execute();
			if(globalOut('gallery'))
			{
				header('Location:'.globalOut('gallery'));
			}
			else
			{
				header("Location: index.php");
			}
			
		}
		elseif(safe($runQueryResult['CUSERNAME']) == $username && safe($runQueryResult['CPASSWORD']) != $password )
		{
			die('You\'ve entered the wrong password');
		}
		elseif(safe($runQueryResult['CUSERNAME']) != $username)
		{
			die('That user does not exist.');
		}
		
	}
	elseif(isset($_POST['register']))
	{
		header('Location: clientRegister.php');
	}
	elseif(globalOut('users'))
	{
		globalDestroy();
		header("Location: index.php");
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
			<form action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>" method="post">
				<table>
					<br>
					<tr>
						<td>Email:</td><td><input type="text" name="username" maxlength="64"></td>
					</tr>
					<tr>
						<td>Password:</td><td><input type="password" name="password" maxlength="64"></td>
					</tr>
				</table>
				<input type="submit" name="submit" value="Submit">
				<input type="submit" name="register" value="Register">
			</form>
		</center>
	</body>
</html>
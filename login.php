<?php 
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';	
	if(isset($_POST['username']) && isset($_POST['password1']) && isset($_POST['password2']))
	{
		global $dbh;
		$username = md5($_POST['username']);
		$password1 = md5($_POST['password1']);
		$password2 = md5($_POST['password2']);
		$query = "
		SELECT 
			CUSERNAME, 
			CPASSWORD, 
			CPASSWORD2,
			IUSERID
		FROM 
			user_account 
		WHERE 
			CUSERNAME = ? AND
			IISACTIVE = '1'";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $username, PDO::PARAM_STR, 225);
		$runQuery->execute();
		$runQueryResult = $runQuery->fetch(PDO::FETCH_BOTH);
		if(safe($runQueryResult[0]) == $username && safe($runQueryResult[1]) == $password1 && safe($runQueryResult[2]) == $password2)
		{
			$userid = $runQueryResult[3];
			$username = stripslashes($username);
			globalIn('users', $username);
			globalIn('user_key1', $password1);
			globalIn('user_key2', $password2);
			globalIn('user_key3', $userid);
			$query = "
				UPDATE
					user_detail
				SET
					CLASTLOGIN = NOW()
				WHERE
					IUSERID = ?
			";
			$addLastLogin = $dbh->prepare($query);
			$addLastLogin->bindParam(1, $userid, PDO::PARAM_STR, 225);
			$addLastLogin->execute();
			if(globalOut('gallery'))
			{
				header('Location:'.globalOut('gallery'));
			}
			else
			{
				header("Location: adminIndex.php");
			}
		}
		elseif(safe($runQueryResult[0]) == $username && (safe($runQueryResult[1]) != $password1 || safe($runQueryResult[2]) != $password2))
		{
			die('You\'ve entered the wrong password');
		}
		elseif(safe($runQueryResult[0]) != $username)
		{
			die('That user does not exist.');
		}
	}
	elseif(globalOut('users'))
	{
		globalDestroy();
		header("Location: adminIndex.php");
	}
	else 
	{
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
						<td>Username:</td><td><input type="text" name="username" maxlength="64"></td>
					</tr>
					<tr>
						<td>Password 1:</td><td><input type="password" name="password1" maxlength="64"></td>
					</tr>
					<tr>
						<td>Password 2:</td><td><input type="password" name="password2" maxlength="64"></td>
					</tr>
				</table>
				<input type="submit" name="submit" value="Submit">
			</form>
			<br><a href='clientLogin.php'>Client Login</a>
		</center>
	</body>
</html>
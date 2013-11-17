<?php 
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';	
	if(!globalOut('users') || globalOut('users') !== $authUser)
	{
		if(globalOut('user_key4') && safe(globalOut('user_key4')) == md5('Izodn'))
		{
		}
		else
		{
			die('You are not suppose to be here.');
		}
	}
	if(isset($_POST['submit']) && isset($_POST['username']) && isset($_POST['password1']) && isset($_POST['password2']))
	{
		global $dbh;
		if ($_POST['password1'] == $_POST['password1r'] && $_POST['password2'] == $_POST['password2r'])
		{
			if($_POST['username'] === '' || $_POST['password1'] === '' || $_POST['password2'] === '')
			{
				die('You have a missing field.');
			}
			else
			{
				$username = $_POST['username'];
				$password1 = $_POST['password1'];
				$password2 = $_POST['password2'];
				$insert = "INSERT INTO user_account (CUSERNAME, CPASSWORD, CPASSWORD2, IISACTIVE)
				VALUES (?, ?, ?, '1')";
				$add_user = $dbh->prepare($insert);
				$add_user->bindParam(1, md5($_POST['username']), PDO::PARAM_STR, 225);
				$add_user->bindParam(2, md5($_POST['password1']), PDO::PARAM_STR, 225);
				$add_user->bindParam(3, md5($_POST['password2']), PDO::PARAM_STR, 225);
				$add_user->execute();
				$insert = "INSERT INTO user_detail (IUSERID, CUSERNAMEDETAIL, IRECORDCOUNT, IUSERIDMD5)
				VALUES ((SELECT IUSERID FROM user_account WHERE CUSERNAME = ?), ?, '0', ?)";
				$add_user = $dbh->prepare($insert);
				$add_user->bindParam(1, md5($_POST['username']), PDO::PARAM_STR, 225);
				$add_user->bindParam(2, $_POST['username'], PDO::PARAM_STR, 225);
				$add_user->bindParam(3, md5($_POST['username']), PDO::PARAM_STR, 255);
				$add_user->execute();
				echo '<center><font color="red">User creation was successful.</font></center>';
			}
		}
		else
		{
			die('You have a missing field.');
		}
	}
	elseif(isset($_POST['submit']))
	{
		if(!isset($_POST['username']) || !isset($_POST['password1']) || !isset($_POST['password1r']) || !isset($_POST['password2']) || !isset($_POST['password2r']))
		{
			die('You have a missing field.');
		}
	}
	echo '<center>';
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
	echo '</center>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>BookKeeping</title>
	</head>
	<body>
		<center>
			<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
				<br>
				<table>
					<tr>
						<td>Username:</td><td><input type="text" name="username" maxlength="64"></td>
					</tr>
					<tr>
						<td>Password 1:</td><td><input type="password" name="password1" maxlength="64"></td>
					</tr>
					<tr>
						<td>Repeat Password 1:</td><td><input type="password" name="password1r" maxlength="64"></td>
					</tr>
					<tr>
						<td>Password 2:</td><td><input type="password" name="password2" maxlength="64"></td>
					</tr>
					<tr>
						<td>Repeat Password 2 :</td><td><input type="password" name="password2r" maxlength="64"></td>
					</tr>
				</table>
				<input type="submit" name="submit" value="Submit">
			</form>
		</center>
	</body>
</html>
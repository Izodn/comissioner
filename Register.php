<?php 
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/forceFunctions.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/functionList.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/credentialCheck.php';
	$registerAllow = false;
	//Start check to see if the database needs setup for the first time
	global $dbh;
	$runQuery = $dbh->prepare("SELECT count(*) rowCount FROM user_account");
	$runQuery->execute();
	$result = $runQuery->fetch(PDO::FETCH_BOTH);
	if($result['rowCount'] === "0") {
		$needSetup = true;
	}
	//End database check
	if( !isset($needsSetup) || $needsSetup == false ) {
		if(credentialCheck('user')) {
			if(globalOut('users') === md5($_SESSION['ENV']['SUPERUSER'])) {
				$registerAllow = true;
			}
		}
	}
	if( $registerAllow === true || (isset($needSetup) && $needSetup === true) ) {
		if( !empty($_POST['username']) && !empty($_POST['pass1']) && !empty($_POST['rPass1']) && !empty($_POST['pass2']) && !empty($_POST['rPass2']) ) {
			$username = $_POST['username'];
			$pass1 = $_POST['pass1'];
			$rPass1 = $_POST['rPass1'];
			$pass2 = $_POST['pass2'];
			$rPass2 = $_POST['rPass2'];
			//Check to see if passwords match
			if( $pass1 !== $rPass1 || $pass2 !== $rPass2 ) {
				$errMsg = "Pass1 didn't match repeat, or pass2 didn't match repeat";

			}
			else {
				$insert = "INSERT INTO user_account (CUSERNAME, CPASSWORD, CPASSWORD2, IISACTIVE)
				VALUES (?, ?, ?, '1')";
				$add_user = $dbh->prepare($insert);
				$add_user->bindParam(1, md5($username), PDO::PARAM_STR, 225);
				$add_user->bindParam(2, md5($pass1), PDO::PARAM_STR, 225);
				$add_user->bindParam(3, md5($pass2), PDO::PARAM_STR, 225);
				$add_user->execute();
				$insert = "INSERT INTO user_detail (IUSERID, CUSERNAMEDETAIL, IRECORDCOUNT, IUSERIDMD5)
				VALUES ((SELECT IUSERID FROM user_account WHERE CUSERNAME = ?), ?, '0', ?)";
				$add_user = $dbh->prepare($insert);
				$add_user->bindParam(1, md5($username), PDO::PARAM_STR, 225);
				$add_user->bindParam(2, $username, PDO::PARAM_STR, 225);
				$add_user->bindParam(3, md5($username), PDO::PARAM_STR, 255);
				$add_user->execute();
				if( (isset($needSetup) && $needSetup === true) )
					$successMsg = 'User creation was successful.<br>Make sure to update the environment.txt file to reflect the superuser username.<br></font><font color="#000000"><a href="/_/">Login</a>';
				else
					$successMsg = 'User creation was successful';
			}
		}
		elseif(isset($_POST['submit'])) {
			$errMsg = "You did not fill out all required fields";
		}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Setup Commissioner</title>
	</head>
	<body>
		<center>
<?php
	if( $registerAllow === true ) {
		echoLinks();
		echoAdminLinks();
	}
?>
			<h3><?php echo $msg=(isset($needSetup) && $needSetup === true) ? "Setup superuser" : "Register admin"; ?></h3>
			<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
				<table>
					<tr>
						<td>New Username: </td>
						<td><input type="text" name="username"></td>
					</tr>
					<tr>
						<td>New Pass1: </td>
						<td><input type="password" name="pass1"></td>
					</tr>
					<tr>
						<td>Repeat Pass1: </td>
						<td><input type="password" name="rPass1"></td>
					</tr>
					<tr>
						<td>New Pass2: </td>
						<td><input type="password" name="pass2"></td>
					</tr>
					<tr>
						<td>Repeat Pass2: </td>
						<td><input type="password" name="rPass2"></td>
					</tr>
					<tr>
						<td><input type="submit" name="submit" value="Submit"></td>
						<td></td>
					</tr>
				</table>
			</form>
<?php
	if(isset($errMsg)) {
?>
			<p name="errorMsg"><font color="#FF0000"><?php echo $errMsg; ?></font></p>
<?php
	}
?>
<?php
	if(isset($successMsg)) {
?>
			<p name="successMsg"><font color="#FF0000"><?php echo $successMsg; ?></font></p>
<?php
	}
?>
		</center>
	</body>
</html>
<?php
	}
	else {
		header("Location: /");
	}
?>
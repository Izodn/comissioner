<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/user.php';
	session_start();
	if( isset($_SESSION['userObj']) )
		header('Location: logout.php');
	if( isset($_POST['login']) ) {
		if( empty($_POST['email']) || empty($_POST['password']) ) {
			$errMsg = "Please fill out all fields";
		}
		$userObj = new user($_POST['email'], $_POST['password']);
		if(!$userObj->doLogin()) { //Will return false if bad can't login
			$errMsg = $userObj->errMsg;
		}
		else {
			$_SESSION['userObj'] = $userObj;
			header('Location: index.php'); //Successfully logged in, goto index.
		}
	}
	elseif(isset($_POST['register'])) {
		header('Location: register.php');
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Commissioner - Login</title>
	</head>
	<body>
		<center>
			<h3>Login</h3>
			<form action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="POST">
				<table>
					<tr>
						<td>Email: </td>
						<td><input type="text" name="email"></td>
					</tr>
					<tr>
						<td>Password: </td>
						<td><input type="password" name="password"></td>
					</tr>
					<tr>
						<td><input type="submit" name="login" value="Login"></td>
						<td><input type="submit" name="register" value="Register"></td>
					</tr>
				</table>
			</form>
			<?php echo ($errMsg = isset($errMsg) ? '<font color="#FF0000">'.$errMsg.'</font>' : "")."\n"; ?>
		</center>
	<body>
</html>
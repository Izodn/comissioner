<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/user.php';
	if( isset($_POST['register']) ) {
		if( empty($_POST['username']) || empty($_POST['password']) || empty($_POST['rPassword']) ) {
			$errMsg = "Please fill out all fields";
		}
		elseif( $_POST['password'] !== $_POST['rPassword'] ) {
			$errMsg = "Passwords must match";
		}
		else {
			if(!isset($_SESSION))
				session_start();
			$userObj = new user($_POST['username'], $_POST['password']);
			if(!$userObj->doCreate()) { //Will return false if cannot create / login after create
				$errMsg = $userObj->errMsg;
			}
			else {
				$_SESSION['userObj'] = $userObj;
				header('Location: index.php'); //Successfully created & logged in, goto index
			}
		}
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
						<td>Username: </td>
						<td><input type="text" name="username"></td>
					</tr>
					<tr>
						<td>Password: </td>
						<td><input type="password" name="password"></td>
					</tr>
					<tr>
						<td>Repeat Password: </td>
						<td><input type="password" name="rPassword"></td>
					</tr>
					<tr>
						<td><input type="submit" name="register" value="Register"></td>
					</tr>
				</table>
			</form>
			<?php echo ($errMsg = isset($errMsg) ? '<font color="#FF0000">'.$errMsg.'</font>' : "")."\n"; ?>
		</center>
	<body>
</html>
<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/user.php';
	if( isset($_POST['register']) ) {
		if( empty($_POST['lastName']) || empty($_POST['lastName']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['rPassword']) ) {
			$errMsg = "Please fill out all fields";
		}
		elseif( $_POST['password'] !== $_POST['rPassword'] ) {
			$errMsg = "Passwords must match";
		}
		else {
			if(!isset($_SESSION))
				session_start();
			$userObj = new user($_POST['email'], $_POST['password']);
			if(!$userObj->doCreate($_POST['firstName'], $_POST['lastName'])) { //Will return false if cannot create / login after create
				$errMsg = $userObj->errMsg;
			}
			else {
				$_SESSION['userObj'] = $userObj;
				header('Location: index.php'); //Successfully created & logged in, goto index
			}
		}
	}
	elseif(isset($_POST['back'])) {
		header("Location: /login.php");
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Commissioner - Login</title>
	</head>
	<body>
		<center>
			<h3>Registration</h3>
			<form action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="POST">
				<table>
					<tr>
						<td>First Name: </td>
						<td><input type="text" name="firstName"<?php echo $value=!empty($_POST['firstName'])?' value="'.htmlentities($_POST['firstName']).'"':"" ?>></td>
					</tr>
					<tr>
						<td>Last Name: </td>
						<td><input type="text" name="lastName"<?php echo $value=!empty($_POST['lastName'])?' value="'.htmlentities($_POST['lastName']).'"':"" ?>></td>
					</tr>
					<tr>
						<td>Email: </td>
						<td><input type="text" name="email"<?php echo $value=!empty($_POST['email'])?' value="'.htmlentities($_POST['email']).'"':"" ?>></td>
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
				<input type="submit" name="back" value="Back">
			</form>
			<?php echo ($errMsg = isset($errMsg) ? '<font color="#FF0000">'.$errMsg.'</font>' : "")."\n"; ?>
		</center>
	<body>
</html>
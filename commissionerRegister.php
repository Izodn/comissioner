<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/user.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/links.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/requireLogin.php'; //Checks login and starts session
	requireLogin('superuser');
	if( isset($_POST['register']) ) {
		if( empty($_POST['lastName']) || empty($_POST['lastName']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['rPassword']) ) {
			$errMsg = "Please fill out all fields";
		}
		elseif( $_POST['password'] !== $_POST['rPassword'] ) {
			$errMsg = "Passwords must match";
		}
		else {
			$userObj = new user($_POST['email'], $_POST['password']);
			if(!$userObj->doCreate($_POST['firstName'], $_POST['lastName'], 'commissioner')) { //Will return false if cannot create / login after create
				$errMsg = $userObj->errMsg;
			}
			else {
				$successMsg = "Commissioner creation successful!";
			}
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Commissioner - Admin Register</title>
	</head>
	<body>
		<center>
			<?php
				$links = new links($_SESSION['userObj']);
				echo $links->getLinks();
			?>
			<h3>Commissioner Registration</h3>
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
						<td>*Password: </td>
						<td><input type="password" name="password"></td>
					</tr>
					<tr>
						<td>*Repeat Password: </td>
						<td><input type="password" name="rPassword"></td>
					</tr>
					<tr>
						<td><input type="submit" name="register" value="Register"></td>
					</tr>
				</table>
				<br>
			</form>
			<?php echo ($errMsg = isset($errMsg) ? '<font color="#FF0000">'.$errMsg.'</font><br>' : "")."\n"; ?>
			<?php echo ($successMsg = isset($successMsg) ? '<font color="#FF0000">'.$successMsg.'</font><br>' : "")."\n"; ?>
		</center>
	<body>
</html>
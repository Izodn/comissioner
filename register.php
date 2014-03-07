<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/user.php';
	if(empty($_SESSION))
		session_start();
	if(isset($_SESSION['userObj'])) //Avoid hitting this page when logged in
		header('Location: index.php');
	if( isset($_POST['register']) ) {
		if( empty($_POST['lastName']) || empty($_POST['lastName']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['rPassword']) ) {
			$errMsg = "Please fill out all fields";
		}
		elseif( $_POST['password'] !== $_POST['rPassword'] ) {
			$errMsg = "Passwords must match";
		}
		else {
			$userObj = new user($_POST['email'], $_POST['password']);
			if( (isset($env['PUBLIC_COMMISSIONER_REG']) && $env['PUBLIC_COMMISSIONER_REG'] === '1') && isset($_POST['userType'])) {
				if(!$userObj->doCreate($_POST['firstName'], $_POST['lastName'], strtolower($_POST['userType']) )) { //Will return false if cannot create / login after create
					$errMsg = $userObj->errMsg;
				}
				else {
					$_SESSION['userObj'] = $userObj;
					header('Location: index.php'); //Successfully created & logged in, goto index
				}
			}
			else {
				if(!$userObj->doCreate($_POST['firstName'], $_POST['lastName'])) { //Will return false if cannot create / login after create
					$errMsg = $userObj->errMsg;
				}
				else {
					$_SESSION['userObj'] = $userObj;
					header('Location: index.php'); //Successfully created & logged in, goto index
				}
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
		<script>
			defaultDetails = '<b>*</b><a href="javascript:showDetails()">(details)</a>'
			onload = function() {
				document.getElementById("passwdDetails").innerHTML = defaultDetails;
			}
			showDetails = function() {
				document.getElementById("passwdDetails").innerHTML = '<b>*</b><a href="javascript:hideDetails()">(hide)</a><br>\
				We do not store your passwords as plain text.<Br>\
				All passwords are hashed (a.k.a encrypted) using very powerful hashing algorithms.<br>\
				Example: "<b>password</b>" can be saved as "<b>$2y$10$H.npQ0Ad1xXWFQhkixNyRewx32GtuOmLsZ3P2m6xT4fxkjOmHMukW</b>"<br>\
				Note: You\'ll still use your desired password to login. This only enhances the security of the site.';
			}
			hideDetails = function() {
				document.getElementById("passwdDetails").innerHTML = defaultDetails;
			}
		</script>
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
						<td>*Password: </td>
						<td><input type="password" name="password"></td>
					</tr>
					<tr>
						<td>*Repeat Password: </td>
						<td><input type="password" name="rPassword"></td>
					</tr>
					<?php
						if( isset($env['PUBLIC_COMMISSIONER_REG']) && $env['PUBLIC_COMMISSIONER_REG'] === '1' ) { //Show type picker
							?>
								<tr>
									<td>Account Type: </td>
									<td>
										<select name="userType">
											<option selected="selected">Client</option>
											<option>Commissioner</option>
										</select>
									</td>
								</tr>
							<?php
						}
					?>
					<tr>
						<td><input type="submit" name="register" value="Register"></td>
					</tr>
				</table>
				<input type="submit" name="back" value="Back">
				<br><br>
				<div id="passwdDetails"></div>
			</form>
			<?php echo ($errMsg = isset($errMsg) ? '<font color="#FF0000">'.$errMsg.'</font>' : "")."\n"; ?>
		</center>
	<body>
</html>
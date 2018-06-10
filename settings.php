<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/user.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/links.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/password.php';
	session_start();
	if( !isset($_SESSION['userObj']) && ($_SESSION['userObj']->getUserType() === "superuser" || $_SESSION['userObj']->getUserType() === "commissioner") )
		header('Location: /'); //Not allowed, go away
	
	if( isset($_POST['back']) ) {
		if( isset($_GET['manageAccount']) && (isset($_POST['oldEmail']) || isset($_POST['oldPass'])) )
			header('Location: settings.php?manageAccount=Manage Account');
		else
			header('Location: settings.php');
	}
	if( !empty($_GET['setupGallery']) ) {
		header('Location: gallery.php?u='.$_SESSION['userObj']->getUserId().'&a='); //Navigate to gallery admin page
	}
	function defaultView() {
		?>
		<h3>Settings</h3>
		<br>
		<form action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="GET">
			<table>
				<tr>
					<td><input type="submit" name="paymentOptions" value="Payment Options"></td>
				</tr>
				<tr>
					<td><input type="submit" name="manageAccount" value="Manage Account"></td>
				</tr>
				<tr>
					<td><input type="submit" name="setupGallery" value="Setup Gallery"></td>
				</tr>
			</table>
		</form>
		<?php
	}
	function paymentView() {
		global $dbh;
		if( isset($_POST['addPaymentOption']) ) {
			if( !empty($_POST['paymentOptionName'])  ) {
				$_SESSION['userObj']->addPaymentOption($_POST['paymentOptionName']);
			}
			else { // Appear as if nothing happened
			}
		}
		elseif( isset($_POST['makeDefault']) && isset($_POST['id']) ) {
			$_SESSION['userObj']->changePaymentDefault($_POST['id']);
		}
		elseif( isset($_POST['remove']) && isset($_POST['id']) ) {
			if($_SESSION['userObj']->removePaymentOption($_POST['id']) === false) {
				$errMsg = $_SESSION['userObj']->errMsg;
			}
		}
		$query = <<<SQL
SELECT
	IACCOUNTID,
	CNAME,
	IISDEFAULT
FROM
	COM_ACCOUNT
WHERE
	IUSERID = ?
SQL;
		$runQuery = $dbh->prepare($query);
		$runQuery->bindValue(1, $_SESSION['userObj']->getUserId());
		$runQuery->execute();
		?>
		<h3>Payment Options</h3>
		<br>
		<table>
			<?php
				while($row = $runQuery->fetch(PDO::FETCH_ASSOC)) {
					?>
				<tr>
					<form action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="POST">
						<input type="hidden" name="id" value="<?php echo $row['IACCOUNTID']; ?>">
						<td><?php echo htmlentities($row['CNAME']); ?></td>
						<?php
							if($row['IISDEFAULT'] === "1")
								echo '<td><font color="#FF0000">Default</font></td>';
							else
								echo '<td><input type="submit" name="makeDefault" value="Make Default"></td>';
							$caQuery = <<<SQL
SELECT
	count(iCommissionId) comCount
FROM
	COM_COMMISSION
WHERE
	iAccountId = ?
SQL;
							$runCaQuery = $dbh->prepare($caQuery);
							$runCaQuery->bindValue(1, $row['IACCOUNTID']);
							$runCaQuery->execute();
							$caResult = $runCaQuery->fetch(PDO::FETCH_ASSOC);
							if( $caResult['comCount'] === '0')
								echo '<td><input alt="test" type="submit" name="remove" value="Remove"></td>';
							else
								echo '<td><button disabled="disabled" alt="Cannot remove payment options that are in use">Remove</button></td>';

						?>
					</form>
				</tr>
					<?php
				}
			?>
		</table>
		<br>
		<table>
			<form action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="POST">
				<tr>
					<td><input type="text" name="paymentOptionName" placeholder="Name of payment option"></td>
					<td><input type="submit" name="addPaymentOption" value="Add Payment Option"></td>
				</tr>
				<tr>
					<td><input type="submit" name="back" value="Back"></td>
				</tr>
			</form>
		</table>
		<br>
		<?php
			if(isset($errMsg))
				echo '<font color="#FF0000">'.$errMsg.'</font>';
	}
	function accountView($type="none") {
		global $dbh;
		if( isset($_POST['changePass']) ) {
			if( empty($_POST['oldPass']) || empty($_POST['newPass']) || empty($_POST['rNewPass']) ) {
				$errMsg = "Please fill out all password fields";
			}
			elseif( $_POST['newPass'] !== $_POST['rNewPass'] ) {
				$errMsg = "New passwords must match";
			}
			else {
				$query = <<<SQL
SELECT
	CPASSWORD
FROM
	COM_USER
WHERE
	IUSERID = ?
SQL;
				$runQuery = $dbh->prepare($query);
				$runQuery->bindValue(1, $_SESSION['userObj']->getUserId());
				$runQuery->execute();
				$result = $runQuery->fetch(PDO::FETCH_ASSOC);
				if( password_verify($_POST['oldPass'], $result['CPASSWORD']) ) {
					$_SESSION['userObj']->changePass($_POST['newPass']); //The user class expects an unhashed password
					$successMsg = "Password Changed";
				}
				else {
					$errMsg = "The old password was incorrect";
				}
			}
		}
		if( isset($_POST['changeEmail']) ) {
			if( empty($_POST['oldEmail']) || empty($_POST['newEmail']) || empty($_POST['rNewEmail']) ) {
				$errMsg = "Please fill out all password fields";
			}
			elseif( $_POST['newEmail'] !== $_POST['rNewEmail'] ) {
				$errMsg = "New emails must match";
			}
			else {
				$newEmail = strtolower($_POST['newEmail']);
				$oldEmail = strtolower($_POST['oldEmail']);
				if( $oldEmail !== $_SESSION['userObj']->email )
					$errMsg = "Current email was wrong.";
				else {
					if( !$_SESSION['userObj']->changeEmail($newEmail) ) { //Call it this way so we catch the error
						$errMsg = $_SESSION['userObj']->errMsg;
					}
					else {
						$successMsg = "Email changed";
					}
				}
			}
		}
		?>
		<h3>Account Management</h3>
		<br>
		<?php
		if( $type==="password" ) {
			?>
			<p>Password Change</p>
			<form action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="POST">
				<input type="hidden" name="type" value="password">
				<table>
					<tr>
						<td>Old Password: </td>
						<td><input type="password" name="oldPass"></td>
					</tr>
					<tr>
						<td>New Password: </td>
						<td><input type="password" name="newPass"></td>
					</tr>
					<tr>
						<td>Repeat New Password: </td>
						<td><input type="password" name="rNewPass"></td>
					</tr>
					<tr>
						<td></td>
						<td><input type="submit" name="changePass" value="Change Password"></td>
					</tr>
					<tr>
						<td><input type="submit" name="back" value="Back"></td>
						<td></td>
					</tr>
				</table>
			</form>
			<?php
		}
		elseif( $type==="email" ) {
			?>
			<p>Email Change</p>
			<form action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="POST">
				<input type="hidden" name="type" value="email">
				<table>
					<tr>
						<td>Current Email: </td>
						<td><input type="text" name="oldEmail"></td>
					</tr>
					<tr>
						<td>New Email: </td>
						<td><input type="text" name="newEmail"></td>
					</tr>
					<tr>
						<td>Repeat New Email: </td>
						<td><input type="text" name="rNewEmail"></td>
					</tr>
					<tr>
						<td></td>
						<td><input type="submit" name="changeEmail" value="Change Email"></td>
					</tr>
					<tr>
						<td><input type="submit" name="back" value="Back"></td>
						<td></td>
					</tr>
				</table>
			</form>
			<?php
		}
		else {
			?>
			<form action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="POST">
				<input type="hidden" name="type" value="password">
				<input type="submit" value="Change Password">
			</form>
			<form action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="POST">
				<input type="hidden" name="type" value="email">
				<input type="submit" value="Change Email">
			</form>
			<br>
			<form action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="POST">
				<input type="submit" name="back" value="Back">
			</form>
			<?php
		}
		if( isset($errMsg) ) {
			echo '<br><font color="#FF0000">'.$errMsg.'</font>';
		}
		if( isset($successMsg) ) {
			echo '<br><font color="#FF0000">'.$successMsg.'</font>';
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Commissioner - Settings</title>
	</head>
	<body>
		<center>
			<?php
				$links = new links($_SESSION['userObj']);
				echo $links->getLinks();
				if( empty($_GET) )
					defaultView();
				elseif( !empty($_GET['paymentOptions']) ) {
					paymentView();
				}
				elseif( !empty($_GET['manageAccount']) ) {
					if( isset($_POST['type']) && $_POST['type'] === 'password' )
						accountView("password");
					elseif( isset($_POST['type']) && $_POST['type'] === 'email' )
						accountView("email");
					else
						accountView("none");
				}
			?>
		</center>
	</body>
</html>
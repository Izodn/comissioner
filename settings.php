<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/user.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/links.php';
	session_start();
	if( !isset($_SESSION['userObj']) && ($_SESSION['userObj']->getUserType() === "superuser" || $_SESSION['userObj']->getUserType() === "commissioner") )
		header('Location: /'); //Not allowed, go away
	
	if( isset($_POST['back']) ) {
		header('Location: settings.php');
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
			<table>
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
			$_SESSION['userObj']->removePaymentOption($_POST['id']);
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
		$runQuery->bindParam(1, $_SESSION['userObj']->getUserId());
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
						?>
						<td><input type="submit" name="remove" value="Remove"></td>
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
		<?php
	}
	function accountView() {
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
				$runQuery->bindParam(1, $_SESSION['userObj']->getUserId());
				$runQuery->execute();
				$result = $runQuery->fetch(PDO::FETCH_ASSOC);
				if( $result['CPASSWORD'] === md5($_POST['oldPass']) ) {
					$_SESSION['userObj']->changePass($_POST['newPass']); //The user class expects an unhashed password
					$successMsg = "Password Changed";
				}
				else {
					$errMsg = "The old password was incorrect";
				}
			}
		}
		?>
		<h3>Account Management</h3>
		<br>
		<p>Password Change</p>
		<form action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="POST">
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
					accountView();
				}
				elseif( !empty($_GET['setupGallery']) ) {
					//Should navigate to gallery.php
				}
			?>
		</center>
	</body>
</html>
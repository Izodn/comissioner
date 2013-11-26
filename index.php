<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/user.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/links.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/dump.php';
	session_start();
	if( !isset($_SESSION['userObj']) ) { //If not logged in
		global $dbh;
		global $links;
		$query = <<<SQL
SELECT
	iUserId
FROM
	COM_USER
LIMIT
	0,1
SQL;
		$runQuery = $dbh->prepare($query);
		$runQuery->execute();
		$result = $runQuery->fetch(PDO::FETCH_ASSOC);
		if( $result === false ) { //No users found, need superuser setup
			header('Location: _/setup.php');
		}
		else {
			header('Location: login.php');
		}
	}
	elseif($_SESSION['userObj']->getUserType() === "superuser" || $_SESSION['userObj']->getUserType() === "commissioner") { //Is commissioner / superuser
		global $dbh;
		$query = <<<SQL
SELECT
	IACCOUNTID
FROM
	COM_ACCOUNT
WHERE
	IUSERID = ?
LIMIT
	0,1
SQL;
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $_SESSION['userObj']->getUserId());
		$runQuery->execute();
		$result = $runQuery->fetch(PDO::FETCH_ASSOC);
		if( $result['IACCOUNTID'] == null ) { //If no payment options go to settings page
			header("Location: settings.php?paymentOptions=Payment+Options");
		}
		if( isset($_POST['submit']) && !empty($_POST['firstName']) && !empty($_POST['lastName']) && !empty($_POST['email']) && !empty($_POST['title']) && !empty($_POST['paymentOption']) ) {
			$cost = 0;
			$description = !empty($_POST['description']) ? $_POST['description'] : "";
			if(!empty($_POST['cost'])) {
				//Expects American English numbers like "$12.50"
				if($_POST['cost'] === str_replace(".", "", $_POST['cost'])) { //No decimal
					$_POST['cost'] .= ".00";
				}
				$cost = intval(str_replace("$", "", str_replace(".", "", $_POST['cost'])));
			}
			/*
			* 2013/11/25 - Brandon
			* We need to create a user for the client that can be "claimed"
			* For this we don't use a pass, and set auto-login to false (4th param).
			* The doCreate function will return false, and set $obj->errMsg. We don't need to handle this.
			* We don't handle the false on create because we only want to create one if one doesn't already exist.
			*/
			//START CLIENT USER CREATE
			$client = new user($_POST['email']); // Create client object
			$client->doCreate($_POST['firstName'], $_POST['lastName'], 'client', false);
			//END CLIENT USER CREATE
			$query = <<<SQL
INSERT INTO
	COM_COMMISSION(CTITLE, CDESCRIPTION, ICLIENTID, ICOMMISSIONERID, ICOST, IACCOUNTID, IPAYMENTSTATUSID, IPROGRESSSTATUSID, IISARCHIVED, DCREATEDDATE)
VALUES(?, ?, (SELECT IUSERID FROM COM_USER WHERE CEMAIL = ?), ?, ?, ?, 1, 1, 0, NOW())
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $_POST['title']);
			$runQuery->bindParam(2, $description);
			$runQuery->bindParam(3, $_POST['email']);
			$runQuery->bindParam(4, $_SESSION['userObj']->getUserId());
			$runQuery->bindParam(5, $cost); //Use already formatted cost var
			$runQuery->bindParam(6, $_SESSION['userObj']->getPaymentId($_POST['paymentOption']));
			if(!$runQuery->execute())
				$errMsg = "Could not submit commission...";
			else
				$successMsg = "Successful!";
		}
		elseif( isset($_POST['submit']) ) {
			$errMsg = "Please fill out all required fields";
		}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Commissioner - Home</title>
	</head>
	<body>
		<center>
			<?php
				$links = new links($_SESSION['userObj']);
				echo $links->getLinks();
			?>
			<br><br>
			<form action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="get">
				Autofill for: 
				<select name="autoFill">
					<option>--Choose a client--</option>
					<?php
						$query = <<<SQL
SELECT
	cu.CEMAIL
FROM
	COM_USER cu
INNER JOIN
	COM_COMMISSION cc ON cc.ICLIENTID = cu.IUSERID
WHERE
	cc.ICOMMISSIONERID = ?
SQL;
						$runQuery = $dbh->prepare($query);
						$runQuery->bindParam(1, $_SESSION['userObj']->getUserId());
						$runQuery->execute();
						while($row = $runQuery->fetch(PDO::FETCH_ASSOC)) {
							echo '<option value="'.$row['CEMAIL'].'">'.$row['CEMAIL'].'</option>';
						}
					?>
				</select>
				<input type="submit" name="submit" value="Go">
			</form>
			<form action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="post">
				<br>
				<table>
					<tr>
					</tr>
					<tr>
						<td>Client's First Name:<font color="red">*</font></td>
						<td><input type="text" name="firstName" size='32' maxlength="225" ></td>
					</tr>
					<tr>
						<td>Client's Last Name:<font color="red">*</font></td>
						<td><input type="text" name="lastName" size='32' maxlength="225" ></td>
					</tr>
					<tr>
						<td>Client's Email:<font color="red">*</font></td>
						<td><input type="text" name="email" size='32' maxlength="225" ></td>
					</tr>
					<tr>
						<td>Commission Title:<font color="red">*</font></td>
						<td><input type="text" name="title" size='16' maxlength="32" ></td>
					<tr>
						<td>Price:<font color="red">*</font></td>
						<td><input type="text" name="cost" size='16' maxlength="8" ></td>
					</tr>
					<tr>
						<td>Account Type:<font color="red">*</font></td>
						<td>
							<select name="paymentOption">
							<?php
								$paymentOptions = $_SESSION['userObj']->getPaymentOptions();
								foreach($paymentOptions as $row) {
									echo '<option'.($default = $row['IISDEFAULT'] === '1'?' selected="selected"':'').'>'.$row['CNAME'].'</option>';
								}
							?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Purchase Description:</td>
						<td><textarea cols='26'rows="4" name="description"></textarea>
					</tr>
				</table>
				<input type="submit" name="submit" value="Submit">
				<br>
				<font color="red">*</font> = Required.
			</form>
			<?php
				echo $errMsg = isset($errMsg) ? '<font color="#FF0000">'.$errMsg.'</font>' : ''; //Show error message if exists
				echo $successMsg = isset($successMsg) ? '<font color="#FF0000">'.$successMsg.'</font>' : ''; //Show error message if exists
			?>
		</center>
	</body>
</html>
<?php
	}
	elseif( $_SESSION['userObj']->getUserType() === "client" ) { //Is client
		?>
<!DOCTYPE html>
<html>
	<head>
		<title>Commissioner - Home</title>
	</head>
	<body>
		<center>
			<?php
				$links = new links($_SESSION['userObj']);
				echo $links->getLinks();
			?>
		</center>
	</body>
</html>
		<?php
	}
	else {
		//Type not handled, this is bad.
	}
?>

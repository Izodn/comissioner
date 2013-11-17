<?php 
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
	if (isset($_POST['submit'])) 
	{
		global $dbh;
		$query = "
			SELECT
				IORDERNUMBER
			FROM
				book_records
			ORDER BY
				IORDERNUMBER DESC
			LIMIT
				0, 1
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->execute();
		$tmp = $runQuery->fetch(PDO::FETCH_BOTH);
		$_POST['OrderNumber'] = $tmp[0]+1;
		if (!$_POST['FirstName'] || !$_POST['LastName'] || !$_POST['Price'] || !$_POST['AccountType'] || !$_POST['Email'] || !$_POST['ComTitle']) 
		{
			die('You did not complete all of the required fields');
		}
		if( !filter_var($_POST['Email'], FILTER_VALIDATE_EMAIL) )
		{
			die('You did not enter a valid email address.');
		}
		$CFIRSTNAME = $_POST['FirstName'];
		$CLASTNAME = $_POST['LastName'];
		$IORDERNUMBER = $_POST['OrderNumber'];
		$IPRICE = $_POST['Price'];
		$CACCOUNTTYPE = $_POST['AccountType'];
		$DESCRIPTION = $_POST['Description'];
		$user_key3 = globalOut('user_key3');
		$check = "SELECT IORDERNUMBER FROM book_records WHERE IORDERNUMBER = ? AND IUSERID = ?";
		$stmt = $dbh->prepare($check);
		$stmt->bindParam(1, $IORDERNUMBER, PDO::PARAM_STR, 225);
		$stmt->bindParam(2, $user_key3, PDO::PARAM_STR, 225);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_BOTH);
		if(safe($row[0]) != '')
		{
			die('Sorry, the ordernumber '. $IORDERNUMBER.' is already in use.');
		}
		/* REMOVED 09/26/2013 - BRANDON
		if (!get_magic_quotes_gpc()) 
		{
			$_POST['FirstName'] = safe($_POST['FirstName']);
			$_POST['LastName'] = safe($_POST['LastName']);
			$_POST['OrderNumber'] = safe($_POST['OrderNumber']);
			$_POST['Price'] = safe($_POST['Price']);
			$_POST['AccountType'] = safe($_POST['AccountType']);
			$_POST['Description'] = safe($_POST['Description']);
		}
		*/
		date_default_timezone_set('America/Los_Angeles');
		$query = "
			SELECT
				CFIRSTNAME,
				CLASTNAME,
				CUSERNAMEDETAIL
			FROM
				client_detail
			WHERE
				CUSERNAMEDETAIL = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $_POST['Email'], PDO::PARAM_STR, 225);
		$runQuery->execute();
		$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
		if(($_POST['FirstName'] != safe($runQueryArray['CFIRSTNAME']) || $_POST['LastName'] != safe($runQueryArray['CLASTNAME'])) && $_POST['Email'] == safe($runQueryArray['CUSERNAMEDETAIL']))
		{
			die('That Email/First Name/Last Name combination is not what we have in the database.<br> Please try the Autofill feature for previous clients.');
		}
		$query = "
			SELECT
				CUSERNAME
			FROM
				client_account
			WHERE
				CUSERNAME = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, md5($_POST['Email']), PDO::PARAM_STR, 225);
		$runQuery->execute();
		$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
		if(safe($runQueryArray['CUSERNAME']) == md5($_POST['Email']))
		{
			$query = "
				SELECT
					ICOMCOUNT
				FROM
					client_detail
				WHERE
					CUSERNAMEDETAIL = ?
			";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $_POST['Email'], PDO::PARAM_STR, 225);
			$runQuery->execute();
			$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
			$comCount = safe($runQueryArray['ICOMCOUNT']);
			$comCount++;
			$query = "
				UPDATE
					client_detail
				SET
					ICOMCOUNT = ?
				WHERE
					CUSERNAMEDETAIL = ?
			";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $comCount, PDO::PARAM_STR, 225);
			$runQuery->bindParam(2, $_POST['Email'], PDO::PARAM_STR, 225);
			$runQuery->execute();
		}
		else
		{
			$query = "
				INSERT INTO
					client_account
				(CUSERNAME)
				VALUES
				(?)
			";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, md5($_POST['Email']), PDO::PARAM_STR, 225);
			$runQuery->execute();
			$query = "
				INSERT INTO
					client_detail
				(ICLIENTID, CUSERNAMEDETAIL, ICOMCOUNT, CFIRSTNAME, CLASTNAME)
				VALUES
				((SELECT ICLIENTID FROM client_account WHERE CUSERNAME = ?), ?, '1', ?, ?)
			";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, md5($_POST['Email']), PDO::PARAM_STR, 225);
			$runQuery->bindParam(2, $_POST['Email'], PDO::PARAM_STR, 225);
			$runQuery->bindParam(3, $_POST['FirstName'], PDO::PARAM_STR, 225);
			$runQuery->bindParam(4, $_POST['LastName'], PDO::PARAM_STR, 225);
			$runQuery->execute();
		}	
		$clientPro=$_POST['OrderNumber'];
		$clientPro=md5($clientPro);
		$user_key3 = globalOut('user_key3');
		$md5_user_key3 = md5($user_key3);
		$insert = "INSERT INTO book_records (CFIRSTNAME, CLASTNAME, IORDERNUMBER, IORDERNUMBERMD5, IPRICE, CACCOUNTTYPE, CPROGRESSTYPE, CPAYMENTSTATUS, DDESCRIPTION, IUSERID, IUSERIDMD5, CTITLE, ICLIENTID)
		VALUES (?, ?, ?, ?, ?, ?, 'NoProgress', 'NoPayment', ?, ?, ?, ?, (SELECT ICLIENTID FROM client_account WHERE CUSERNAME = ?))";	
		$add_order = $dbh->prepare($insert);
		$add_order->bindParam(1, $_POST['FirstName'], PDO::PARAM_STR, 225);
		$add_order->bindParam(2, $_POST['LastName'], PDO::PARAM_STR, 225);
		$add_order->bindParam(3, $_POST['OrderNumber'], PDO::PARAM_STR, 225);
		$add_order->bindParam(4, $clientPro, PDO::PARAM_STR, 225);
		$add_order->bindParam(5, $_POST['Price'], PDO::PARAM_STR, 225);
		$add_order->bindParam(6, $_POST['AccountType'], PDO::PARAM_STR, 225);
		$add_order->bindParam(7, $_POST['Description'], PDO::PARAM_STR, 225);
		$add_order->bindParam(8, $user_key3, PDO::PARAM_STR, 225);
		$add_order->bindParam(9, $md5_user_key3, PDO::PARAM_STR, 225);
		$add_order->bindParam(10, $_POST['ComTitle'], PDO::PARAM_STR, 225);
		$add_order->bindParam(11, md5($_POST['Email']), PDO::PARAM_STR, 225);
		$add_order->execute();
		$query = "
			SELECT
				IRECORDCOUNT
			FROM
				user_detail
			WHERE
				IUSERID = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $user_key3, PDO::PARAM_STR, 225);
		$runQuery->execute();
		$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
		$recordCount = safe($runQueryArray['IRECORDCOUNT']);
		$recordCount++;
		$query = "
			UPDATE
				user_detail
			SET
				IRECORDCOUNT = ?
			WHERE
				IUSERID = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $recordCount, PDO::PARAM_STR, 225);
		$runQuery->bindParam(2, $user_key3, PDO::PARAM_STR, 225);
		$runQuery->execute();
		$query = "
			UPDATE
				com_request
			SET
				CRESPONCE = 'Deleted'
			WHERE
				IREQUESTID = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $_GET['requestID'], PDO::PARAM_STR, 225);
		$runQuery->execute();
		header('Location: adminIndex.php');
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>BookKeeping</title>
	</head>
	<body>
		<center>
			<?php
				global $dbh;
				echoLinks();
				echoCommissionLinks();
				echo '<br>';
				$query = "
					SELECT DISTINCT
						CUSERNAMEDETAIL
					FROM 
						client_detail
					ORDER BY 
						Lower(CUSERNAMEDETAIL)
				";
				$stmt = $dbh->prepare($query);
				$stmt->execute();
				$runQueryArray = $stmt->fetch(PDO::FETCH_BOTH);
				if(!safe($runQueryArray['CUSERNAMEDETAIL']) || safe($runQueryArray['CUSERNAMEDETAIL']) == '')
				{
				}
				else
				{
					$count=0;
					echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="get">';
					echo 'Autofill for:<select name="autoFill">';
					$result = $dbh->prepare($query);
					$result->execute();
					while($row = $result->fetch(PDO::FETCH_BOTH))
					{
						$var = 'returnNumber'.$count;
						if(isset($_GET['autoFill']) && $_GET['autoFill'] == safe($row['CUSERNAMEDETAIL']))
						{
							echo '<option selected value="'.$row['CUSERNAMEDETAIL'].'">'.$row['CUSERNAMEDETAIL'].'</option>';
						}
						else
						{
							echo '<option value="'.$row['CUSERNAMEDETAIL'].'">'.$row['CUSERNAMEDETAIL'].'</option>';
						}
						$count++;
					}
					echo '</select>';
					echo'<input type="submit" name="submit" value="Go">';
					echo '</form>';
					if( isset($_GET['autoFill']) )
					{
						echo '<a href="'.htmlentities($_SERVER['PHP_SELF']).'">Clear</a>';
						$query = "
							SELECT
								CFIRSTNAME,
								CLASTNAME,
								CUSERNAMEDETAIL
							FROM
								client_detail
							WHERE
								CUSERNAMEDETAIL = ?
						";
						if(isset($_GET['requestID']))
						{
							$query2 = "
								SELECT
									cr.CREQUESTTITLE,
									cr.CDESCRIPTION,
									cr.IREQUESTPRICE
								FROM
									com_request cr
								INNER JOIN
									client_detail cd ON (cd.ICLIENTID = cr.ICLIENTID)
								WHERE
									cd.CUSERNAMEDETAIL = ? AND
									cr.IREQUESTID = ?
									
							";	
							$runQuery2 = $dbh->prepare($query2);
							$runQuery2->bindParam(1, $_GET['autoFill'], PDO::PARAM_STR, 225);
							$runQuery2->bindParam(2, $_GET['requestID'], PDO::PARAM_STR, 225);
							$runQuery2->execute();
							$runQueryArray2 = $runQuery2->fetch(PDO::FETCH_BOTH);
						}
						$runQuery = $dbh->prepare($query);
						$runQuery->bindParam(1, $_GET['autoFill'], PDO::PARAM_STR, 225);
						$runQuery->execute();
						$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
					}
				}
				if(isset($_GET['requestID']))
				{
					echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'?requestID='.$_GET['requestID'].'" method="post">';
				}
				else
				{
					echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="post">';
				}
			?>
				<br>
				<table>
					<tr>
					</tr>
					<tr>
						<td>Client's First Name:<font color="red">*</font></td><td><input type="text" name="FirstName" size='32' maxlength="30" <?php if(isset($_GET['autoFill'])){echo 'value="'.safe($runQueryArray['CFIRSTNAME']).'" readonly="readonly"';}?>></td>
					</tr>
					<tr>
						<td>Client's Last Name:<font color="red">*</font></td><td><input type="text" name="LastName" size='32' maxlength="30" <?php if(isset($_GET['autoFill'])){echo 'value="'.safe($runQueryArray['CLASTNAME']).'" readonly="readonly"';}?>></td>
					</tr>
					<tr>
						<td>Client's Email:<font color="red">*</font></td><td><input type="text" name="Email" size='32' maxlength="128" <?php if(isset($_GET['autoFill'])){echo 'value="'.safe($runQueryArray['CUSERNAMEDETAIL']).'" readonly="readonly"';}?>></td>
					</tr>
					<tr>
						<td>Commission Title:<font color="red">*</font></td><td><input type="text" name="ComTitle" size='16' maxlength="30" <?php if(isset($_GET['requestID'])){echo 'value="'.safe($runQueryArray2['CREQUESTTITLE']).'" readonly="readonly"';}?>></td>
					<tr>
						<td>Price:<font color="red">*</font></td><td><input type="text" name="Price" size='16' maxlength="12" <?php if(isset($_GET['requestID'])){echo 'value="'.safe($runQueryArray2['IREQUESTPRICE']).'" readonly="readonly"';}?>></td>
					</tr>
					<?php
						$query = "
							SELECT
								CVALUE,
								COTHERFIELD
							FROM
								user_settings
								
							WHERE
								CTYPE = 'paymentMethod' AND
								IUSERID = ?
						";
						$runQuery = $dbh->prepare($query);
						$runQuery->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
						$runQuery->execute();
						$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
						if($runQueryArray['CVALUE'])
						{
							?>
							<tr>
								<td>Account Type:<font color="red">*</font></td><td>
								<select name="AccountType">
								<?php
									$runQuery = $dbh->prepare($query);
									$runQuery->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
									$runQuery->execute();
									while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
									{
										if(isset($runQueryArray['COTHERFIELD']) && $runQueryArray['COTHERFIELD'] == 'default')
										{
											echo '<option selected>'.$runQueryArray['CVALUE'].'</option>';
										}
										else
										{
											echo '<option>'.$runQueryArray['CVALUE'].'</option>';
										}
									}
								?>
								</select>
							</tr>
							<?php
						}
						else
						{
							?>
							<tr>
								<td>Account Type:<font color="red">*</font></td><td><input type="text" name="AccountType" size='32' maxlength="30">
							</tr>
							<?php
						}
					?>
					<tr>
						<td>Purchase Description:</td><td><textarea cols='26'rows="4" name="Description" <?php if(isset($_GET['requestID'])){echo 'readonly="readonly">'.safe($runQueryArray2['CDESCRIPTION']);} else{echo '>';}?></textarea>
					</tr>
				</table>
				<input type="submit" name="submit" value="Submit"><br />
				<font color="red">*</font> = Required.
			</form>
		</center>
	</body>
</html>
<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
	function addAccount($a, $b, $c = false)
	{
		global $dbh;
		global $_PAGE;
		$c = $c === true ? ",1" : ",0";
		$query = "
			INSERT INTO
				user_settings
			(
				CTYPE,
				CVALUE,
				IUSERID,
				ICURRENCYTYPE
			)
			VALUES
			(
				'paymentMethod', 
				?, 
				?
				".$c."
			)
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $a, PDO::PARAM_STR, 225);
		$runQuery->bindParam(2, $b, PDO::PARAM_STR, 225);
		$runQuery->execute();
		redirectHandler($_PAGE['Last']);
	}
	function defaultAccount($a)
	{
		global $dbh;
		global $_PAGE;
		$query = "
			UPDATE
				user_settings
			SET
				COTHERFIELD = ''
			WHERE
				IUSERID = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
		$runQuery->execute();
		$query = "
			UPDATE
				user_settings
			SET
				COTHERFIELD = 'default'
			WHERE
				ISETTINGID = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $a, PDO::PARAM_STR, 225);
		$runQuery->execute();
		redirectHandler($_PAGE['Last']);
	}
	function removeAccount($a)
	{
		global $dbh;
		global $_PAGE;
		$query = "
			DELETE FROM
				user_settings
			WHERE
				IUSERID = ? AND
				ISETTINGID = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
		$runQuery->bindParam(2, $a, PDO::PARAM_STR, 225);
		$runQuery->execute();
		redirectHandler($_PAGE['Last']);
	}
	function changePassword($var)
	{
		global $dbh;
		global $_PAGE;
		if(getType($var) !== 'array')
		{
			writeLog('Password change request not array', null, 'HARD');
		}
		$query = "
			UPDATE
				user_account
			SET
				CPASSWORD = ?,
				CPASSWORD2 = ?
			WHERE
				IUSERID = ? AND
				CUSERNAME = ? AND
				CPASSWORD = ? AND
				CPASSWORD2 = ?
		";
		$iuserid = globalOut('user_key3');
		$cusername = globalOut('users');
		$cpassword = globalOut('user_key1');
		$cpassword2 = globalOut('user_key2');
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, md5($var[0]));
		$runQuery->bindParam(2, md5($var[1]));
		$runQuery->bindParam(3, $iuserid);
		$runQuery->bindParam(4, $cusername);
		$runQuery->bindParam(5, $cpassword);
		$runQuery->bindParam(6, $cpassword2);
		$runQuery->execute();
		globalClear('user_key1');
		globalClear('user_key2');
		globalIn('user_key1', md5($var[0]));
		globalIn('user_key2', md5($var[1]));
		redirectHandler($_PAGE['Last']);
	}
	function addAccountForm()
	{
		?>
		<form name="" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
			<table>
				<?php
					global $dbh;
					$query = "
						SELECT
							ISETTINGID,
							CVALUE,
							COTHERFIELD,
							ICURRENCYTYPE
						FROM
							user_settings
							
						WHERE
							CTYPE = 'paymentMethod' AND
							IUSERID = ?
					";
					$runQuery = $dbh->prepare($query);
					$runQuery->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
					$runQuery->execute();
					echo '<table>';
					while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
					{
						echo '<tr>';
						if( !empty($runQueryArray['ICURRENCYTYPE']) )
							echo '<td>(<font color="red">$</font>)</td>';
						else
							echo '<td></td>';
						if($runQueryArray['COTHERFIELD'] == 'default')
						{
							echo '<td>'.$runQueryArray['CVALUE'].'</td>';
							echo '<td><font color="FF0000">Default</font></td>';
							echo '<form name="" action="'.$_SERVER['PHP_SELF'].'" method="POST">';
							echo '<input type="hidden" name="delete" value="'.$runQueryArray['ISETTINGID'].'">';
							echo '<td><input type="Submit" name="paymentMethodDelete" value="Remove"></td>';
							echo '</form>';
						}
						else
						{
							echo '<form name="" action="'.$_SERVER['PHP_SELF'].'" method="POST">';
							echo '<input type="hidden" name="default" value="'.$runQueryArray['ISETTINGID'].'">';
							echo '<td>'.$runQueryArray['CVALUE'].'</td>';
							echo '<td><input type="Submit" name="paymentMethodDefault" value="Make Default"></td>';
							echo '</form>';
							echo '<form name="" action="'.$_SERVER['PHP_SELF'].'" method="POST">';
							echo '<input type="hidden" name="delete" value="'.$runQueryArray['ISETTINGID'].'">';
							echo '<td><input type="Submit" name="paymentMethodDelete" value="Remove"></td>';
							echo '</form>';
						}
						echo '</tr>';
					}
					echo '</table>';
					echo '<br>';
				?>
			</table>
		</form>
		<form name="" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
			<table>
				<tr>
					<td>
						<input type="text" name="accountName">
					</td>
					<td>
						<input type="Submit" name="addAccount" value="Add Account">
					</td>
				</tr>
				<tr>
					<td>Currency: <input type="checkbox" name="currency" /></td>
				</tr>
				<tr>
					<td>
						<input type="Submit" name="Back" value="Back">
					</td>
				</tr>
			</table>
		</form>
		<?php
	}
	function changePasswordForm()
	{
		?>
		<form name="" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
			<table>
				<tr>
					<td>Current Password 1: </td>
					<td><input type="password" name="password[0]"></td>
				</tr>
				<tr>
					<td>Current Password 2: </td>
					<td><input type="password" name="password[1]"></td>
				</tr>
				<tr>
					<td>New Password 1: </td>
					<td><input type="password" name="password[2]"></td>
				</tr>
				<tr>
					<td>Repeat New Password 1: </td>
					<td><input type="password" name="password[3]"></td>
				</tr>
				<tr>
					<td>New Password 2: </td>
					<td><input type="password" name="password[4]"></td>
				</tr>
				<tr>
					<td>Repeat New Password 2: </td>
					<td><input type="password" name="password[5]"></td>
				</tr>
				<tr>
					<td></td><td><input type="Submit" name="passwordChangeSubmit" value="Change Password"></td>
				</tr>
				<tr>
					<td><input type="Submit" name="Back" value="Back"></td><td></td>
				</tr>
			</table>
		</form>
		<?php
	}
	function defaultForm()
	{
		?>
		<form name="" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="GET">
			<table>
				<tr>
					<td>
						<input type="Submit" name="addAccountType" value="Payment Methods">
					</td>
				</tr>
				<tr>
					<td>
						<input type="Submit" name="changePassword" value="Change Password">
					</td>
				</tr>
				<tr>
					<td>
						<input type="Submit" name="setupGallery" value="Setup Gallery">
					</td>
				</tr>
			</table>
		</form>
		<?php
	}
	if(isset($_POST['passwordChangeSubmit']))
	{
		if(isset($_POST['password'][0]) && isset($_POST['password'][1]) && isset($_POST['password'][2]) && isset($_POST['password'][3]) && isset($_POST['password'][4]) && isset($_POST['password'][5]))
		{
			$pwdArray = $_POST['password'];
			$pwd0 = $pwdArray[0];
			$pwd1 = $pwdArray[1];
			$pwd2 = $pwdArray[2];
			$pwd3 = $pwdArray[3];
			$pwd4 = $pwdArray[4];
			$pwd5 = $pwdArray[5];
			if(!$pwd0 || !$pwd1 || !$pwd2 || !$pwd3 || !$pwd4 || !$pwd5)
			{
				echo 'Please fill out all fields.'."\n<br>\n";
				echo 'Click <a href="'.$_PAGE['Last'].'">here</a> to go back'."\n<br>\n";
				die('');
			}
			elseif($pwd2 !== $pwd3 || $pwd4 != $pwd5)
			{
				echo 'Oops! A repeated password doesn\'t match.'."\n<br>\n";
				echo 'Click <a href="'.$_PAGE['Last'].'">here</a> to go back'."\n<br>\n";
				die('');
			}
			elseif(md5($pwd0) != globalOut('user_key1') || md5($pwd1) != globalOut('user_key2'))
			{
				echo 'The current passwords you submitted didn\'t match.'."\n<br>\n";
				echo 'Click <a href="'.$_PAGE['Last'].'">here</a> to go back'."\n<br>\n";
				die('');
			}
			else
			{
				$var = array($pwd3, $pwd5);
				changePassword($var);
			}
			redirectHandler($_PAGE['Last']);
		}
		else
		{
			redirectHandler($_PAGE['Last']);
		}
	}
	if(isset($_POST['addAccount']) && $_POST['addAccount'])
	{
		if(isset($_POST['accountName']) && $_POST['accountName'])
		{
			if( !empty($_POST['currency']) )
				addAccount($_POST['accountName'], globalOut('user_key3'), true);
			else
				addAccount($_POST['accountName'], globalOut('user_key3'));
		}
		else
		{
			redirectHandler($_PAGE['Last']);
		}
	}
	if(isset($_POST['paymentMethodDefault']) && isset($_POST['default']) && $_POST['default'])
	{
		defaultAccount($_POST['default']);
	}
	if(isset($_POST['paymentMethodDelete']) && isset($_POST['delete']) && $_POST['delete'])
	{
		removeAccount($_POST['delete']);
	}
?>
<html>
	<head>
	</head>
	<body>
		<center>
			<?php
				echoLinks();
			?>
			<br>
			<?php
				if(!isset($_GET) || $_GET == array())
				{
					echo 'Settings';
					echo '<br>';
					echo '<br>';
					defaultForm();
				}
				elseif(isset($_GET['addAccountType']) || (isset($_GET['c']) && $_GET['c'] == 'accountType'))
				{
					echo 'Payment Methods';
					echo '<br>';
					echo '<br>';
					if(isset($_GET['addAccountType']))
					{
						header('Location: ?c=accountType');
					}
					addAccountForm();
					if(globalOut('success'))
					{
						echo "\n<br>\n<font color=\"FF0000\">Success</font>";
						globalClear('success');
					}
				}
				elseif(isset($_GET['changePassword']) || (isset($_GET['c']) && $_GET['c'] == 'changePassword'))
				{
					echo 'Password Change';
					echo '<br>';
					echo '<br>';
					if(isset($_GET['changePassword']))
					{
						header('Location: ?c=changePassword');
					}
					changePasswordForm();
					if(globalOut('success'))
					{
						echo "\n<br>\n<font color=\"FF0000\">Success</font>";
						globalClear('success');
					}
				}
				elseif(isset($_GET['setupGallery']))
				{
					header('Location: gallery.php');
				}
			?>
		</center>
	</body>
</html>
<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
	if(isset($_POST['back']))
	{
		header('Location: clientLogin.php');
	}
	if( isset($_POST['submit']) && $_POST['username'] )
	{
		if( !filter_var($_POST['username'], FILTER_VALIDATE_EMAIL) )
		{
			die('You did not enter a valid email address.');
		}
	}
	if(isset($_GET['IORDERNUMBER']) && isset($_GET['clientview']))
	{
		global $dbh;
		if(isset($_POST['submit']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['passwordr']) && $_POST['password'] == $_POST['passwordr'] && $_POST['username'] != '' && $_POST['password'] != '' && $_POST['passwordr'] != '')
		{
			$query = "
				SELECT
					cd.CUSERNAMEDETAIL
				FROM
					client_detail cd
				INNER JOIN
					book_records br ON (br.ICLIENTID = cd.ICLIENTID)
				WHERE
					br.IORDERNUMBERMD5 = ? AND
					br.IUSERIDMD5 = ?
			";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $_GET['IORDERNUMBER'], PDO::PARAM_STR, 225);
			$runQuery->bindParam(2, $_GET['clientview'], PDO::PARAM_STR, 225);
			$runQuery->execute();
			$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
			if(safe($runQueryArray[0]) != safe($_POST['username']))
			{
				die('I\'m sorry, that\'s not the Email we have on file for this commission. <br> Please try again, or contact the commissioner.');
			}
			$query = "
				SELECT
					CUSERNAME,
					CPASSWORD
				FROM
					client_account
				WHERE
					CUSERNAME = ?
			";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, md5($_POST['username']), PDO::PARAM_STR, 225);
			$runQuery->execute();
			$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
			if(!safe($runQueryArray[1]))
			{
				$username = $_POST['username'];
				$password = $_POST['password'];
				$query = "
					UPDATE
						client_account
					SET
						CPASSWORD = ?,
						IISACTIVE = '1'
					WHERE 
						CUSERNAME = ?
				";
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, md5($_POST['password']), PDO::PARAM_STR, 225);
				$runQuery->bindParam(2, md5($_POST['username']), PDO::PARAM_STR, 225);
				$runQuery->execute();
				die('<center><font color="red">User creation was successful.</font><br><a href="index.php">Click here</a> to login.</center>');
			}
			else
			{
				die('This account has already been claimed');
			}
		}
		else
		{
			$query = "
				SELECT
					br.ICLIENTID,
					cd.CUSERNAMEDETAIL,
					ca.CPASSWORD
				FROM
					book_records br
				INNER JOIN
					client_account ca ON (ca.ICLIENTID = br.ICLIENTID)
				INNER JOIN
					client_detail cd ON (cd.ICLIENTID = br.ICLIENTID)
				WHERE
					br.IORDERNUMBERMD5 = ? AND
					br.IUSERIDMD5 = ?
			";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $_GET['IORDERNUMBER'], PDO::PARAM_STR, 225);
			$runQuery->bindParam(2, $_GET['clientview'], PDO::PARAM_STR, 225);
			$runQuery->execute();
			$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
			echo safe($runQueryArray[2]);
			if(safe($runQueryArray[2]) == '')
			{
				?>
				<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
					<head>
						<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
						<title>BookKeeping</title>
					</head>
					<body>
						<center>
							<form action="<?php echo htmlentities($_SERVER['PHP_SELF']).'?IORDERNUMBER='.$_GET['IORDERNUMBER'].'&clientview='.$_GET['clientview']; ?>" method="post">
								<br>
								<table>
									<tr>
										<td>Email:</td><td><input type="text" name="username" maxlength="64" value="<?php echo $runQueryArray[1];?>" readonly="readonly"></td>
									</tr>
									<tr>
										<td>Password:</td><td><input type="password" name="password" maxlength="64"></td>
									</tr>
									<tr>
										<td>Confirm Password:</td><td><input type="password" name="passwordr" maxlength="64"></td>
									</tr>
								</table>
								<input type="submit" name="submit" value="Register">
							</form>
							<?php
								if(isset($_POST['submit']) && (!isset($_POST['username']) || $_POST['username']) == '')
								{
									echo '<font color="red">Please enter a Username</font>';
								}
								elseif(isset($_POST['submit']) && (!isset($_POST['password']) || $_POST['password']) == '')
								{
									echo '<font color="red">Please enter a Password</font>';
								}
								elseif(isset($_POST['submit']) && (!isset($_POST['passwordr']) || $_POST['passwordr']) == '')
								{
									echo '<font color="red">Please enter a password</font>';
								}
								elseif(isset($_POST['submit']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['passwordr']) && $_POST['password'] !== $_POST['passwordr'] )
								{
									echo '<font color="red">Your passwords do not match</font>';
								}
							?>
						</center>
					</body>
				</html>		
				<?php
			}
			else
			{
				die('You\'ve already registered an account');
			}
		}
	}
	elseif(isset($_POST['submit']) && (isset($_POST['username']) && $_POST['username'] != '') && (isset($_POST['password']) && $_POST['password'] != '') && (isset($_POST['passwordr']) && $_POST['passwordr'] != '') && (isset($_POST['firstName']) && $_POST['firstName'] != '') && (isset($_POST['lastName']) && $_POST['lastName'] != '') )
	{
		if($_POST['password'] != $_POST['passwordr'])
		{
			die('Your passwords do not match.');
		}
		global $dbh;
		$query = "
			SELECT
				cd.ICLIENTID,
				cd.CUSERNAMEDETAIL,
				ca.CPASSWORD,
				cd.CFIRSTNAME,
				cd.CLASTNAME
			FROM
				client_detail cd
			INNER JOIN
				client_account ca ON (ca.ICLIENTID = cd.ICLIENTID)
			WHERE
				CUSERNAMEDETAIL = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $_POST['username'], PDO::PARAM_STR, 225);
		$runQuery->execute();
		$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
		if(safe($runQueryArray[2]) != '')
		{
			die('That email is already registered.');
		}
		elseif((safe($runQueryArray[3]) != safe($_POST['firstName']) && safe($runQueryArray[3]) != '') || (safe($runQueryArray[4]) != $_POST['lastName'] && safe($runQueryArray[4]) != ''))
		{
			$nameNotMatch = '1';
			die('Your name doesn\'t match what\'s on file for this username. Please contact the commissioner to see if this is a mistake.');
		}
		elseif(safe($runQueryArray[0]) != '' && safe($runQueryArray[1]) != '' && safe($runQueryArray[2]) == '')
		{
			$query = "
				UPDATE
					client_account
				SET
					CPASSWORD = ?,
					IISACTIVE = '1'
				WHERE 
					CUSERNAME = ?
			";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, md5($_POST['password']), PDO::PARAM_STR, 225);
			$runQuery->bindParam(2, md5($_POST['username']), PDO::PARAM_STR, 225);
			$runQuery->execute();
			die('<center><font color="red">User creation was successful.</font><br><a href="index.php">Click here</a> to login.</center>');
		}
		elseif(safe($runQueryArray[0]) == '' && safe($runQueryArray[1]) == '' && safe($runQueryArray[2]) == '')
		{
			$query = "
				INSERT INTO
					client_account
				(CUSERNAME, CPASSWORD, IISACTIVE)
				VALUES
				(?, ?, '1')
			";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, md5($_POST['username']), PDO::PARAM_STR, 225);
			$runQuery->bindParam(2, md5($_POST['password']), PDO::PARAM_STR, 225);
			$runQuery->execute();
			$query = "
				INSERT INTO
					client_detail
				(ICLIENTID, CUSERNAMEDETAIL, ICOMCOUNT, CFIRSTNAME, CLASTNAME)
				VALUES
				((SELECT ICLIENTID FROM client_account WHERE CUSERNAME = ?), ?, '0', ?, ?)
			";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, md5($_POST['username']), PDO::PARAM_STR, 225);
			$runQuery->bindParam(2, $_POST['username'], PDO::PARAM_STR, 225);
			$runQuery->bindParam(3, $_POST['firstName'], PDO::PARAM_STR, 225);
			$runQuery->bindParam(4, $_POST['lastName'], PDO::PARAM_STR, 225);
			$runQuery->execute();
			die('<center><font color="red">User creation was successful.</font><br><a href="index.php">Click here</a> to login.</center>');
		}
	}
	else
	{
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
				<title>BookKeeping</title>
			</head>
			<body>
				<center>
					<form action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>" method="post">
						<br>
						<table>
							<tr>
								<td>First Name:</td><td><input type="text" name="firstName" maxlength="64"></td>
							</tr>
							<tr>
								<td>Last Name:</td><td><input type="text" name="lastName" maxlength="64"></td>
							</tr>
							<tr>
								<td>Email:</td><td><input type="text" name="username" maxlength="64"></td>
							</tr>
							<tr>
								<td>Password:</td><td><input type="password" name="password" maxlength="64"></td>
							</tr>
							<tr>
								<td>Confirm Password:</td><td><input type="password" name="passwordr" maxlength="64"></td>
							</tr>
						</table>
						<input type="submit" name="back" value="Back">
						<input type="submit" name="submit" value="Register">
					</form>
					<?php
						if(isset($_POST['submit']) && (!isset($_POST['username']) || $_POST['username']) == '')
						{
							echo '<font color="red">Please enter a Username</font>';
						}
						elseif(isset($_POST['submit']) && (!isset($_POST['password']) || $_POST['password']) == '')
						{
							echo '<font color="red">Please enter a Password</font>';
						}
						elseif(isset($_POST['submit']) && (!isset($_POST['passwordr']) || $_POST['passwordr']) == '')
						{
							echo '<font color="red">Please enter a password</font>';
						}
						elseif(isset($_POST['submit']) && (!isset($_POST['firstName']) || $_POST['firstName']) == '')
						{
							echo '<font color="red">Please enter a First Name</font>';
						}
						elseif(isset($_POST['submit']) && (!isset($_POST['lastName']) || $_POST['lastName']) == '')
						{
							echo '<font color="red">Please enter a Last Name</font>';
						}
						elseif(isset($_POST['submit']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['passwordr']) && $_POST['password'] !== $_POST['passwordr'] )
						{
							echo '<font color="red">Your passwords do not match</font>';
						}
						if(isset($nameNotMatch))
						{
							echo '<font color="red">The First Name/Last Name combo does not match our files. Please contact the commissioner.</font>';
						}
					?>
				</center>
			</body>
		</html>		
		<?php
	}
?>
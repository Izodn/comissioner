<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
	global $dbh;
	if(!globalOut('users') || globalOut('users') !== $authUser)
	{
		if(globalOut('user_key4') && globalOut('user_key4') == md5('Izodn'))
		{
		}
		else
		{
			die('You are not suppose to be here.');
		}
	}
	if(isset($_POST['userDetailSelect']))
	{
		$user_key4 = globalOut('users');
		$user_key5 = globalOut('user_key1');
		$user_key6 = globalOut('user_key2');
		$user_key7 = globalOut('user_key3');
		globalIn('user_key4', $user_key4);
		globalIn('user_key5', $user_key5);
		globalIn('user_key6', $user_key6);
		globalIn('user_key7', $user_key7);
		$query = "
			SELECT
				ua.CUSERNAME,
				ua.CPASSWORD,
				ua.CPASSWORD2,
				ua.IUSERID
			FROM
				user_account ua
			INNER JOIN
				user_detail ud ON (ud.IUSERID = ua.IUSERID)
			WHERE
				ud.CUSERNAMEDETAIL = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $_POST['userDetailSelect'], PDO::PARAM_STR, 225);
		$runQuery->execute();
		$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
		$users = safe($runQueryArray[0]);
		$user_key1 = safe($runQueryArray[1]);
		$user_key2 = safe($runQueryArray[2]);
		$user_key3 = safe($runQueryArray[3]);
		globalIn('users', $users);
		globalIn('user_key1', $user_key1);
		globalIn('user_key2', $user_key2);
		globalIn('user_key3', $user_key3);
		header('Location: adminIndex.php');
	}
	elseif(isset($_POST['clientDetailSelect']))
	{
		$user_key4 = globalOut('users');
		$user_key5 = globalOut('user_key1');
		$user_key6 = globalOut('user_key2');
		$user_key7 = globalOut('user_key3');
		globalIn('user_key4', $user_key4);
		globalIn('user_key5', $user_key5);
		globalIn('user_key6', $user_key6);
		globalIn('user_key7', $user_key7);
		$query = "
			SELECT
				ca.CUSERNAME,
				ca.CPASSWORD,
				ca.ICLIENTID
			FROM
				client_account ca
			INNER JOIN
				client_detail cd ON (ca.ICLIENTID = cd.ICLIENTID)
			WHERE
				cd.CUSERNAMEDETAIL = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $_POST['clientDetailSelect'], PDO::PARAM_STR, 225);
		$runQuery->execute();
		$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
		$users = safe($runQueryArray[0]);
		$user_key1 = safe($runQueryArray[1]);
		$user_key2 = safe($runQueryArray[2]);
		globalIn('users', $users);
		globalIn('user_key1', $user_key1);
		globalIn('user_key2', $user_key2);
		globalClear('user_key3');
		header('Location: index.php');
	}
	echo '<center>';
	if(globalOut('user_key4') && globalOut('user_key5') && globalOut('user_key6') && globalOut('user_key7') && globalOut('user_key3'))
	{
		echoLinks();
	}
	elseif(globalOut('user_key4') && globalOut('user_key5') && globalOut('user_key6') && globalOut('user_key2'))
	{
		echoClientLinks();
	}
	else
	{
		echoLinks();
	}
	echoAdminLinks();
	echo '<br>';
	if(globalOut('user_key4') && globalOut('user_key5') && globalOut('user_key6') && globalOut('user_key7'))
	{
	}
	else
	{
		echo 'Login as:';
		echo '<table border = "1">';
		echo '<tr>';
		$query = "
			SELECT
				ud.CUSERNAMEDETAIL
			FROM
				user_detail ud
			INNER JOIN
				user_account ua ON (ua.IUSERID = ud.IUSERID)
			WHERE
				ud.IUSERID != ? AND
				ua.IISACTIVE = 1
			ORDER BY
				Lower(ud.CUSERNAMEDETAIL)";
		$result = $dbh->prepare($query);
		$result->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
		$result->execute();
		$count=0;
		echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="post">';
		echo '<td>User</td>';
		echo '<td>';
		echo '<select name="userDetailSelect">';
		while( ($row = $result->fetch(PDO::FETCH_BOTH)))
		{
			$var = 'returnNumber'.$count;
			echo '<option value="'.safe($row[0]).'">'.safe($row[0]).'</option>';
			$count++;
		}
		echo '</select>';
		echo '</td>';
		echo '<td><input type="submit" name="submit" value="Login"></td>';
		echo '</form>';
		echo '</tr>';
		echo '<tr>';
		$query = "
			SELECT 
				cd.CUSERNAMEDETAIL 
			FROM 
				client_detail cd
			INNER JOIN
				client_account ca ON (ca.ICLIENTID = cd.ICLIENTID)
			WHERE 
				ca.CPASSWORD IS NOT NULL AND
				ca.IISACTIVE = 1
			ORDER BY 
				Lower(cd.CUSERNAMEDETAIL)";
		$result = $dbh->prepare($query);
		$result->execute();
		$count=0;
		echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="post">';
		echo '<td>Client</td>';
		echo '<td>';
		echo '<select name="clientDetailSelect">';
		while( ($row = $result->fetch(PDO::FETCH_BOTH)))
		{
			$var = 'returnNumber'.$count;
			echo '<option value="'.safe($row[0]).'">'.safe($row[0]).'</option>';
			$count++;
		}
		echo '</select>';
		echo '</td>';
		echo '<td><input type="submit" name="submit" value="Login"></td>';
		echo '</form>';
		echo '</tr>';
	}
	if(isset($_POST['LogOut']) && globalOut('user_key4') && globalOut('user_key5') && globalOut('user_key6') && globalOut('user_key7'))
	{
		globalIn('users', globalOut('user_key4'));
		globalIn('user_key1', globalOut('user_key5'));
		globalIn('user_key2', globalOut('user_key6'));
		globalIn('user_key3', globalOut('user_key7'));
		globalClear('user_key4');
		globalClear('user_key5');
		globalClear('user_key6');
		globalClear('user_key7');
		header('Location: userDetail.php');
	}
	elseif(!isset($_POST['LogOut']) && globalOut('user_key4') && globalOut('user_key5') && globalOut('user_key6') && globalOut('user_key7'))
	{
		if(globalOut('user_key3'))
		{
			$query = "
				SELECT
					ud.CUSERNAMEDETAIL
				FROM
					user_detail ud
				INNER JOIN
					user_account ua ON (ua.IUSERID = ud.IUSERID)
				WHERE
					ud.IUSERID = ?
			";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
			$runQuery->execute();
			$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
			$_POST['LogOut'] = safe($runQueryArray[0]);
			echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="post">';
			echo '<input type="hidden" name="LogOut" value="'.safe($runQueryArray[0]).'">';
			echo 'Log out of '.safe($runQueryArray[0]).' <input type="submit" name="LogOutAs" value="Submit">';
			echo '</form>';
		}
		else
		{
			$query = "
				SELECT
					cd.CUSERNAMEDETAIL
				FROM
					client_detail cd
				INNER JOIN
					client_account ca ON (ca.ICLIENTID = cd.ICLIENTID)
				WHERE
					cd.ICLIENTID = ?
			";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, globalOut('user_key2'), PDO::PARAM_STR, 225);
			$runQuery->execute();
			$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
			$_POST['LogOut'] = safe($runQueryArray[0]);
			echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="post">';
			echo '<input type="hidden" name="LogOut" value="'.safe($runQueryArray[0]).'">';
			echo 'Log out of '.safe($runQueryArray[0]).' <input type="submit" name="LogOutAs" value="Submit">';
			echo '</form>';
		}
	}
	echo '</center>';
?>


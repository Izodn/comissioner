<?php 
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';	
	if(!globalOut('users') || safe(globalOut('users')) !== $authUser)
	{
		if(globalOut('user_key4') && safe(globalOut('user_key4')) == md5($_SESSION['ENV']['SUPERUSER']))
		{
		}
		else
		{
			die('You are not suppose to be here.');
		}
	}
	if( isset($_POST['ban2']) ) //MANUAL BAN
	{
		if(!isset($_POST['IP2']) || safe($_POST['IP2']) == '')
		{
			die('Please enter the IP Address');
		}
		$query = "
			SELECT
				IIPADDRESS
			FROM
				ip_ban
			WHERE
				IIPADDRESS = ? AND
				IISBANNED = '0'
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $_POST['IP2'], PDO::PARAM_STR, 225);
		$runQuery->execute();
		$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
		if(safe($runQueryArray[0]) == '')
		{
			die('That IP doesn\'t exist and/or is already banned/unbanned');
		}
		$query = "
			UPDATE
				ip_ban
			SET
				IISBANNED = '1'
			WHERE
				IIPADDRESS = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $_POST['IP2'], PDO::PARAM_STR, 225);
		$runQuery->execute();
	}
	if( isset($_POST['unban2']) ) //MANUAL BAN
	{
		if(!isset($_POST['IP2']) || safe($_POST['IP2']) == '')
		{
			die('Please enter the IP Address');
		}
		$query = "
			SELECT
				IIPADDRESS
			FROM
				ip_ban
			WHERE
				IIPADDRESS = ? AND
				IISBANNED = '1'
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $_POST['IP2'], PDO::PARAM_STR, 225);
		$runQuery->execute();
		$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
		if(safe($runQueryArray[0]) == '')
		{
			die('That IP doesn\'t exist and/or is already banned/unbanned');
		}
		$query = "
			UPDATE
				ip_ban
			SET
				IISBANNED = '0'
			WHERE
				IIPADDRESS = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $_POST['IP2'], PDO::PARAM_STR, 225);
		$runQuery->execute();
	}
	if( isset($_POST['ban']) )
	{
		$query = "
			UPDATE
				ip_ban
			SET
				IISBANNED = '1'
			WHERE
				IIPADDRESS = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $_POST['IP'], PDO::PARAM_STR, 225);
		$runQuery->execute();
	}
	if( isset($_POST['unban']) )
	{
		$query = "
			UPDATE
				ip_ban
			SET
				IISBANNED = '0'
			WHERE
				IIPADDRESS = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $_POST['IP'], PDO::PARAM_STR, 225);
		$runQuery->execute();
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
				$ipQuery = "
					SELECT DISTINCT
						IIPADDRESS
					FROM
						ip_ban
				";
				$ipRunQuery = $dbh->prepare($ipQuery);
				$ipRunQuery->execute();
				$ipCount = 0;
				while($ipRunQueryArray = $ipRunQuery->fetch(PDO::FETCH_BOTH))
				{
					if(isset($ipRunQueryArray[0]) && safe($ipRunQueryArray[0]) != "")
					{
						$ipCount++;
					}
				}
				$results = (isset($_GET['resultsPerPage']) && $_GET['resultsPerPage'] / 2 != $_GET['resultsPerPage']) ? $_GET['resultsPerPage'] : 10;
				$limit = null;
				$page = (isset($_GET['page']) && $_GET['page'] / 2 != $_GET['page']) ? $_GET['page'] : 0;
				if($page == 0)
				{
					$limit = 'LIMIT 0, '.$results;
				}
				else
				{
					$tmp = $page * $results;
					$limit = 'LIMIT '.$tmp.', '.$results;
				}
				$query = "
					SELECT
						ib.IIPADDRESS,
						cd.CUSERNAMEDETAIL,
						ud.CUSERNAMEDETAIL,
						ib.ICREATEDTIME,
						ib.IISBANNED
					FROM
						ip_ban ib
					LEFT JOIN
						client_detail cd ON (cd.ICLIENTID = ib.ICLIENTID)
					LEFT JOIN
						user_detail ud ON (ud.IUSERID = ib.IUSERID)
					ORDER BY
						ib.ICREATEDTIME DESC
					".$limit."
				";
				$runQuery = $dbh->prepare($query);
				$runQuery->execute();
				echo 'Manual IP entry';
				echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="POST">';
				echo '<input type="textbox" name="IP2">';
				echo '<input type="submit" name="ban2" value="Hammer">';	
				echo '<input type="submit" name="unban2" value="Unhammer">';	
				echo '</form>';
				echo '<br>';
				echo 'Unique IPs: '.$ipCount;
				echo '<br>';
				echo tableNav('start', 'adminIPIndex', $results);
				echo '<table border="1">';
				echo '<tr>';
				echo '<td>IP Address</td><td>Client ID</td><td>User ID</td><td>Created Time</td><td>Ban</td><td>Un Ban</td>';
				echo '</tr>';
				while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
				{
					echo '<tr>';
					echo '<td>'.safe($runQueryArray[0]).'</td>';
					echo '<td>'.safe($runQueryArray[1]).'</td>';
					echo '<td>'.safe($runQueryArray[2]).'</td>';
					echo '<td>'.safe($runQueryArray[3]).'</td>';
					echo '<td>';
					if(safe($runQueryArray[4]) == '1')
					{
						
						echo '<font color="red">Banned</font>';
					}
					elseif(safe($runQueryArray[0]) == '')
					{
						echo '<font color="red">No IP</font>';
					}
					else
					{
						echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="POST">';
						echo '<input type="hidden" name="IP" value='.safe($runQueryArray['IIPADDRESS']).'>';
						echo '<input type="submit" name="ban" value="Hammer">';	
						echo '</form>';
					}
					echo '</td>';
					echo '<td>';
					if(safe($runQueryArray[4]) == '1')
					{
						
						echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="POST">';
						echo '<input type="hidden" name="IP" value='.safe($runQueryArray['IIPADDRESS']).'>';
						echo '<input type="submit" name="unban" value="Unhammer">';	
						echo '</form>';
					}
					elseif(safe($runQueryArray[0]) == '')
					{
						echo '<font color="red">No IP</font>';
					}
					else
					{
						echo '<font color="red">Not Banned</font>';
					}
					echo '</td>';
					echo '</tr>';
				}
				echo '</table>';
				echo tableNav('end', 'adminIPIndex', $results);
			?>
		</center>
	</body>
</html>
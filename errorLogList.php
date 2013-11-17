<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
	global $dbh;
	echo "<center>\n";
	echoLinks();
	echoAdminLinks();
	echo "<br>\n";
	$archivesLink = isset($_GET['archives']) ? $_SERVER['PHP_SELF'] : '?archives=1';
	echo '<a href="'.$archivesLink.'">Archives</a>';
	echo "</center>\n";
	$filter = isset($_GET['archives']) ? 'IS NOT NULL' : 'IS NULL';
	if(isset($_GET['logID']))
	{
		echo "<center><a href=\"errorLogList.php\">Back</a></center>\n";
		$query = "
			SELECT
				CLONGVALUE
			FROM
				errorLogs
			WHERE
				ILOGID = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, safe($_GET['logID']));
		$runQuery->execute();
		$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
		echo $runQueryArray[0];
	}
	elseif(isset($_POST['submit']) && isset($_POST['checkbox']))
	{
		$checkbox = $_POST['checkbox'];
		echo dump($_POST);
		$query = "
			UPDATE
				errorLogs
			SET
				IISARCHIVED = 1
			WHERE
				ILOGID IN (";
		$count = 0;
		foreach($checkbox as $check)
		{
			if($count === 0)
			{
				$query = $query."?";
			}
			else
			{
				$query = $query.", ?";
			}
			$count++;
		}
		$query = $query.")";
		$runQuery = $dbh->prepare($query);
		$count = 1;
		foreach($checkbox as $check => &$var)
		{
			$runQuery->bindParam($count, $var);
			$count++;
		}
		$runQuery->execute();
		header("Location: errorLogList.php");
	}
	else
	{
		echo "<center>\n";
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
				el.ICREATEDTIME,
				el.CSHORTVALUE,
				ud.CUSERNAMEDETAIL,
				cd.CUSERNAMEDETAIL,
				el.ILOGID,
				el.IISARCHIVED
			FROM
				errorLogs el
			LEFT JOIN
				user_detail ud ON (ud.IUSERID = el.IUSERID)
			LEFT JOIN
				client_detail cd ON (cd.ICLIENTID = el.ICLIENTID)
			WHERE
				el.IISARCHIVED ".$filter."
			ORDER BY
				el.ICREATEDTIME DESC
			".$limit."
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->execute();
		echo tableNav('start', 'adminErrorLogs', $results);
		echo "<form name=\"\" action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"POST\">\n";
		echo "<table border=1>\n";
		echo "<tr>\n";
		echo "<td>Input Time</td><td>Description</td><td>Admin</td><td>Client</td><td>View</td><td>Archive</td>\n";
		echo "</tr>\n";
		while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
		{
			echo "<tr>\n";
			echo "<td>".safe($runQueryArray[0])."</td>\n";
			echo "<td>".safe($runQueryArray[1])."</td>\n";
			echo "<td>".safe($runQueryArray[2])."</td>\n";
			echo "<td>".safe($runQueryArray[3])."</td>\n";
			echo "<td><a href=\"errorLogList.php?logID=".safe($runQueryArray[4])."\">Click Here</a></td>\n";
			echo "<td><input type=\"checkbox\" name=\"checkbox[]\" value=\"".safe($runQueryArray[4])."\"></td>\n";
			echo "</tr>\n";
		}
		echo "</table>\n";
		echo tableNav('end', 'adminErrorLogs', $results);
		echo "<br /><br /><input type=\"Submit\" name=\"submit\" value=\"Archive Selected\">";
		echo "</form>\n";
	}
	echo "</center>\n";
?>
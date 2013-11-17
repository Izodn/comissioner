<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
	global $dbh;
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
	if(isset($_POST['delete']))
	{
		$query = "
			UPDATE
				com_request
			SET
				CRESPONCE = 'ClientDeleted'
			WHERE
				IREQUESTID = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $_POST['requestID'], PDO::PARAM_STR, 225);
		$runQuery->execute();
	}
	if(isset($_POST['approve']))
	{
		$query = "
			UPDATE
				com_request
			SET
				IISAPPROVED = '1'
			WHERE
				IREQUESTID = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $_POST['requestID'], PDO::PARAM_STR, 225);
		$runQuery->execute();
	}
	echo '<center>';
	echoClientLinks();
	echo '<br>';
	if(isset($_GET['sort']))
	{
		if(isset($_GET['order']))
		{
			$order = 'Lower('.$_GET['sort'].') '.$_GET['order'].'';
		}
		else
		{
			$order = 'Lower('.$_GET['sort'].')';
		}
	}
	else
	{
		$order = 'cr.ICREATEDTIME DESC';
	}
	$query = "
		SELECT
			ud.CUSERNAMEDETAIL,
			cr.CREQUESTTITLE,
			cr.CDESCRIPTION,
			cr.CRESPONCESTATUS,
			cr.ICREATEDTIME,
			cr.IREQUESTID,
			cr.IREQUESTPRICE,
			cr.IISAPPROVED
		FROM
			com_request cr
		INNER JOIN
			user_detail ud ON (ud.IUSERID = cr.IUSERID)
		WHERE
			cr.ICLIENTID = ? AND
			cr.CRESPONCE != 'ClientDeleted'
		ORDER BY
			".$order."
		".$limit."
	";
	$runQuery = $dbh->prepare($query);
	$runQuery->bindParam(1, globalOut('user_key2'), PDO::PARAM_STR, 225);
	$runQuery->execute();
	echo tableNav('start', 'clientPendingComm', $results);
	echo '<table border=1>';
	echo '<tr>';
	echoPendingHeader();
	echo '<td>Responce Status</td><td>Price</td><td>Approve</td><td>Delete</td>';
	echo '</tr>';
	while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
	{
		echo '<tr>';
		echo '<td>'.safe($runQueryArray['CUSERNAMEDETAIL']).'</td>';
		echo '<td>'.safe($runQueryArray['CREQUESTTITLE']).'</td>';
		echo '<td>'.safe($runQueryArray['CDESCRIPTION']).'</td>';
		echo '<td>'.safe($runQueryArray['ICREATEDTIME']).'</td>';
		echo '<td>'.safe($runQueryArray['CRESPONCESTATUS']).'</td>';
		echo '<td>';
		if(safe($runQueryArray['IREQUESTPRICE']) == '')
		{
			echo 'None Set';
		}
		else
		{
			echo safe($runQueryArray['IREQUESTPRICE']);
		}
		echo '</td>';
		echo '<td>';
		echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="POST">';
		echo '<input type="hidden" name="requestID" value='.safe($runQueryArray['IREQUESTID']).'>';
		if(safe($runQueryArray['CRESPONCESTATUS']) != 'Declined' && safe($runQueryArray['IREQUESTPRICE']) != '' && safe($runQueryArray['IISAPPROVED']) != '1' )
		{
			echo '<input type="submit" name="approve" value="Approve">';
		}
		echo '</form>';
		echo '</td>';		
		echo '<td>';
		echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="POST">';
		echo '<input type="hidden" name="requestID" value='.safe($runQueryArray['IREQUESTID']).'>';
		if(safe($runQueryArray['CRESPONCESTATUS']) != 'Approved' || (safe($runQueryArray['CRESPONCESTATUS']) == 'Approved' && safe($runQueryArray['IISAPPROVED']) != '1'))
		{
			echo '<input type="submit" name="delete" value="Delete">';
		}
		echo '</form>';
		echo '</td>';
		echo '</tr>';
	}
	echo '</tr>';
	echo '</table>';
	echo tableNav('end', 'clientPendingComm', $results);
	echo '<br>';
	echo '<br>';
	echo '</center>';
?>
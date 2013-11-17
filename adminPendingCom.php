<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
	if(isset($_POST['submit']))
	{
		global $dbh;
		if(isset($_POST['Checkbox']))
		{
			foreach ($_POST['Checkbox'] as $check)
			{
				$var1 = $_POST[$check.'Price'];
				$var2 = $_POST[$check.'ResponceStatus'];
				if($var2 != 'Awaiting Responce')
				{
					if($var1 == '')
					{
						if($var2 != 'Declined')
						{
							$noPrice = '1';
							die('Please enter a price.');
						}
					}
				}
				else
				{
					die('Please set a Responce Status');
				}
			}
			if( isset($noPrice) && ($_POST[$check.'ResponceStatus'] == 'Approved' || $_POST[$check.'ResponceStatus'] == 'Awaiting Responce' ))
			{
			}
			else
			{
				foreach ($_POST['Checkbox'] as $check)
				{
					$var1 = $_POST[$check.'ResponceStatus'];
					if(isset($_POST[$check.'Price']))
					{
						$var2 = $_POST[$check.'Price'];	
						$query = "UPDATE com_request SET CRESPONCESTATUS= ?, IMODIFIEDTIME = NOW(), IREQUESTPRICE = ? WHERE IREQUESTID= ? AND IUSERID = ?";
						$runQuery = $dbh->prepare($query);
						$runQuery->bindParam(1, $var1, PDO::PARAM_STR, 225);
						$runQuery->bindParam(2, $var2, PDO::PARAM_STR, 225);
						$runQuery->bindParam(3, $check, PDO::PARAM_STR, 225);
						$runQuery->bindParam(4, globalOut('user_key3'), PDO::PARAM_STR, 225);
						$runQuery->execute();
					}
					else
					{
						$query = "UPDATE com_request SET CRESPONCESTATUS= ?, IMODIFIEDTIME = NOW() WHERE IREQUESTID= ? AND IUSERID = ?";
						$runQuery = $dbh->prepare($query);
						$runQuery->bindParam(1, $var1, PDO::PARAM_STR, 225);
						$runQuery->bindParam(2, $check, PDO::PARAM_STR, 225);
						$runQuery->bindParam(3, globalOut('user_key3'), PDO::PARAM_STR, 225);
						$runQuery->execute();
					}
					redirectHandler();
				}
			}
		}
		else
		{
			$noCheckbox='1';
		}
	}
	if(isset($_POST['commission']))
	{
		global $dbh;
		$query3 = "
			SELECT
				cd.CUSERNAMEDETAIL
			FROM
				client_detail cd
			INNER JOIN
				com_request cr ON (cr.ICLIENTID = cd.ICLIENTID)
			WHERE
				cr.IREQUESTID = ?
		";
		$runQuery3 = $dbh->prepare($query3);
		$runQuery3->bindParam(1, $_POST['requestID'], PDO::PARAM_STR, 225);
		$runQuery3->execute();
		$runQueryArray3 = $runQuery3->fetch(PDO::FETCH_BOTH);
		header('Location: adminIndex.php?autoFill='.safe($runQueryArray3['CUSERNAMEDETAIL']).'&requestID='.$_POST['requestID'].'');
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
	echo '<center>';
	echoLinks();
	echoCommissionLinks();
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
			cd.CUSERNAMEDETAIL,
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
			client_detail cd ON (cd.ICLIENTID = cr.ICLIENTID)
		WHERE
			cr.IUSERID = ? AND
			cr.CRESPONCE != 'Deleted' AND
			cr.CRESPONCE != 'ClientDeleted' AND
			cr.CRESPONCESTATUS != 'Declined'
		ORDER BY
			".$order."
		".$limit."
	";
	$runQuery = $dbh->prepare($query);
	$runQuery->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
	$runQuery->execute();
	$results = (isset($_GET['resultsPerPage']) && $_GET['resultsPerPage'] / 2 != $_GET['resultsPerPage']) ? $_GET['resultsPerPage'] : 10;
	echo tableNav('start', 'adminPendingComm', $results);
	echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="POST">';
	echo '<table border=1>';
	echo '<tr>';
	echoAdminPendingHeader();
	echo '<td>Responce Status</td><td>Authorize Price</td><td>Update</td><td>Input</td>';
	echo '</tr>';
	while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
	{
		$selected1 = '';
		$selected2 = '';
		$selected3 = '';
		//if(safe($runQueryArray['CRESPONCESTATUS']) == 'Awaiting responce'){ $selected1 = 'selected'; }
		if(safe($runQueryArray['CRESPONCESTATUS']) == 'Approved'){ $selected2 = 'selected'; }
		if(safe($runQueryArray['CRESPONCESTATUS']) == 'Declined'){ $selected3 = 'selected'; }
		echo '<tr>';
		echo '<td><a href="search.php?FirstName=&LastName=&OrderNumber=&Email='.safe($runQueryArray['CUSERNAMEDETAIL']).'&search=Search">'.safe($runQueryArray['CUSERNAMEDETAIL']).'</a></td>';
		echo '<td>'.safe($runQueryArray['CREQUESTTITLE']).'</td>';
		echo '<td>'.safe($runQueryArray['CDESCRIPTION']).'</td>';
		echo '<td>'.safe($runQueryArray['ICREATEDTIME']).'</td>';
		echo '<td>';
		echo '<select name="'.safe($runQueryArray["IREQUESTID"]).'ResponceStatus">';
		//echo '<option value="Awaiting Responce" '.$selected1.'>Awaiting Responce</option>';
		echo '<option value="Approved" '.$selected2.'>Approved</option>';
		echo '<option value="Declined" '.$selected3.'>Declined</option>';
		echo '</select>';
		echo '</td>';
		echo '<td><input type="text" name="'.safe($runQueryArray["IREQUESTID"]).'Price" size="16" maxlength="12" value="'.safe($runQueryArray['IREQUESTPRICE']).'"></td>';
		echo '<td><input type="checkbox" name="Checkbox[]" value='.safe($runQueryArray["IREQUESTID"]).'></td>';
		echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="POST">';
		echo '<input type="hidden" name="requestID" value='.safe($runQueryArray['IREQUESTID']).'>';
		echo '<td>';
		if(safe($runQueryArray['IISAPPROVED']) == '1')
		{
			echo '<input type="submit" name="commission" value="Input Commission">';
		}
		elseif(safe($runQueryArray['CRESPONCESTATUS']) == 'Approved')
		{
			echo '<font color="red">Awaiting client responce</font>';
		}
		elseif(safe($runQueryArray['CRESPONCESTATUS']) == 'Awaiting responce')
		{
			echo '<font color="red">Awaiting your action</font>';
		}		
		echo '</td>';
		echo '</form>';
		echo '</tr>';
	}
	echo '</tr>';
	echo '</table>';
	echo tableNav('end', 'adminPendingComm', $results);
	echo '<br>';
	echo '<br>';
	echo'<input type="submit" name="submit" value="Update Selected">';
	echo '</form>';
	if(isset($noCheckbox))
	{
		echo '<br><font color="red">You didn\'t check a Checkbox.</font>';
	}
	if(isset($noPrice))
	{
		echo '<br><font color="red">You didn\'t enter a price.</font>';
	}
	if(successMsg())
	{
		echo '<br><font color="red">Success</font>';
	}
	echo '</center>';
?>
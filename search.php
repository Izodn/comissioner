<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
	if(!empty($_GET['Checkbox']) && (isset($_GET['submit']) || isset($_GET['delete'])) && isset($_GET['URL']))
	{
		global $dbh;
		if(isset($_GET['submit']))
		{
			foreach ($_GET['Checkbox'] as $check)
			{
				$var1 = $_GET[$check.'ProgressType'];
				$var2 = $_GET[$check.'PaymentStatus'];
				$query = $dbh->prepare("UPDATE book_records SET CPROGRESSTYPE= ? WHERE IRECORDNUMBER= ? AND IUSERID = ?");
				$query->bindParam(1, $var1, PDO::PARAM_STR, 225);
				$query->bindParam(2, $check, PDO::PARAM_STR, 225);
				$query->bindParam(3, globalOut('user_key3'), PDO::PARAM_STR, 225);
				$query->execute();
				$query = $dbh->prepare("UPDATE book_records SET CPAYMENTSTATUS= ? WHERE IRECORDNUMBER= ? AND IUSERID = ?");
				$query->bindParam(1, $var2, PDO::PARAM_STR, 225);
				$query->bindParam(2, $check, PDO::PARAM_STR, 225);
				$query->bindParam(3, globalOut('user_key3'), PDO::PARAM_STR, 225);
				$query->execute();
				$query = $dbh->prepare("UPDATE book_records SET IMODIFIEDTIME = NOW() WHERE IRECORDNUMBER= ? AND IUSERID = ?");
				$query->bindParam(1, $check, PDO::PARAM_STR, 225);
				$query->bindParam(2, globalOut('user_key3'), PDO::PARAM_STR, 225);
				$query->execute();
			}
		}
		elseif(isset($_GET['delete']) && isset($_GET['Checkbox2']))
		{
			foreach ($_GET['Checkbox2'] as $check)
			{
				$query = $dbh->prepare("UPDATE book_records SET CISDELETED='Checked' WHERE IRECORDNUMBER= ? AND IUSERID = ?");
				$query->bindParam(1, $check, PDO::PARAM_STR, 225);
				$query->bindParam(2, globalOut('user_key3'), PDO::PARAM_STR, 225);
				$query->execute();
				$query = $dbh->prepare("UPDATE book_records SET IMODIFIEDTIME = NOW() WHERE IRECORDNUMBER= ? AND IUSERID = ?");
				$query->bindParam(1, $check, PDO::PARAM_STR, 225);
				$query->bindParam(2, globalOut('user_key3'), PDO::PARAM_STR, 225);
				$query->execute();
			}
		}
		elseif(!isset($_GET['Checkbox2']))
		{
			die("You didn't check a checkbox");
		}
		if(isset($_GET['URL']))
		{
			header('Location:'.$_GET['URL']);
		}
	}
	elseif(!isset($_GET['Checkbox']) && (isset($_GET['submit']) || isset($_GET['delete'])))
	{
		die("You didn't check a checkbox");
	}
	elseif(!isset($_GET['URL']) && ( isset($_GET['submit'] ) || isset($_GET['delete']))) 
	{
		die('No URL');
	}
	echo '<center>';
	echoLinks();
	echoCommissionLinks();
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
	echo '<br>';
	if(isset($_GET['LastName']) || isset($_GET['FirstName']) || isset($_GET['Email']))
	{
		echo tableNav('end', 'adminSearch', $results);
		echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="GET">';
		$pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
		if ($_SERVER["SERVER_PORT"] != "80")
		{
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} 
		else 
		{
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		echo '<input type="hidden" name="URL" value="'.$pageURL.'">';
		if( (isset($_GET['FirstName']) && $_GET['FirstName'] == '') && (isset($_GET['LastName']) && $_GET['LastName'] == '') && (isset($_GET['Email']) && $_GET['Email'] == '') )
		{
			die('You\'re missing a field to search by');
		}
		$queryType = 'SELECT';
		$fieldArray = array("br.CTITLE,", "br.IORDERNUMBER,", "br.CFIRSTNAME,", "br.CLASTNAME,", "br.DDESCRIPTION,", "br.IPRICE,",  "br.CACCOUNTTYPE,", "br.CIMPUTTIME,", "br.CPROGRESSTYPE,", "br.CPAYMENTSTATUS,", "br.IRECORDNUMBER,", "br.CISDELETED,", "cd.CUSERNAMEDETAIL");
		$queryWhere = 'FROM';
		$fieldWhere = 'book_records br';
		$joinType = 'INNER JOIN';
		$joinWhere = 'client_detail cd ON (cd.ICLIENTID = br.ICLIENTID)';
		$whereClause = 'WHERE';
		$a = array('', '', '', '');
		if(isset($_GET['FirstName']) && $_GET['FirstName'] != ''){$a[0] = 'br.CFIRSTNAME = ?';}
		if(isset($_GET['LastName']) && $_GET['LastName'] != ''){$a[1] = 'br.CLASTNAME = ?';}
		if(isset($_GET['Email']) && $_GET['Email'] != ''){$a[2] = 'cd.CUSERNAMEDETAIL = ?';}
		$a[3] = "br.CISDELETED != 'Checked'";
		$orderClause = '';
		if(isset($_GET['sort'])&&$_GET['sort']!=''){$orderClause = 'ORDER BY'.$_GET['sort'];}
		if((isset($_GET['sort'])&&$_GET['sort']!='') &&(isset($_GET['order'])&&$_GET['order']!='')){$orderClause = 'ORDER BY '.$_GET['sort'].' '.$_GET['order'];}
		$orderClause = $orderClause.' '.$limit;
		global $queryType;
		global $fieldArray;
		global $queryWhere;
		global $fieldWhere;
		global $joinType;
		global $joinWhere;
		global $whereClause;
		global $a;
		global $orderClause;
		echoSearch();
	}
	else
	{
		echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="GET">';
		echo '<table>';
		echo '<tr>';
		echo '<td>First Name:</td><td><input type="text" name="FirstName" maxlength="64"></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Last Name:</td><td><input type="text" name="LastName" maxlength="64"></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Email:</td><td><input type="text" name="Email" maxlength="225"></td>';
		echo '</tr>';
		echo '</table>';
		echo'<input type="submit" name="search" value="Search">';
	}
	echo '</form>';
	echo '</center>';
?>
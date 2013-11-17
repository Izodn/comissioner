<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
	global $dbh;
	if(isset($_POST['Checkbox']))
	{
		if(isset($_POST['submit']))
		{
			foreach ($_POST['Checkbox'] as $check)
			{
				$var1 = $_POST[$check.'ProgressType'];
				$var2 = $_POST[$check.'PaymentStatus'];
				if (isset($_POST['submit'])) 
				{	
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
					$_POST['success'] = '1';
				}
				else
				{
				}
			}
		}
	}
	if(isset($_POST['Checkbox2']))
	{
		if(isset($_POST['delete']))
		{
			foreach ($_POST['Checkbox2'] as $check)
			{
				$query = $dbh->prepare("UPDATE book_records SET CISDELETED='Checked' WHERE IRECORDNUMBER= ? AND IUSERID = ?");
				$query->bindParam(1, $check, PDO::PARAM_STR, 225);
				$query->bindParam(2, globalOut('user_key3'), PDO::PARAM_STR, 225);
				$query->execute();
				$query = $dbh->prepare("UPDATE book_records SET IMODIFIEDTIME = NOW() WHERE IRECORDNUMBER= ? AND IUSERID = ?");
				$query->bindParam(1, $check, PDO::PARAM_STR, 225);
				$query->bindParam(2, globalOut('user_key3'), PDO::PARAM_STR, 225);
				$query->execute();
				$_POST['success'] = '1';
			}
		}
	}
	echo '<center>';
	echoLinks();
	echoCommissionLinks();
	echo '<br>';
	$results = (isset($_GET['resultsPerPage']) && $_GET['resultsPerPage'] / 2 != $_GET['resultsPerPage']) ? $_GET['resultsPerPage'] : 10;
	echo tableNav('start', 'adminProgress', $results);
	echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="POST">';
	echoTable();
	echo '</form>';
	if(isset($_POST['submit']))
	{
		if(isset($_POST['Checkbox']) && isset($_POST['success']))
		{
			echo '<font color="red">Success</font>';
		}
		else
		{
			echo '<font color="red">You did not check a box!</font>';
		}
	}
	elseif(isset($_POST['delete']))
	{
		if(isset($_POST['Checkbox2']) && isset($_POST['success']))
		{
			echo '<font color="red">Success</font>';
		}
		else
		{
			echo '<font color="red">You did not check a box!</font>';
		}
	}

	echo '</center>';
?>
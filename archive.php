<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
	global $dbh;	
	if(isset($_POST['submit']))
	{	
		if(!empty($_POST['Checkbox']))
		{
			foreach ($_POST['Checkbox'] as $check)
			{
				if (isset($_POST['submit'])) 
				{
					$query = "UPDATE book_records SET CISDELETED='' WHERE IRECORDNUMBER= ? AND IUSERID = ?";
					$runQuery = $dbh->prepare($query);
					$runQuery->bindParam(1, $check, PDO::PARAM_STR, 225);
					$runQuery->bindParam(2, globalOut('user_key3'), PDO::PARAM_STR, 225);
					$runQuery->execute();
				}
				else
				{
				}
			}
		}
	}
	$query = 'SELECT * FROM book_records WHERE IUSERID = ?';
    $result = $dbh->prepare($query);
	$result->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
	$result->execute();
	echo '<center>';
	echoLinks();
	echoCommissionLinks();
	echo '<br>';
	$results = (isset($_GET['resultsPerPage']) && $_GET['resultsPerPage'] / 2 != $_GET['resultsPerPage']) ? $_GET['resultsPerPage'] : 10;
	echo tableNav('start', 'adminTrash', $results);
	echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="POST">';
	echoTrash();
	echo '</form>';
	echo '</center>';
?>
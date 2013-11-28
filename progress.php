<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/user.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/links.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/requireLogin.php'; //Checks login and starts session
	requireLogin();
	if($_SESSION['userObj']->getUserType() === 'commissioner' || $_SESSION['userObj']->getUserType() === 'superuser') {
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Commissioner - Progress</title>
	</head>
	<body>
		<center>
			<?php
				$links = new links($_SESSION['userObj']);
				echo $links->getLinks();
				$query = <<<SQL
SELECT
	cc.ICOMMISSIONID commissionId,
	cc.CTITLE title,
	cu.CFIRSTNAME firstName,
	cu.CLASTNAME lastName,
	cc.CDESCRIPTION description,
	cc.ICOST cost,
	ca.CNAME paymentMethod,
	cc.DCREATEDDATE createdDate,
	cpr.CNAME progressStatus,
	cpa.CNAME paymentStatus
FROM
	COM_COMMISSION cc
INNER JOIN
	COM_USER cu ON cu.IUSERID = cc.ICLIENTID
INNER JOIN
	COM_ACCOUNT ca ON ca.IACCOUNTID = cc.IACCOUNTID
INNER JOIN
	COM_PROGRESSSTATUS cpr ON cpr.ISTATUSID = cc.IPROGRESSSTATUSID
INNER JOIN
	COM_PAYMENTSTATUS cpa ON cpa.ISTATUSID = cc.IPAYMENTSTATUSID
WHERE
	cc.ICOMMISSIONERID = ? AND
	cc.IISARCHIVED = ?
ORDER BY
	cc.ICOMMISSIONID DESC
SQL;
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $_SESSION['userObj']->getUserId());
				$runQuery->bindParam(2, $tmp = '0');
				$runQuery->execute();
				
			?>
			<table border="1">
				<tr>
					<?php
						$table = '';
						$headerArray = array('Title','First Name','Last Name','Description','Price','Payment method','Created Time','Progress','Payment','Archive');
						$headerLen = count($headerArray);
						$table .= '<tr>';
						for($a=0;$a<$headerLen;$a++) {
							$table .= '<td>'.$headerArray[$a].'</td>';
						}
						$table .= '</tr>';
						while($row = $runQuery->fetch(PDO::FETCH_BOTH)) {
							$table .= '<tr>';
							$table .= '<td>'.$row['title'].'</td>';
							$table .= '<td>'.$row['firstName'].'</td>';
							$table .= '<td>'.$row['lastName'].'</td>';
							$table .= '<td>'.$row['description'].'</td>';
							$table .= '<td>'.$row['cost'].'</td>';
							$table .= '<td>'.$row['paymentMethod'].'</td>';
							$table .= '<td>'.$row['createdDate'].'</td>';
							$table .= '<td>'.$row['progressStatus'].'</td>';
							$table .= '<td>'.$row['paymentStatus'].'</td>';
							$table .= '<td><input type="checkbox" name="archive[]" value="'.$row['commissionId'].'"></td>';
							$table .= '<tr>';
						}
						echo $table;
					?>
				</tr>
			</table>
		</center>
	</body>
</html>
<?php
	}
?>
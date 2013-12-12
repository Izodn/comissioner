<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/user.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/links.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/requireLogin.php'; //Checks login and starts session
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/toMoney.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/genSelect.php';
	requireLogin();
	if($_SESSION['userObj']->getUserType() === 'commissioner' || $_SESSION['userObj']->getUserType() === 'superuser') {
		global $dbh;
		if(!empty($_POST)) {
			if( !empty($_POST['progress']) && isset($_POST['id']) ) {
				$query = <<<SQL
UPDATE
	COM_COMMISSION
SET
	IPROGRESSSTATUSID = (
		SELECT
			ISTATUSID
		FROM
			COM_PROGRESSSTATUS
		WHERE
			CNAME = ? AND
			IUSERID IN (0, ?)
		LIMIT
			0, 1
	)
WHERE
	ICOMMISSIONID = ? AND
	ICOMMISSIONERID = ?
SQL;
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $_POST['progress']);
				$runQuery->bindParam(2, $_SESSION['userObj']->getUserId());
				$runQuery->bindParam(3, $_POST['id']);
				$runQuery->bindParam(4, $_SESSION['userObj']->getUserId());
				$runQuery->execute();
			}
			if( isset($_POST['payment']) && isset($_POST['id']) ) {
				$query = <<<SQL
UPDATE
	COM_COMMISSION
SET
	IPAYMENTSTATUSID = (
		SELECT
			ISTATUSID
		FROM
			COM_PAYMENTSTATUS
		WHERE
			CNAME = ? AND
			IUSERID IN (0, ?)
		LIMIT
			0, 1
	)
WHERE
	ICOMMISSIONID = ? AND
	ICOMMISSIONERID = ?
SQL;
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $_POST['payment']);
				$runQuery->bindParam(2, $_SESSION['userObj']->getUserId());
				$runQuery->bindParam(3, $_POST['id']);
				$runQuery->bindParam(4, $_SESSION['userObj']->getUserId());
				$runQuery->execute();
			}
			if( isset($_POST['archive']) && isset($_POST['id']) ) {
				$query = <<<SQL
UPDATE
	COM_COMMISSION
SET
	IISARCHIVED = ?
WHERE
	ICOMMISSIONID = ? AND
	ICOMMISSIONERID = ?
SQL;
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $tmp = isset($_GET['archives']) ? 0 : 1);
				$runQuery->bindParam(2, $_POST['id']);
				$runQuery->bindParam(3, $_SESSION['userObj']->getUserId());
				$runQuery->execute();
			}
			$query = <<<SQL
UPDATE
	COM_COMMISSION
SET
	DMODIFIEDDATE = NOW()
WHERE
	ICOMMISSIONID = ? AND
	ICOMMISSIONERID = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $_POST['id']);
			$runQuery->bindParam(2, $_SESSION['userObj']->getUserId());
			$runQuery->execute();
		}
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
	CNAME
FROM
	COM_PROGRESSSTATUS
WHERE
	IUSERID IN (0, ?)
SQL;
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $_SESSION['userObj']->getUserId());
				$runQuery->execute();
				$i=0;
				while($progress = $runQuery->fetch(PDO::FETCH_ASSOC)) {
					$progresses[$i] = $progress['CNAME'];
					$i++;
				}
				$query = <<<SQL
SELECT
	CNAME
FROM
	COM_PAYMENTSTATUS
WHERE
	IUSERID IN (0, ?)
SQL;
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $_SESSION['userObj']->getUserId());
				$runQuery->execute();
				$i=0;
				while($payment = $runQuery->fetch(PDO::FETCH_ASSOC)) {
					$payments[$i] = $payment['CNAME'];
					$i++;
				}
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
				$runQuery->bindParam(2, $tmp = isset($_GET['archives']) ? '1' : '0');
				$runQuery->execute();
				if( isset($_GET['archives']) )
					echo '<button onClick="window.location.href = \'progress.php\'">Close Archive</button>';
				else
					echo '<button onClick="window.location.href = \'progress.php?archives=\'">Open Archive</button>';
			?>
			<table border="1">
				<?php
					$table = '';
					$headerArray = array('Title','Client Name','Description','Price','Payment method','Created Time','Progress','Payment','Archive');
					$headerLen = count($headerArray);
					$table .= '<tr>';
					for($a=0;$a<$headerLen;$a++) {
						$table .= '<td>'.$headerArray[$a].'</td>';
					}
					$table .= '</tr>';
					while($row = $runQuery->fetch(PDO::FETCH_BOTH)) {
						$table .= '<tr>';
						$table .= '<form action="'.htmlentities($_SERVER['REQUEST_URI']).'", method="POST">';
						$table .= '<input type="hidden" name="id" value="'.$row['commissionId'].'">';
						$table .= '<td><a href="commission.php?id='.$row['commissionId'].'">'.$row['title'].'</a></td>';
						$table .= '<td>'.$row['firstName'].' '.$row['lastName'].'</td>';
						$table .= '<td>'.$row['description'].'</td>';
						$table .= '<td>'.toMoney($row['cost']).'</td>';
						$table .= '<td>'.$row['paymentMethod'].'</td>';
						$table .= '<td>'.$row['createdDate'].'</td>';
						$table .= '<td>'.genSelect($progresses, $row['progressStatus'], 'progress', null, 'onChange="form.submit()"').'</td>';
						$table .= '<td>'.genSelect($payments, $row['paymentStatus'], 'payment', null, 'onChange="form.submit()"').'</td>';
						$table .= '<td><input type="submit" name="archive" value="'.($_value=isset($_GET['archives'])?'Unarchive':'Archive').'"></button></td>';
						$table .= '</form>';
						$table .= '</tr>';
					}
					echo $table;
				?>
			</table>
		</center>
	</body>
</html>
<?php
	}
?>
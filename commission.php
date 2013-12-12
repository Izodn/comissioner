<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/user.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/links.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/requireLogin.php'; //Checks login and starts session
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/toMoney.php';
	requireLogin();
	global $dbh;
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Commission - Commission</title>
	</head>
	<body>
		<center>
			<?php
				$links = new links($_SESSION['userObj']);
				echo $links->getLinks();
				$query = <<<SQL
SELECT
	cc.CTITLE title,
	cc.CDESCRIPTION description,
	concat(cu.CFIRSTNAME,' ',cu.CLASTNAME) clientName,
	cc.ICOST price,
	ca.CNAME paymentMethod,
	cc.DCREATEDDATE createdDate,
	cp1.CNAME progress,
	cp2.CNAME payment
FROM
	COM_COMMISSION cc
INNER JOIN
	COM_USER cu ON cu.IUSERID = cc.ICLIENTID
INNER JOIN
	COM_ACCOUNT ca ON ca.IACCOUNTID = cc.IACCOUNTID
INNER JOIN
	COM_PROGRESSSTATUS cp1 ON cp1.ISTATUSID = cc.IPROGRESSSTATUSID
INNER JOIN
	COM_PAYMENTSTATUS cp2 ON cp2.ISTATUSID = cc.IPAYMENTSTATUSID
WHERE
	cc.ICOMMISSIONID = ? AND
	(
		cc.ICOMMISSIONERID = ? OR
		cc.ICLIENTID = ?
	)
LIMIT
	0, 1
SQL;
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $_GET['id']);
				$runQuery->bindParam(2, $_SESSION['userObj']->getUserId());
				$runQuery->bindParam(3, $_SESSION['userObj']->getUserId());
				$runQuery->execute();
				$result = $runQuery->fetch(PDO::FETCH_ASSOC);
			?>
			<table border="1">
				<tr>
					<td>Title</td>
					<td><?php echo $result['title'];?></td>
				</tr>
				<tr>
					<td>Description</td>
					<td><?php echo $result['description'];?></td>
				</tr>
				<tr>
					<td>Client Name</td>
					<td><?php echo $result['clientName'];?></td>
				</tr>
				<tr>
					<td>Price</td>
					<td><?php echo toMoney($result['price']);?></td>
				</tr>
				<tr>
					<td>Payment Method</td>
					<td><?php echo $result['paymentMethod'];?></td>
				</tr>
				<tr>
					<td>Created Date</td>
					<td><?php echo $result['createdDate'];?></td>
				</tr>
				<tr>
					<td>Progress Status</td>
					<td><?php echo $result['progress'];?></td>
				</tr>
				<tr>
					<td>Payment Status</td>
					<td><?php echo $result['payment'];?></td>
				</tr>
			</table>
		</center>
	</body>
</html>

















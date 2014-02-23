<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/user.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/links.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/table.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/requireLogin.php'; //Checks login and starts session
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/money.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/strToInput.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/strToSelect.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/dump.php';
	requireLogin();
	if($_SESSION['userObj']->getUserType() === 'superuser' || $_SESSION['userObj']->getType() === 'commissioner') {
		if( !empty($_POST) ) {
			echo dump($_POST);
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
				global $dbh;
				$links = new links($_SESSION['userObj']);
				echo $links->getLinks();
				$headers = array('Commission ID', 'Title', 'Client Name', 'Description', 'Price', 'Input Time', 'Progress', 'Payment', 'Archive');
				$progressSelOptions = array();
				$query = <<<SQL
SELECT
	cName
FROM
	COM_PROGRESSSTATUS
WHERE
	IUSERID = ? OR
	IUSERID = 0
SQL;
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $_SESSION['userObj']->getUserId());
				$runQuery->execute();
				while($row = $runQuery->fetch(PDO::FETCH_ASSOC)) {
					$progressSelOptions[count($progressSelOptions)] = $row['cName'];
				}
				$paymentSelOptions = array();
				$query = <<<SQL
SELECT
	cName
FROM
	COM_PAYMENTSTATUS
WHERE
	IUSERID = ? OR
	IUSERID = 0
SQL;
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $_SESSION['userObj']->getUserId());
				$runQuery->execute();
				while($row = $runQuery->fetch(PDO::FETCH_ASSOC)) {
					$paymentSelOptions[count($paymentSelOptions)] = $row['cName'];
				}
				$query = <<<SQL
SELECT
	cc.iCommissionId,
	cc.cTitle as title,
	concat(cu.cFirstName,' ',cu.cLastName) as clientName,
	cc.cDescription as description,
	cc.iCost as price,
	cc.dCreatedDate as inputTime,
	cpr.cName as progressStatus,
	cpa.cName as paymentStatus,
	cc.iCommissionId as commissionId
FROM
	COM_COMMISSION cc
INNER JOIN
	COM_USER cu ON cu.iUserId = cc.iClientId
INNER JOIN
	COM_PROGRESSSTATUS cpr ON cpr.iStatusId = cc.iProgressStatusId
INNER JOIN
	COM_PAYMENTSTATUS cpa ON cpa.iStatusId = cc.iPaymentStatusId
WHERE
	cc.iCommissionerId = ? AND
	cc.iIsArchived = '0'
SQL;
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $_SESSION['userObj']->getUserId());
				$runQuery->execute();
				$result = $runQuery->fetchall(PDO::FETCH_NUM);
				$table = new table($headers, $result);
				$table->setAttr('border',1);
				$table->changeData('all', 'Commission ID', 'strToInput', array('hidden', 'commissionId[]'));
				$table->changeData('all', 'Price', 'moneyToStr');
				$table->changeData('all', 'Archive', 'strToInput', array('checkbox', 'archive[]'));
				$table->changeData('all', 'Progress', 'strToSelect', array('progress[]', $progressSelOptions, 'onChange="form.submit()"'));
				$table->changeData('all', 'Payment', 'strToSelect', array('payment[]', $paymentSelOptions, 'onChange="form.submit()"'));
				$table->hideColumn('Commission ID', /*HideHeader*/true, /*HideData*/false, /*ExcludeTh*/true, /*ExcludeTd*/true);
				echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="POST">';
				echo $table->getTable();
				echo '<br /><input type="submit" name="archive" value="Archive Selected">';
				echo '</form>';
			?>
		</center>
	</body>
</html>
<?php
	}
	elseif($_SESSION['userObj']->getUserType() === 'client') {
		echo 'hello there';
	}
?>
<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	define('COMMISSION_NOT_EXIST', 'That commission doesn\'t exist.');
	define('PAYMENT_STATUS_NOT_EXIST', 'Tried using payment status that doesn\'t exist.');
	define('PROGRESS_STATUS_NOT_EXIST', 'Tried using progress status that doesn\'t exist.');
	class commission {
		var $commissionId;
		var $userId;
		var $progressStatusId;
		var $paymentStatusId;
		var $error;
		function __construct($id) {
			global $dbh;
			$this->commissionId = $id;
			$this->userId = $_SESSION['userObj']->getUserId();
			$query = <<<SQL
SELECT
	count(iCommissionId) as commissionCount,
	iProgressStatusId as progressStatusId,
	iPaymentStatusId as paymentStatusId
FROM
	COM_COMMISSION
WHERE
	ICOMMISSIONID = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $id);
			$runQuery->execute();
			$result = $runQuery->fetch(PDO::FETCH_ASSOC);
			if($result['commissionCount'] === '0') {
				$this->error = COMMISSION_NOT_EXIST;
			}
			else {
				$this->progressStatusId = $result['progressStatusId'];
				$this->paymentStatusId = $result['paymentStatusId'];
			}
		}
		function updatePaymentStatus($statusName) {
			global $dbh;
			$query = <<<SQL
SELECT
	count(iStatusId) as rowCount,
	iStatusId as statusId
FROM
	COM_PAYMENTSTATUS
WHERE
	CNAME = ? AND
	(
		IUSERID = 0 OR
		IUSERID = ?
	)
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $statusName);
			$runQuery->bindParam(2, $this->userId);
			$runQuery->execute();
			$result = $runQuery->fetch(PDO::FETCH_ASSOC);
			if( $result['rowCount'] !== '0' ) { //Should only get 1 anyway, they're going to be unique
				$desiredId = $result['statusId'];
				if($desiredId !== $this->paymentStatusId) {
					//Change in status, do that here
					$query = <<<SQL
UPDATE
	COM_COMMISSION
SET
	IPAYMENTSTATUSID = ?,
	DMODIFIEDDATE = NOW()
WHERE
	ICOMMISSIONID = ?
SQL;
					$runQuery = $dbh->prepare($query);
					$runQuery->bindParam(1, $desiredId);
					$runQuery->bindParam(2, $this->commissionId);
					$runQuery->execute();
				}
				else {
					//The desired status is the same that's already set
					if($this->commissionId === 2) {
						die('Got here');
					}
				}
			}
			else {
				$this->error = PAYMENT_STATUS_NOT_EXIST;
				die('Error');
			}
		}
		function updateProgressStatus($statusName) {
			
		}
	}
?>
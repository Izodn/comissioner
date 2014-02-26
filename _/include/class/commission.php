<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	define('COMMISSION_NOT_EXIST', 'That commission doesn\'t exist.');
	define('PAYMENT_STATUS_NOT_EXIST', 'Tried using payment status that doesn\'t exist.');
	define('PROGRESS_STATUS_NOT_EXIST', 'Tried using progress status that doesn\'t exist.');
	class commission {
		var $commissionId;
		var $exists = false; //Always true or false
		var $userId;
		var $progressStatusId;
		var $paymentStatusId;
		var $isArchived;
		var $title;
		var $description;
		var $clientName;
		var $cost;
		var $paymentOption;
		var $inputTime;
		var $progressStatus;
		var $paymentStatus;
		var $commissionerId;
		var $clientId;
		var $gallaryExists = false;
		var $images = array(); // 0-Index=>imageLocation
		var $error;
		function __construct($id) {
			global $dbh;
			$this->commissionId = $id;
			$this->userId = $_SESSION['userObj']->getUserId();
			$query = <<<SQL
SELECT
	count(cc.iCommissionId) as commissionCount,
	cc.cTitle as title,
	cc.cDescription as description,
	concat(cu.cFirstName, ' ', cu.cLastName) as clientName,
	cc.iCost as cost,
	ca.cName as paymentOption,
	cc.dCreatedDate as inputTime,
	cpr.cName as progressStatus,
	cpa.cName as paymentStatus,
	cc.iProgressStatusId as progressStatusId,
	cc.iPaymentStatusId as paymentStatusId,
	cc.iIsArchived as isArchived,
	cc.iCommissionerId as commissionerId,
	cc.iClientId as clientId,
	count(ci.iImageId) as imageCount
FROM
	COM_COMMISSION cc
INNER JOIN
	COM_USER cu ON cu.iUserId = cc.iCommissionerId
INNER JOIN
	COM_ACCOUNT ca ON ca.iAccountId = cc.iAccountId
INNER JOIN
	COM_PROGRESSSTATUS cpr ON cpr.iStatusId = cc.iProgressStatusId
INNER JOIN
	COM_PAYMENTSTATUS cpa ON cpa.iStatusId = cc.iPaymentStatusId
LEFT JOIN
	COM_IMAGES ci ON ci.iCommissionId = cc.iCommissionId
WHERE
	cc.iCommissionId = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $id);
			$runQuery->execute();
			$result = $runQuery->fetch(PDO::FETCH_ASSOC);
			if($result['commissionCount'] === '0') {
				$this->error = COMMISSION_NOT_EXIST;			}
			else {
				$this->exists = true;
				$this->title = $result['title'];
				$this->description = $result['description'];
				$this->clientName = $result['clientName'];
				$this->cost = $result['cost'];
				$this->paymentOption = $result['paymentOption'];
				$this->inputTime = $result['inputTime'];
				$this->progressStatus = $result['progressStatus'];
				$this->paymentStatus = $result['paymentStatus'];
				$this->progressStatusId = $result['progressStatusId'];
				$this->paymentStatusId = $result['paymentStatusId'];
				$this->isArchived = $result['isArchived'];
				$this->commissionerId = $result['commissionerId'];
				$this->clientId = $result['clientId'];
				$this->gallaryExists = ($result['imageCount']!=='0'?true:false); //If there's at least 1 iamge, set to true
				if( $this->gallaryExists === true ) {
					$query = <<<SQL
SELECT
	iImageId as imageId,
	cLocation as location
FROM
	COM_IMAGES
WHERE
	iCommissionId = ?
SQL;
					$runQuery = $dbh->prepare($query);
					$runQuery->bindParam(1, $id);
					$runQuery->execute();
					$results = $runQuery->fetchall(PDO::FETCH_ASSOC);
					foreach($results as $key=>$val) {
						$this->images[count($this->images)] = $results['cLocation'];
					}
				}
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
				}
			}
			else {
				$this->error = PAYMENT_STATUS_NOT_EXIST;
				die('Error');
			}
		}
		function updateProgressStatus($statusName) {
			global $dbh;
			$query = <<<SQL
SELECT
	count(iStatusId) as rowCount,
	iStatusId as statusId
FROM
	COM_PROGRESSSTATUS
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
				if($desiredId !== $this->progressStatusId) {
					//Change in status, do that here
					$query = <<<SQL
UPDATE
	COM_COMMISSION
SET
	IPROGRESSSTATUSID = ?,
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
				}
			}
			else {
				$this->error = PROGRESS_STATUS_NOT_EXIST;
				die('Error');
			}
		}
		function archive() {
			global $dbh;
			if($this->isArchived === '0') { //If not already archived
				$query = <<<SQL
UPDATE
	COM_COMMISSION
SET
	iIsArchived = 1,
	dModifiedDate = NOW()
WHERE
	iCommissionId = ?
SQL;
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $this->commissionId);
				$runQuery->execute();
			}
		}
		function unArchive() {
			global $dbh;
			if($this->isArchived === '1') { //If already archived
				$query = <<<SQL
UPDATE
	COM_COMMISSION
SET
	iIsArchived = 0,
	dModifiedDate = NOW()
WHERE
	iCommissionId = ?
SQL;
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $this->commissionId);
				$runQuery->execute();
			}
		}
	}
?>
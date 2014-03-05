<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	define('COMMISSION_NOT_EXIST', 'That commission doesn\'t exist.');
	define('PAYMENT_STATUS_NOT_EXIST', 'Tried using payment status that doesn\'t exist.');
	define('PROGRESS_STATUS_NOT_EXIST', 'Tried using progress status that doesn\'t exist.');
	define('TITLE_CHANGE_FAIL', 'Could not change the title of commission.');
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
		var $commissionerName;
		var $cost;
		var $paymentOption;
		var $inputTime;
		var $progressStatus;
		var $paymentStatus;
		var $commissionerId;
		var $clientId;
		var $galleryExists = false;
		var $images = array(); // iImageId=>imageLocation
		var $publicImages = array(); // iImageId=>1
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
	concat(cu1.cFirstName, ' ', cu1.cLastName) as clientName,
	concat(cu2.cFirstName, ' ', cu2.cLastName) as commissionerName,
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
	COM_USER cu1 ON cu1.iUserId = cc.iClientId
INNER JOIN
	COM_USER cu2 ON cu2.iUserId = cc.iCommissionerId
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
				$this->commissionerName = $result['commissionerName'];
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
				$this->galleryExists = ($result['imageCount']!=='0'?true:false); //If there's at least 1 iamge, set to true
				if( $this->galleryExists === true ) {
					$query = <<<SQL
SELECT
	iImageId as imageId,
	cLocation as location,
	iIsPublic as isPublic
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
						$this->images[$val['imageId']] = $val['location'];
						if( $val['isPublic'] === '1' ) //Is public
							$this->publicImages[$val['imageId']] = 1;
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
		function changeTitle($newTitle) {
			global $dbh;
			$query = <<<SQL
UPDATE
	COM_COMMISSION
SET
	cTitle = ?,
	dModifiedDate = NOW()
WHERE
	iCommissionId = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $newTitle);
			$runQuery->bindParam(2, $this->commissionId);
			if(!$runQuery->execute()) {
				$this->error = TITLE_CHANGE_FAIL;
				return false;
			}
			$this->title = $newTitle;
			return true;
		}
		function uploadPhoto($file = null) {
			global $env;
			global $dbh;
			/*
			*   FILE UPLOAD CODE COURTESY OF "CertaiN"
			*   http://us2.php.net/file_upload#114004
			*/
			$uploadDir = $env['IMAGE_LIB'];
			$fileSize = intval( ($env['UPLOAD_SIZE_LIMIT']*1024)*1024 ); //Stored as MB, turn into B
			$allowedTypes = array(
				'jpg' => 'image/jpeg',
				'png' => 'image/png',
				'gif' => 'image/gif'
			);
			if( $file===null ) {
				return false;
			}
			try {
				// Undefined | Multiple Files | $file Corruption Attack
				// If this request falls under any of them, treat it invalid.
				if( !isset($file['upfile']['error']) || is_array($file['upfile']['error']) ) {
					throw new RuntimeException('Invalid parameters.');
				}
				// Check $file['upfile']['error'] value.
				switch($file['upfile']['error']) {
					case UPLOAD_ERR_OK:
						break;
					case UPLOAD_ERR_NO_FILE:
						throw new RuntimeException('No file sent.');
					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						throw new RuntimeException('Exceeded filesize limit.');
					default:
						throw new RuntimeException('Unknown errors.');
				}
				// You should also check filesize here. 
				if($file['upfile']['size'] > $fileSize) {
					throw new RuntimeException('Exceeded filesize limit.');
				}
				// DO NOT TRUST $file['upfile']['mime'] VALUE !!
				// Check MIME Type by yourself.
				$finfo = new finfo(FILEINFO_MIME_TYPE);
				if( false === $ext = array_search($finfo->file($file['upfile']['tmp_name']), $allowedTypes, true) ) {
					throw new RuntimeException('Invalid file format.');
				}
				// You should name it uniquely.
				// DO NOT USE $file['upfile']['name'] WITHOUT ANY VALIDATION !!
				// On this example, obtain safe unique name from its binary data.
				$shaFileName = sha1_file($file['upfile']['tmp_name']);
				$fullPath = $uploadDir.$shaFileName.'.'.$ext;
				if ( !move_uploaded_file($file['upfile']['tmp_name'], sprintf($uploadDir.'%s.%s', $shaFileName, $ext)) ) {
					throw new RuntimeException('Failed to move uploaded file.');
				}
				$query = <<<SQL
INSERT INTO COM_IMAGES(cLocation,iCommissionId,iUserId,dCreatedDate)
VALUES(?, ?, ?, NOW())
SQL;
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $fullPath);
				$runQuery->bindParam(2, $this->commissionId);
				$runQuery->bindParam(3, $_SESSION['userObj']->getUserId());
				$runQuery->execute();
				/*
				*	MAY WANT TO __construct($this->commissionId) HERE
				*	TO REBUILD OBJ DUE TO NEW IMAGE
				*/
				return true;
			} catch (RuntimeException $e) {
				$this->error = $e->getMessage();
			}
		}
	}
?>
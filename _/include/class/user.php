<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	class user {
		var $errMsg;
		var $noHashUsername;
		var $username;
		var $password;
		var $userId;
		var $userType;
		function __construct($username, $password) {
			$this->noHashUsername = $username;
			$this->username = md5($username);
			$this->password = md5($password);
		}
		function doLogin() {
			global $dbh;
			$query = <<<SQL
SELECT
	count(iUserId) foundUser,
	iUserId,
	cUserType,
	iIsActive
FROM
	COM_USER
WHERE
	cUsername = ? AND
	cPassword = ?
LIMIT
	0,1
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $this->username);
			$runQuery->bindParam(2, $this->password);
			$runQuery->execute();
			$result = $runQuery->fetch(PDO::FETCH_ASSOC);
			if( $result['foundUser'] === "1" ) {
				$this->userId = $result['iUserId'];
				$this->userType = $result['cUserType'];
				$query = <<<SQL
UPDATE
	COM_USER
SET
	DLASTLOGIN = NOW()
WHERE
	IUSERID = ?
SQL;
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $this->userId);
				$runQuery->execute();
				return true;
			}
			else {
				$this->errMsg = "Invalid username or password.";
				return false;
			}
		}
		function doCreate($type="client") {
			global $dbh;
			$query = <<<SQL
SELECT
	count(iUserId) foundUser
FROM
	COM_USER
WHERE
	cUsername = ?
LIMIT
	0,1
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $this->username);
			$runQuery->execute();
			$result = $runQuery->fetch(PDO::FETCH_ASSOC);
			if( $result['foundUser'] !== "0" ) {
				$this->errMsg = "That username is taken";
				return false;
			}
			$query = <<<SQL
INSERT INTO
	COM_USER (CUSERNAME, CPASSWORD, CUSERTYPE, DCREATEDDATE, IISACTIVE)
VALUES (?, ?, ?, NOW(), 1)
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $this->username);
			$runQuery->bindParam(2, $this->password);
			$runQuery->bindParam(3, $type);
			$runQuery->execute();
			if(!$this->doLogin()) {
				$this->errMsg = "Something broke, cannot login";
				return false;
			}
			else {
				return true;
			}
		}
	}
?>
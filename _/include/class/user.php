<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	class user {
		var $errMsg;
		var $email; //Don't grab email directly for sensitive info. Use $this->getEmail() instead.
		var $username;
		var $password;
		function __construct($username, $password = null) {
			$this->email = $username;
			$this->username = md5($username);
			$this->password = $password === null ? "null" : md5($password); //If a password is provided, hash it otherwise save as "null"
		}
		function getUserId() {
			global $dbh;
			$query = <<<SQL
SELECT
	iUserId
FROM
	COM_USER
WHERE
	CUSERNAME = ? AND
	CPASSWORD = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $this->username);
			$runQuery->bindParam(2, $this->password);
			$runQuery->execute();
			$result = $runQuery->fetch(PDO::FETCH_ASSOC);
			return $result['iUserId'];
		}
		function getFirstName() {
			global $dbh;
			$query = <<<SQL
SELECT
	cFirstName
FROM
	COM_USER
WHERE
	CUSERNAME = ? AND
	CPASSWORD = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $this->username);
			$runQuery->bindParam(2, $this->password);
			$runQuery->execute();
			$result = $runQuery->fetch(PDO::FETCH_ASSOC);
			return $result['cFirstName'];
		}
		function getLastName() {
			global $dbh;
			$query = <<<SQL
SELECT
	cLastName
FROM
	COM_USER
WHERE
	CUSERNAME = ? AND
	CPASSWORD = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $this->username);
			$runQuery->bindParam(2, $this->password);
			$runQuery->execute();
			$result = $runQuery->fetch(PDO::FETCH_ASSOC);
			return $result['cLastName'];
		}
		function getEmail() {
			global $dbh;
			$query = <<<SQL
SELECT
	cEmail
FROM
	COM_USER
WHERE
	CUSERNAME = ? AND
	CPASSWORD = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $this->username);
			$runQuery->bindParam(2, $this->password);
			$runQuery->execute();
			$result = $runQuery->fetch(PDO::FETCH_ASSOC);
			return $result['cEmail'];
		}
		function getUserType() {
			global $dbh;
			$query = <<<SQL
SELECT
	cUserType
FROM
	COM_USER
WHERE
	CUSERNAME = ? AND
	CPASSWORD = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $this->username);
			$runQuery->bindParam(2, $this->password);
			$runQuery->execute();
			$result = $runQuery->fetch(PDO::FETCH_ASSOC);
			return $result['cUserType'];
		}
		function doLogin() {
			global $dbh;
			$query = <<<SQL
SELECT
	count(iUserId) foundUser,
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
				$query = <<<SQL
UPDATE
	COM_USER
SET
	DLASTLOGIN = NOW()
WHERE
	cUsername = ? AND
	cPassword = ?
SQL;
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $this->username);
				$runQuery->bindParam(2, $this->password);
				$runQuery->execute();
				return true;
			}
			else {
				$this->errMsg = "Invalid username or password.";
				return false;
			}
		}
		function doCreate($firstName, $lastName, $type="client", $autoLogin = true) {
			global $dbh;
			$isActive = $this->password !== "null" ? "1" : "0"; //If password is set as "null" (User created by commission entry), set to not active.
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
	COM_USER (CFIRSTNAME, CLASTNAME, CEMAIL, CUSERNAME, CPASSWORD, CUSERTYPE, DCREATEDDATE, IISACTIVE)
VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $firstName);
			$runQuery->bindParam(2, $lastName);
			$runQuery->bindParam(3, $this->email);
			$runQuery->bindParam(4, $this->username);
			$runQuery->bindParam(5, $this->password);
			$runQuery->bindParam(6, $type);
			$runQuery->bindParam(7, $isActive);
			$runQuery->execute();
			if($autoLogin === true) {
				if(!$this->doLogin()) {
					$this->errMsg = "Something broke, cannot login";
					return false;
				}
			}
			if($type !== "client") {
				$this->addPaymentOption('Credit / Debit');
				$this->changePaymentDefault($this->getPaymentId('Credit / Debit'));
			}
			return true;
		}
		function changePass($newPass) {
			global $dbh;
			$query = <<<SQL
UPDATE
	COM_USER
SET
	CPASSWORD = ?,
	DMODIFIEDDATE = NOW()
WHERE
	CUSERNAME = ? AND
	CPASSWORD = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, md5($newPass));
			$runQuery->bindParam(2, $this->username);
			$runQuery->bindParam(3, $this->password);
			$runQuery->execute();
			$this->password = md5($newPass);
		}
		function addPaymentOption($name) {
			global $dbh;
			$query = <<<SQL
INSERT INTO
	COM_ACCOUNT(CNAME, IUSERID, DCREATEDDATE)
VALUES(?, ?, NOW())
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $name);
			$runQuery->bindParam(2, $this->getUserId());
			$runQuery->execute();
			return true;
		}
		function getPaymentId($name) {
			global $dbh;
			$query = <<<SQL
SELECT
	IACCOUNTID
FROM
	COM_ACCOUNT
WHERE
	IUSERID = ? AND
	CNAME = ?
LIMIT
	0,1
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $this->getUserId());
			$runQuery->bindParam(2, $name);
			$runQuery->execute();
			$result = $runQuery->fetch(PDO::FETCH_ASSOC);
			return $result['IACCOUNTID'];
		}
		function changePaymentDefault($id) {
			global $dbh;
			//$query changes all to not default
			$query = <<<SQL
UPDATE
	COM_ACCOUNT
SET
	IISDEFAULT = 0
WHERE
	IUSERID = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $this->getUserId());
			$runQuery->execute();
			//$query changes only the selected one to default
			$query = <<<SQL
UPDATE
	COM_ACCOUNT
SET
	IISDEFAULT = 1
WHERE
	IACCOUNTID = ? AND
	IUSERID = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $id);
			$runQuery->bindParam(2, $this->getUserId());
			$runQuery->execute();
		}
		function removePaymentOption($id) {
			global $dbh;
			$query = <<<SQL
DELETE FROM
	COM_ACCOUNT
WHERE
	IUSERID = ? AND
	IACCOUNTID = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $this->getUserId());
			$runQuery->bindParam(2, $id);
			$runQuery->execute();
		}
		function getPaymentOptions() {
			global $dbh;
			$query = <<<SQL
SELECT
	CNAME,
	IISDEFAULT
FROM
	COM_ACCOUNT
WHERE
	IUSERID = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $this->getUserId());
			$runQuery->execute();
			return($runQuery->fetchAll());
		}
	}
?>
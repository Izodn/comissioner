<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/password.php'; //Include the hash functions
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/dump.php';
	//START ERROR MESSAGES
	define('USERNAME_TAKEN', 'That username is unavailable.');
	define('INVALID_LOGIN', 'Username or password is incorrect.');
	define('SOMETHING_BROKE', 'Oops! Something broke. If this issue persists please contact an admin.');
	//END ERROR MESSAGES
	class user {
		var $errMsg;
		var $salt;
		var $hash_cost;
		var $email; //Don't grab email directly for sensitive info. Use $this->getEmail() instead.
		var $username;
		var $password;
		var $userId;
		function __construct($username, $password = null) {
			global $env; //We need the salt from env variables
			$this->salt = isset($env['SALT']) ? $env['SALT'] : ''; //Default salt = ""
			$this->hash_cost = isset($env['HASH_COST']) ? intVal($env['HASH_COST']) : 10; //Default cost = 10
			$this->email = $username;
			$this->username = $username;
			$this->password = $password === null ? null : $password; //If a password is provided, hash it otherwise save as null
		}
		function hash_pass($val) {
			return password_hash($val, PASSWORD_BCRYPT, array( 'SALT'=>$this->salt,'cost'=>$this->hash_cost ));
		}
		function getUserId() {
			return $this->userId;
		}
		function getFirstName() {
			global $dbh;
			$query = <<<SQL
SELECT
	cFirstName
FROM
	COM_USER
WHERE
	IUSERID = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $this->userId);
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
	IUSERID = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $this->userId);
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
	IUSERID = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $this->userId);
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
	IUSERID = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $this->userId);
			$runQuery->execute();
			$result = $runQuery->fetch(PDO::FETCH_ASSOC);
			return $result['cUserType'];
		}
		function doLogin() {
			global $dbh;
			$query = <<<SQL
SELECT
	count(iUserId) foundUser,
	iIsActive,
	cPassword,
	iUserId
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
			if( $result['foundUser'] === "1" && password_verify($this->password, $result['cPassword'])) {
				$this->userId = $result['iUserId'];
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
				$this->errMsg = INVALID_LOGIN;
				return false;
			}
		}
		function doClaim($firstName, $lastName) { //Used to "claim" users created via commission-input
			//This will need redone when email authentication in implemented
			global $dbh;
			$query = <<<SQL
UPDATE
	COM_USER
SET
	CFIRSTNAME = ?,
	CLASTNAME = ?,
	CPASSWORD = ?,
	IISACTIVE = 1
WHERE
	CEMAIL = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $firstName);
			$runQuery->bindParam(2, $lastName);
			$runQuery->bindParam(3, $this->hash_pass($this->password));
			$runQuery->bindParam(4, $this->email);
			if(!$runQuery->execute()) {
				$this->errMsg = SOMETHING_BROKE;
				return false;
			}
			return true;
		}
		function doCreate($firstName, $lastName, $type="client", $autoLogin = true) {
			global $dbh;
			$isActive = $this->password !== null ? "1" : "0"; //If password is set as null (User created by commission entry), set to not active.
			$query = <<<SQL
SELECT
	count(iUserId) foundUser,
	cPassword,
	iIsActive
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
			if( $result['foundUser'] === "1" ) { //if a user was found
				if($result['cPassword'] === null && $result['iIsActive'] === '0') { //User was added via commission-input
					$this->doClaim($firstName, $lastName); //Claim user
					if(!$this->doLogin()) {
						$this->errMsg = SOMETHING_BROKE;
						return false;
					}
					return true;
				}
				else {
					echo dump($result);
					$this->errMsg = USERNAME_TAKEN;
					return false;
				}
			}
			$pass = $this->password === null ? null : $this->hash_pass($this->password);
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
			//If password is null (created via commission entry), pass '' instead of attempting to hash
			$runQuery->bindParam(5, $pass);
			$runQuery->bindParam(6, $type);
			$runQuery->bindParam(7, $isActive);
			$runQuery->execute();
			if($autoLogin === true) {
				if(!$this->doLogin()) {
					$this->errMsg = SOMETHING_BROKE;
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
	userId = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $this->hash_pass($newPass));
			$runQuery->bindParam(2, $this->userId);
			$runQuery->execute();
			$this->password = $newPass;
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
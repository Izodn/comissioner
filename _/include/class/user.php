<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/password.php'; //Include the hash functions
	//START ERROR MESSAGES
	define('EMAIL_TAKEN', 'That email is already in use.');
	define('INVALID_EMAIL', 'This isn\'t a valid email address.');
	define('INVALID_LOGIN', 'Email or password is incorrect.');
	define('SOMETHING_BROKE', 'Oops! Something broke. If this issue persists please contact an admin.');
	define('ACCOUNT_IN_USE', 'That payment option is in use, and can\'t be deleted.');
	define('NO_REMOVE_DEFAULT', 'You cannot remove the default payment option.');
	//END ERROR MESSAGES
	class user {
		var $errMsg;
		var $salt;
		var $hash_cost;
		var $email; //Don't grab email directly for sensitive info. Use $this->getEmail() instead.
		var $password;
		var $userId;
		function __construct($email, $password = null) {
			$email = strtolower($email); //Make sure to lower-case
			global $env; //We need the salt from env variables
			$this->salt = isset($env['SALT']) ? $env['SALT'] : ''; //Default salt = ""
			$this->hash_cost = isset($env['HASH_COST']) ? intVal($env['HASH_COST']) : 10; //Default cost = 10
			$this->email = $email;
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
			$runQuery->bindValue(1, $this->userId);
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
			$runQuery->bindValue(1, $this->userId);
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
			$runQuery->bindValue(1, $this->userId);
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
			$runQuery->bindValue(1, $this->userId);
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
	cEmail = ?
GROUP BY
	iIsActive,
	cPassword,
	iUserId,
	cEmail
LIMIT
	0,1
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindValue(1, $this->email);
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
				$runQuery->bindValue(1, $this->userId);
				$runQuery->execute();
				return true;
			}
			else {
				$this->errMsg = INVALID_LOGIN;
				return false;
			}
		}
		function doClaim($firstName, $lastName, $type='client') { //Used to "claim" users created via commission-input
			//This will need redone when email authentication in implemented
			global $dbh;
			$query = <<<SQL
UPDATE
	COM_USER
SET
	CFIRSTNAME = ?,
	CLASTNAME = ?,
	CPASSWORD = ?,
	CUSERTYPE = ?,
	IISACTIVE = 1
WHERE
	CEMAIL = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindValue(1, $firstName);
			$runQuery->bindValue(2, $lastName);
			$runQuery->bindValue(3, $this->hash_pass($this->password));
			$runQuery->bindValue(4, $type);
			$runQuery->bindValue(5, $this->email);
			if(!$runQuery->execute()) {
				$this->errMsg = SOMETHING_BROKE;
				return false;
			}
			return true;
		}
		function doCreate($firstName, $lastName, $type = null, $autoLogin = true, $fromComInput = false) {
			if( !filter_var($this->email, FILTER_VALIDATE_EMAIL) ) { //Check to see if we're given an email
				$this->errMsg = INVALID_EMAIL;
				return false;
			}
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
	cEmail = ?
GROUP BY
	cPassword,
	iIsActive,
	cEmail
LIMIT
	0,1
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindValue(1, $this->email);
			$runQuery->execute();
			$result = $runQuery->fetch(PDO::FETCH_ASSOC);
			if( $result['foundUser'] === "1" && $fromComInput === false) { //if a user was found and not from commission input
				if($result['cPassword'] === null && $result['iIsActive'] === '0') { //User was added via commission-input
					$this->doClaim($firstName, $lastName, $type); //Claim user
					if(!$this->doLogin()) {
						$this->errMsg = SOMETHING_BROKE;
						return false;
					}
					return true;
				}
				else {
					$this->errMsg = EMAIL_TAKEN;
					return false;
				}
			}
			elseif($result['foundUser'] === '0') { //Only if no users found
				$pass = $this->password === null ? null : $this->hash_pass($this->password);
				$query = <<<SQL
INSERT INTO
	COM_USER (CFIRSTNAME, CLASTNAME, CEMAIL, CPASSWORD, CUSERTYPE, DCREATEDDATE, IISACTIVE)
VALUES (?, ?, ?, ?, ?, NOW(), ?)
SQL;
				$runQuery = $dbh->prepare($query);
				$runQuery->bindValue(1, $firstName);
				$runQuery->bindValue(2, $lastName);
				$runQuery->bindValue(3, $this->email);
				//If password is null (created via commission entry), pass '' instead of attempting to hash
				$runQuery->bindValue(4, $pass);
				$runQuery->bindValue(5, $type);
				$runQuery->bindValue(6, $isActive);
				$runQuery->execute();
				if($autoLogin === true) {
					if(!$this->doLogin()) {
						$this->errMsg = SOMETHING_BROKE;
						return false;
					}
				}
				/* I'm not sure we really need this yet.
					We don't have credit/debit setup anyway, so just let the
					commissioner add it.
				if(in_array($type, array('commissioner', 'superuser'))) {
					$this->addPaymentOption('Credit / Debit');
					$this->changePaymentDefault($this->getPaymentId('Credit / Debit'));
				}*/
				return true;
			}
			return true; //Should only get hit when inputting a commission for a user that hasn't been claimed yet
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
	iUserId = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindValue(1, $this->hash_pass($newPass));
			$runQuery->bindValue(2, $this->userId);
			$runQuery->execute();
			$this->password = $newPass;
		}
		function changeEmail($newEmail) {
			if( !$this->checkEmail($newEmail) ) { //Email's taken/invalid
				//No need to set error here, it's set in checkEmail()
				return false;
			}
			global $dbh;
			$query = <<<SQL
UPDATE
	COM_USER
SET
	cEmail = ?
WHERE
	iUserId = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindValue(1, $newEmail);
			$runQuery->bindValue(2, $this->getUserId());
			$runQuery->execute();
			$this->email = $newEmail;
			return true;
		}
		function checkEmail($email) {
			if( !filter_var($email, FILTER_VALIDATE_EMAIL) ) { //Check to see if we're given an email
				$this->errMsg = INVALID_EMAIL;
				return false;
			}
			global $dbh;
			$email = strtolower($email); //We want case insensitive checks.
			$query = <<<SQL
SELECT
	count(iUserId)
FROM
	COM_USER
WHERE
	cEmail = ?
SQL;
			$runQuery =  $dbh->prepare($query);
			$runQuery->bindValue(1, $email);
			$runQuery->execute();
			$result = $runQuery->fetchall(PDO::FETCH_NUM);
			if( $result[0][0] !== '0' ) { //If email is in use
				$this->errMsg = EMAIL_TAKEN;
				return false;
			}
			return true; //It's good-to-go, let's return
		}
		function addPaymentOption($name) {
			global $dbh;
			$default = ($this->getPaymentDefaultId() === false ? '1':'0');
			$query = <<<SQL
INSERT INTO
	COM_ACCOUNT(CNAME, IISDEFAULT, IUSERID, DCREATEDDATE)
VALUES(?, ?, ?, NOW())
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindValue(1, $name);
			$runQuery->bindValue(2, $default);
			$runQuery->bindValue(3, $this->getUserId());
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
			$runQuery->bindValue(1, $this->getUserId());
			$runQuery->bindValue(2, $name);
			$runQuery->execute();
			$result = $runQuery->fetch(PDO::FETCH_ASSOC);
			return $result['IACCOUNTID'];
		}
		function getPaymentDefaultId() {
			global $dbh;
			$query = <<<SQL
SELECT
	iAccountId
FROM
	COM_ACCOUNT
WHERE
	iUserId = ? AND
	iIsDefault = 1
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindValue(1, $this->getUserId());
			$runQuery->execute();
			if(!$result = $runQuery->fetch(PDO::FETCH_NUM))
				return false;
			else
				return $result[0];
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
			$runQuery->bindValue(1, $this->getUserId());
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
			$runQuery->bindValue(1, $id);
			$runQuery->bindValue(2, $this->getUserId());
			$runQuery->execute();
		}
		function removePaymentOption($id) {
			global $dbh;
			if($id === $this->getPaymentDefaultId()) {
				$this->errMsg = NO_REMOVE_DEFAULT;
				return false;
			}
			$query = <<<SQL
SELECT
	count(iCommissionId) comCount
FROM
	COM_COMMISSION
WHERE
	iAccountId = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindValue(1, $id);
			$runQuery->execute();
			$result = $runQuery->fetch(PDO::FETCH_ASSOC);
			if($result['comCount'] !== '0') { //Can't delete payment options in use
				$this->errMsg = ACCOUNT_IN_USE;
				return false;
			}
			$query = <<<SQL
DELETE FROM
	COM_ACCOUNT
WHERE
	IUSERID = ? AND
	IACCOUNTID = ?
SQL;
			$runQuery = $dbh->prepare($query);
			$runQuery->bindValue(1, $this->getUserId());
			$runQuery->bindValue(2, $id);
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
			$runQuery->bindValue(1, $this->getUserId());
			$runQuery->execute();
			return($runQuery->fetchAll());
		}
	}
?>
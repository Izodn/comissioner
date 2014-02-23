<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/user.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	function requireLogin($type='') {
		global $dbh;
		$query = <<<SQL
SELECT
	iUserId
FROM
	COM_USER
LIMIT
	0,1
SQL;
		$runQuery = $dbh->prepare($query);
		$runQuery->execute();
		$result = $runQuery->fetch(PDO::FETCH_ASSOC);
		if( $result === false ) { //No users found, need superuser setup
			header('Location: _/setup.php');
			die(); //Die to prevent further script execution
		}
		if(empty($_SESSION)) { //Start session if not already set (Shouldn't be already set)
			session_start();
		}
		if( !isset($_SESSION['userObj']) ) { //If not logged in
			if($type==='') { //General "not-logged-in"
				header('Location: login.php');
				die(); //Die to prevent further script execution
			}
			elseif($type==='client') { //Not logged in as client
				header('Location: login.php');
				die(); //Die to prevent further script execution
			}
			elseif($type==='commissioner') { //Not logged in as commissioner
				header('Location: login.php');
				die(); //Die to prevent further script execution
			}
			elseif($type==='superuser') { //Not logged in as superuser
				header('Location: login.php');
				die(); //Die to prevent further script execution
			}
		}
		else { //Logged in, check credentials
			if($type==='client' && $_SESSION['userObj']->getUserType() !== $type) { //Not logged in as client
				header('Location: login.php');
				die(); //Die to prevent further script execution
			}
			elseif($type==='commissioner' && $_SESSION['userObj']->getUserType() !== $type) { //Not logged in as commissioner
				header('Location: login.php');
				die(); //Die to prevent further script execution
			}
			elseif($type==='superuser' && $_SESSION['userObj']->getUserType() !== $type) { //Not logged in as superuser
				header('Location: login.php');
				die(); //Die to prevent further script execution
			}
		}
	}
?>
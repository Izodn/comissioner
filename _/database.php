<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/dbh.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/credentialCheck.php';
	global $dbh;
	if( !isset($_SESSION['databaseOk']) ) {
		//Start check to see if the database needs setup for the first time
		$runQuery = $dbh->prepare("SELECT count(*) rowCount FROM user_account");
		$runQuery->execute();
		$result = $runQuery->fetch(PDO::FETCH_BOTH);
		if($result['rowCount'] === "0") {
			header("Location: Register.php");
		}
		else {
			$_SESSION['databaseOK'] = true;
		}
		//End database check
	}
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/lockdown.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/errorLog.php';
	function superuserLocation()
	{
		$url = array(
			//SUPERUSER ONLY URLS
			'/ipIndex.php',
			'/SQL.php',
			'/userDetail.php',
			'/userManagement.php',
			'/version.php',
			'/_/baseEncode.php',
			'/_/errorLog.php',
			'/errorLogList.php',
			'/test.php',
			'/uiTest.php',
			'/emailList.php',
			'/backupDB.php'
		);
		$curURL = $_SERVER['PHP_SELF'];
		if(in_array($curURL, $url))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	function adminLocation()
	{
		$url = array(
			//ADMIN ONLY URLS
			'/adminIndex.php',
			'/adminPendingCom.php',
			'/archive.php',
			'/photo.php',
			'/progress.php',
			'/search.php',
			'/settings.php',
			'/reports.php'
		);
		$curURL = $_SERVER['PHP_SELF'];
		if(in_array($curURL, $url))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	function clientLocation()
	{
		$url = array(
			//CLIENT ONLY URLS
			'/commissionPending.php',
			'/commissionRequest.php',
			'/index.php',
			'/userProfile.php'
		);
		$curURL = $_SERVER['PHP_SELF'];
		if(in_array($curURL, $url))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	function sharedLocation()
	{
		$url = array(
			/*
			* MAKE ABSOLUTELY SURE TO HANDLE CREDENTIALS ON THESE PAGES
			* AS THEY ARE'T MANAGED HERE.
			*/
			//SHARED URLS
			'/galleryList.php'
		);
		$curURL = $_SERVER['PHP_SELF'];
		if(in_array($curURL, $url))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	function noLoginLocation()
	{
		$url = array(
			'/clientLogin.php',
			'/login.php',
			'/clientRegister.php',
			'/clientprofile.php',
			'/redirectHandler.php',
			'/_/include/pageBuilder.php',
			'/privacyPolicy.php',
			'/gallery.php',
			'/gallery2.php',
			'/Register.php'
		);
		$curURL = $_SERVER['PHP_SELF'];
		if(in_array($curURL, $url))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	if(lockdownCheck())
	{
		$user = md5(lockdownCheck());
		$unlockedURLs = array(
			'/login.php',
			'/adminIndex.php',
			'/userManagement.php'
		);
		if((!in_array($_SERVER['PHP_SELF'], $unlockedURLs)) || (globalOut('users') && $user != globalOut('users')) && $_SERVER['PHP_SELF'] != '/login.php')
		{
			die('Server is down<br>Admin login: <a href="/login.php">Click Here</a>');
		}
	}
	if(superuserLocation())
	{
		require_once $_SERVER['DOCUMENT_ROOT'].'/_/banCheck.php';
		banCheck();
		$authUser = md5($_SESSION['ENV']['SUPERUSER']);
		if(globalOut('user_key4'))
		{
			if(globalOut('user_key4') != $authUser)
			{
				$shortVal = 'Non-superuser attempting to access superuser-only area.';
				$longVal = null;
				writeLog($shortVal, $longVal, 'HARD');
			}
		}
		elseif(globalOut('user_key3'))
		{
			if(globalOut('users') != $authUser)
			{
				$shortVal = 'Non-superuser attempting to access superuser-only area.';
				$longVal = null;
				writeLog($shortVal, $longVal, 'HARD');
			}
		}
		else
		{
			$shortVal = 'Non-superuser attempting to access superuser-only area.';
			$longVal = null;
			writeLog($shortVal, $longVal, 'HARD');
		}
		if(globalOut('user_key3'))
		{
			if(!credentialCheck('user'))
			{
				$shortVal = 'Credentials don\'t match superuser';
				$longVal = null;
				writeLog($shortVal, $longVal, 'HARD');
			}
		}
		elseif(globalOut('user_key2'))
		{
			if(!credentialCheck('client'))
			{
				$shortVal = 'Credentials don\'t match superuser';
				$longVal = null;
				writeLog($shortVal, $longVal, 'HARD');
			}
		}
		else
		{
			$shortVal = 'Non-superuser attempting to access superuser-only area.';
			$longVal = null;
			writeLog($shortVal, $longVal, 'HARD');
		}
	}
	elseif(adminLocation())
	{
		require_once $_SERVER['DOCUMENT_ROOT'].'/_/banCheck.php';
		banCheck();
		if(!credentialCheck('user'))
		{
			if(credentialCheck('client'))
			{
				header('Location: /index.php');
			}
			else
			{
				header('Location: /login.php');
			}
		}
	}
	elseif(clientLocation())
	{
		require_once $_SERVER['DOCUMENT_ROOT'].'/_/banCheck.php';
		banCheck();
		if(!credentialCheck('client'))
		{
			if(credentialCheck('user'))
			{
				header('Location: /adminIndex.php');
			}
			else
			{
				header('Location: /clientLogin.php');
			}
		}
	}
	elseif(sharedLocation())
	{	
		require_once $_SERVER['DOCUMENT_ROOT'].'/_/banCheck.php';
		banCheck();
		if(credentialCheck('user'))
		{
		}
		elseif(credentialCheck('client'))
		{
		}
		else
		{
			die('Please <a href="clientLogin.php">login</a> to view this page.');
		}
	}
	elseif(noLoginLocation())
	{
		require_once $_SERVER['DOCUMENT_ROOT'].'/_/banCheck.php';
		banCheck();
	}
	else
	{
		$shortVal = 'This location isn\'t handled. This is a bad thing';
		$longVal = null;
		writeLog($shortVal, $longVal, 'HARD');
	}
?>
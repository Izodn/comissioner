<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/user.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/links.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/dump.php';
	session_start();
	if( !isset($_SESSION['userObj']) ) { //If not logged in
		global $dbh;
		global $links;
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
		}
		else {
			header('Location: login.php');
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Commissioner - Home</title>
	</head>
	<body>
		<center>
			<?php
				$links = new links($_SESSION['userObj']);
				echo $links->getLinks(" | ");
			?>
		</center>
	</body>
</html>
<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/user.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/commission.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/links.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/requireLogin.php'; //Checks login and starts session
	requireLogin();
	if($_SESSION['userObj']->getUserType() === 'superuser' || $_SESSION['userObj']->getUserType() === 'commissioner') {
		if( !empty($_FILES) ) {
			$commission = new commission($_GET['c']);
			if(!$commission->uploadPhoto($_FILES)) {
				$errMsg = $commission->error;
			}
			else {
				$successMsg = 'File successfully uploaded';
			}
		}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Commissioner - Photo</title>
	</head>
	<body>
		<center>
			<?php
				global $dbh;
				$links = new links($_SESSION['userObj']);
				echo $links->getLinks();
			?>
			<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST" enctype="multipart/form-data">
				<input type="file" name="upfile" id="file">
				<br><br>
				<input type="submit" name="submit" value="Submit">
			</form>
			<?php
				if(isset($errMsg))
					echo '<font color="#FF0000">'.$errMsg.'</font>';
				elseif(isset($successMsg))
					echo '<font color="#FF0000">'.$successMsg.'</font>';
			?>
		</center>
	</body>
</html>
<?php
	}
	elseif($_SESSION['userObj']->getUserType() === 'client') {
		//Handle here
		header('Location: /');
	}
?>
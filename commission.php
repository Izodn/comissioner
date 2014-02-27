<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/user.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/links.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/commission.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/requireLogin.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/money.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/dump.php';
	requireLogin();
	if(isset($_GET['id']) && $_GET['id'] !== '')
		$commission = new commission($_GET['id']); //Save $commission as commission obj
	else //If id not set
		$commission = new commission(-1); //Will never exist
	if($_SESSION['userObj']->getUserType() === "superuser" || $_SESSION['userObj']->getUserType() === "commissioner") { //Is commissioner / superuser
		if(isset($_POST['title'])) { //Title change
			$commission->changeTitle($_POST['title']);
		}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Commissioner - Commission</title>
		<script>
			showChangeTitle = function() {
				tgtElement = document.getElementById('titleContainer');
				tgtTitle = document.getElementById('titleSpan');
				tgtElement.innerHTML = '<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST"><input type="test" name="title" value="'+tgtTitle.innerHTML+'"></form>';
			}
		</script>
	</head>
	<body>
		<center>
			<?php
				if( $commission->exists === true ) { //Make sure it's real
					if($commission->commissionerId === $_SESSION['userObj']->getUserId()) {
					//Make sure the user viewing this, owns the commission
						$links = new links($_SESSION['userObj']);
						echo $links->getLinks();
						$data = array(
							'Title'			=>$commission->title,
							'Description'	=>$commission->description,
							'Client Name'	=>$commission->clientName,
							'Cost'			=>$commission->cost,
							'Payment Option'=>$commission->paymentOption,
							'Input Time'	=>$commission->inputTime,
							'Progress'		=>$commission->progressStatus,
							'Payment'		=>$commission->paymentStatus,
							'Gallery'		=>'' //We don't need valid data here
						);
						echo '<table border="1"><tbody>';
						foreach($data as $key=>$val) {
							echo '<tr>';
							echo '<td>'.$key.'</td>';
							if( $key === 'Title' )
								echo '<td id="titleContainer"><span id="titleSpan" onclick="showChangeTitle();">'.$val.'</span></td>';
							elseif( $key === 'Cost' )
								echo '<td>'.moneyToStr($val).'</td>';
							elseif( $key === 'Gallery' ) {
								if( $commission->galleryExists === true )
									echo '<td><a href="gallery.php?c='.$commission->commissionId.'">Gallery</a>'; //Link to gallery
								else
									echo '<td>No Images';
								//Photo upload icon here
								echo '<a href="photo.php?c='.$commission->commissionId.'"><img src="/images/upload.jpg" align="right"></a>';
								echo '</td>';
							}
							else
								echo '<td>'.$val.'</td>';
							echo '</tr>';
						}
						echo '</tbody></table>';
					}
					else {
						$errorMsg = 'You do not have permission to view this commission.';
					}
					if(isset($errorMsg))
						echo '<font color="#FF0000">'.$errorMsg.'</font>';
				}
			?>
		</center>
	</body>
</html>
<?php
	}
	elseif( $_SESSION['userObj']->getUserType() === "client" ) { //Is client
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Commissioner - Commission</title>
	</head>
	<body>
		<center>
			<?php
				if( $commission->exists === true ) { //Make sure it's real
					if($commission->clientId === $_SESSION['userObj']->getUserId()) {
					//Make sure the user viewing this, owns the commission
						$links = new links($_SESSION['userObj']);
						echo $links->getLinks();
						$data = array(
							'Title'			=>$commission->title,
							'Description'	=>$commission->description,
							'Client Name'	=>$commission->clientName,
							'Cost'			=>$commission->cost,
							'Payment Option'=>$commission->paymentOption,
							'Input Time'	=>$commission->inputTime,
							'Progress'		=>$commission->progressStatus,
							'Payment'		=>$commission->paymentStatus
						);
						echo '<table border="1"><tbody>';
						foreach($data as $key=>$val) {
							echo '<tr>';
							echo '<td>'.$key.'</td>';
							if( $key === 'Cost' )
								echo '<td>'.moneyToStr($val).'</td>';
							else
								echo '<td>'.$val.'</td>';
							echo '</tr>';
						}
						echo '</tbody></table>';
					}
					else {
						$errorMsg = 'You do not have permission to view this commission.';
					}
					if(isset($errorMsg))
						echo '<font color="#FF0000">'.$errorMsg.'</font>';
				}
			?>
		</center>
	</body>
</html>
<?
	}
	else {
		//Type not handled, this is bad.
	}

?>
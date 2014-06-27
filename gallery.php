<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/user.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/links.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/commission.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/requireLogin.php'; //Checks login and starts session
	session_start();
	$userId = isset($_SESSION['userObj']) ? $_SESSION['userObj']->getUserId() : 0;
	global $dbh;
	$imageSize = 150;
	/*
	*
	*	NOTE: WE NEED TO ADD MORE SECURITY TO THIS PAGE
	*	AS OF NOW, RANDOM PEOPLE CAN SEE OTHER COMMISSION GALLERIES
	*	BY MODIFYING URL
	*
	*/
	function generateImage($img = null, $targetDim = null) {
		global $env;
		$targetImg = $_SERVER['DOCUMENT_ROOT'].'/'.$img;
		list($width, $height, $type, $attr) = getimagesize($targetImg);
		$imageDim = array(
			'width' => $width,
			'height' => $height
		);
		$imageProperties = array(
			'width' => $width,
			'height' => $height,
			'type' => $type,
			'attr' => $attr
		);
		if($imageDim['width'] >= $imageDim['height'])
		{
			$imageDim['width'] = $imageDim['width'] * ($targetDim / $imageProperties['width']);
			$imageDim['height'] = $imageDim['height'] * ($targetDim / $imageProperties['width']);
		}
		else
		{
			$imageDim['height'] = $imageDim['height'] * ($targetDim / $imageProperties['height']);
			$imageDim['width'] = $imageDim['width'] * ($targetDim / $imageProperties['height']);
		}
		$imageDim['width'] = intval($imageDim['width']);
		$imageDim['height'] = intval($imageDim['height']);
		return '<img width="'.$imageDim['width'].'" height="'.$imageDim['height'].'" src="image.php?n='.str_replace($env['IMAGE_LIB'], '', $img).'">';
	}
	if( !empty($_POST) && isset($_GET['u']) && isset($_GET['a']) && isset($_SESSION['userObj']) && ($_SESSION['userObj']->getUserId() === $_GET['u']) ) { //If page submitted and right user
		$query = <<<SQL
SELECT
	iImageId as imageId,
	iIsPublic as isPublic
FROM
	COM_IMAGES
WHERE
	iCommissionId IN (
		SELECT
			iCommissionId
		FROM
			COM_COMMISSION
		WHERE
			iCommissionerId = ?
	)
SQL;
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $_SESSION['userObj']->getUserId());
		$runQuery->execute();
		$results = $runQuery->fetchAll(PDO::FETCH_ASSOC);
		$images = array();
		foreach($results as $key=>$val) {
			$images[$val['imageId']] = $val['isPublic'];
		}
		foreach($_POST as $key=>$val) {
			if( isset($images[$key]) && $images[$key] !== $val ) { //If there's a change
				$query = <<<SQL
UPDATE
	COM_IMAGES
SET
	iIsPublic = ?,
	dModifiedDate = NOW()
WHERE
	iImageId = ? AND
	iCommissionId IN (
		SELECT
			iCommissionId
		FROM
			COM_COMMISSION
		WHERE
			iCommissionerId = ?
	)
SQL;
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $val);
				$runQuery->bindParam(2, $key);
				$runQuery->bindParam(3, $_SESSION['userObj']->getUserId());
				$runQuery->execute();
			}
		}
		header('Location: '.$_SERVER['REQUEST_URI']); //Redirect to same page after update to stop page submit on F5
	}
	if( isset($_GET['u']) && isset($_GET['a']) && (!isset($_SESSION['userObj']) || ($_SESSION['userObj']->getUserId() !== $_GET['u'])) ) //Make sure the ids are the same
		header('Location: '.$_SERVER['PHP_SELF'].'?u='.$_GET['u']); //Route to same page without admin flag
	if( isset($_GET['c']) && isset($_SESSION['userObj'])) {
		$commission = new commission($_GET['c']);
		if( $commission->clientId !== $_SESSION['userObj']->getUserId() && $commission->commissionerId !== $_SESSION['userObj']->getUserId() ) {
			$badUserId = true;
			$errMsg = 'You do not have permission to view this gallery.';
		}
	}
	?>
<!DOCTYPE html>
<html>
	<head>
		<title>Commissioner - Gallery</title>
	</head>
	<body>
		<center>
			<?php
				if( isset($_SESSION['userObj'])  ) {
					$links = new links($_SESSION['userObj']);
					echo $links->getLinks();
				}
				if( isset($_GET['u']) && !isset($_GET['a'])) { //Public non-admin gallery
					$query = <<<SQL
SELECT
	iImageId as imageId,
	cLocation as location
FROM
	COM_IMAGES
WHERE
	iUserId = ? AND
	iIsPublic = 1
ORDER BY
	imageId DESC
SQL;
					$runQuery = $dbh->prepare($query);
					$runQuery->bindParam(1, $_GET['u']);
					$runQuery->execute();
					$results = $runQuery->fetchall(PDO::FETCH_ASSOC);
					$images = array();
					foreach( $results as $key=>$val ) {
						$images[$val['imageId']] = $val['location'];
					}
					$imagesLen = count($images);
					$rowCount = 0;
					if($imagesLen > 0) {
						echo '<table border="1">';
						foreach($images as $key=>$val) {
							if( $rowCount === 4 )
								$rowCount = 0;
							if( $rowCount === 0 )
								echo '<tr>';
							$imageName = str_replace($env['IMAGE_LIB'], '', $val);
							$imagePath = 'image.php?n='.$imageName;
							echo '<td><a href="'.$imagePath.'">'.generateImage($val, $imageSize).'</a></td>';
							if( $rowCount === 3 )
								echo '</tr>';
							$rowCount++;
						}
						echo '</table>';
					}
					else
						$errMsg = 'Gallery is empty.';
				}
				elseif( isset($_GET['u']) && isset($_GET['a']) ) { //public admin gallery
					$query = <<<SQL
SELECT
	iCommissionId as commissionId
FROM
	COM_COMMISSION
WHERE
	iCommissionerId = ?
ORDER BY
	commissionId DESC
SQL;
					$runQuery = $dbh->prepare($query);
					$runQuery->bindParam(1, $_SESSION['userObj']->getUserId());
					$runQuery->execute();
					$results = $runQuery->fetchall(PDO::FETCH_ASSOC);
					$commissions = array();
					$radioStr = array();
					echo '<form action="'.htmlentities($_SERVER['REQUEST_URI']).'" method="POST">';
					foreach($results as $keyA=>$valA) { //For each commission
						$commissions[$keyA] = new commission($valA['commissionId']);
						$images[$keyA] = $commissions[$keyA]->images;
						$publicImages[$keyA] = $commissions[$keyA]->publicImages;
						$radioStr[$keyA] = '';
						if( count($images[$keyA]) > 0 ) {
							echo '<b>'.$commissions[$keyA]->title.'</b><br>';
							echo '<table border="1"><tr>';
							foreach( $images[$keyA] as $keyB=>$valB) { //For each image within commission
								$imageName = str_replace($env['IMAGE_LIB'], '', $valB);
								$imagePath = 'image.php?n='.$imageName;
								echo '<td><a href="'.$imagePath.'">'.generateImage($valB, $imageSize).'</a></td>';
								$radioStr[$keyA] .= '<td>';
								$radioStr[$keyA] .= '<label><input type="radio" name="'.$keyB.'" value="1"'.(isset($publicImages[$keyA][$keyB])?' checked="checked"':'').'>Public</label><br>';
								$radioStr[$keyA] .= '<label><input type="radio" name="'.$keyB.'" value="0"'.(!isset($publicImages[$keyA][$keyB])?' checked="checked"':'').'>Private</label>';
								$radioStr[$keyA] .= '</td>';
							}
							echo '</tr><tr>'.$radioStr[$keyA];
							echo '</tr></table><br>';
						}
					}
					if( !empty($images) )
						echo '<button onClick="form.submit()">Update</button>'; //Use button to stop submit from appearing in $_POST
					echo '</form>';
				}
				elseif( isset($_GET['c']) && !isset($badUserId) ) { //Commission gallery
					if( count($commission->images) > 0 ) { //Commission has images attached
						$images = $commission->images;
						$imagesLen = count($images);
						$rowCount = 0;
						echo '<table border="1">';
						foreach($images as $key=>$val) {
							if( $rowCount === 4 )
								$rowCount = 0;
							if( $rowCount === 0 )
								echo '<tr>';
							$imageName = str_replace($env['IMAGE_LIB'], '', $val);
							$imagePath = 'image.php?n='.$imageName;
							echo '<td><a href="'.$imagePath.'">'.generateImage($val, $imageSize).'</a></td>';
							if( $rowCount === 3 )
								echo '</tr>';
							$rowCount++;
						}
						echo '</table>';
					}
					else {
						$errMsg = 'Commission gallery is empty.';
					}
				}
				elseif( empty($_GET) ){ //Show public gallery list
					//Select all 'commissioner' type accounts with public images
					$query = <<<SQL
SELECT
	a.email,
	a.userId
FROM (
	SELECT
		cEmail as email,
		iUserId as userId,
		(
			SELECT
				count(iImageId)
			FROM
				COM_IMAGES
			WHERE
				iUserId = cu.iUserId AND
				iIsPublic = 1
		) as publicImageCount
	FROM
		COM_USER cu
	WHERE
		cu.cUserType = 'commissioner'
SQL;
					if(isset( $env['SHOW_SUPERUSER_GALLERY'] ) && $env['SHOW_SUPERUSER_GALLERY'] === '1') { //If we need to show superuser in the gallery list
						$query .= <<<SQL

OR cu.cUserType = 'superuser'
SQL;
					}
					$query .= <<<SQL
) a
WHERE
	a.publicImageCount > 0
SQL;
					$runQuery = $dbh->prepare($query);
					$runQuery->execute();
					$results = $runQuery->fetchall(PDO::FETCH_ASSOC);
					foreach($results as $key=>$val) {
						echo '<a href="gallery.php?u='.$val['userId'].'">'.$val['email'].'</a><br>';
					}
				}
				if( isset($errMsg) )
					echo '<font color="#FF0000">'.$errMsg.'</font>';
			?>
		</center>
	</body>
</html>

<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/user.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/links.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/class/commission.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/dbh.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/requireLogin.php'; //Checks login and starts session
	requireLogin();
	/*
	*
	*	NOTE: WE NEED TO ADD MORE SECURITY TO THIS PAGE
	*	AS OF NOW, RANDOM PEOPLE CAN SEE OTHER COMMISSION GALLERIES
	*	BY MODIFYING URL
	*
	*/
	function generateImage($img = null, $targetDim = null)
	{
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
	if( empty($_GET['c']) )
		header('Location: /');
	if($_SESSION['userObj']->getUserType() === 'superuser' || $_SESSION['userObj']->getUserType() === 'commissioner') {
		?>
<!DOCTYPE html>
<html>
	<head>
		<title>Commissioner - Gallery</title>
	</head>
	<body>
		<center>
			<?php
				$links = new links($_SESSION['userObj']);
				echo $links->getLinks();
				$commission = new commission($_GET['c']);
				$images = $commission->images;
				$imagesLen = count($images);
				$rowCount = 0;
				echo '<table border="1">';
				for($a=0;$a<$imagesLen;$a++) {
					if( $rowCount === 4 )
						$rowCount = 0;
					if( $rowCount === 0 )
						echo '<tr>';
					$imageName = str_replace($env['IMAGE_LIB'], '', $images[$a]);
					$imagePath = 'image.php?n='.$imageName;
					echo '<td><a href="'.$imagePath.'">'.generateImage($images[$a], 100).'</a></td>';
					if( $rowCount === 3 )
						echo '</tr>';
					$rowCount++;
				}
				echo '</table>';
			?>
		</center>
	</body>
</html>
		<?php
	}
	elseif($_SESSION['userObj']->getUserType() === 'client') {
		//Handle here
	}
?>
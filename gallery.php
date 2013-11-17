<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
	function generateImage($img = null, $targetDim = null, $gallery = '')
	{
		if($gallery == 'gallery')
		{
			$targetImg = $_SERVER['DOCUMENT_ROOT'].'/gallery/'.$img;
			$baseDir = 'gallery/'.$img;
		}
		elseif($gallery == 'nogallery')
		{
			$targetImg = $_SERVER['DOCUMENT_ROOT'].'/'.$img;
			$baseDir = $img;
		}
		if(!file_exists($baseDir))
		{
			globalIn('$baseDir', $baseDir);
			globalIn('$targetImg', $targetImg);
			if( empty($_SESSION['ENV']['DEVELOPMENT']) || $_SESSION['ENV']['DEVELOPMENT'] === '0' )
				writeLog('$targetImg could not be found', null, null);
			else
			{
				$targetImg = $_SERVER['DOCUMENT_ROOT'].'/images/placeholder.jpg';
				$img = '/images/placeholder.jpg';
				
			}
			//return false;
		}
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
		if($gallery == 'gallery')
		{
			return '<img width="'.$imageDim['width'].'" height="'.$imageDim['height'].'" src="image.php?c='.$img.'">';
		}
		elseif($gallery == 'nogallery')
		{
			return '<img width="'.$imageDim['width'].'" height="'.$imageDim['height'].'" src="'.$img.'">';
		}
		else
		{
			return false;
		}
	}
	function commissionView($commission = null)
	{
		global $dbh;
		echo '<center>';
		if(credentialCheck('client'))
			echoClientLinks();
		elseif(credentialCheck('user'))
			echoLinks();
		echo '<br>';
		echo '</center>';
		$targetDim = 150;
		$query = "
			SELECT
				CLOCALLOCATION
			FROM
				book_images
			WHERE
				IRECORDNUMBER = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, htmlentities($_GET['o']));
		$runQuery->execute();
		echo "\n";
		echo '<center>';
		echo "\n";
		$tmpQuery = "
			SELECT
				CLOCALLOCATION
			FROM
				book_images
			WHERE
				IRECORDNUMBER = ?
			LIMIT 0, 1
		";
		$tmpRunQuery = $dbh->prepare($tmpQuery);
		$tmpRunQuery->bindParam(1, htmlentities($_GET['o']));
		$tmpRunQuery->execute();
		$tmp = $tmpRunQuery->fetch(PDO::FETCH_BOTH);
		if($tmp == array())
		{
			echo 'This user hasn\'t setup their gallery.';
			echo "\n";
			echo "<br>\n";
			if( credentialCheck('client') || credentialCheck('user') )
				echo '<a href="userProfile.php">Back</a>';
			return false;
			exit;
		}
		echo '<table border="1">';
		echo "\n";
		echo '<tr>';
		echo "\n";
		$count = 0;
		while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
		{
			if($count >= 4)
			{
				echo '</tr><tr>';
		echo "\n";
				$count = 0;
			}
			$img = $runQueryArray['CLOCALLOCATION'];
			echo '<td><a href="image.php?c='.$img.'&g=1">'.generateImage($img, $targetDim, 'nogallery').'</a></td>';
		echo "\n";
			
			if($count >= 4)
			{
			//	echo '</tr>';
				
			}
			$count++;
		}
		echo '</tr>';
		echo "\n";
		echo '</table>';
		echo "\n";
		echo "<br>\n";
		global $_PAGE;
		if( isset($_PAGE['Last']) && credentialCheck('client') || credentialCheck('user'))
			echo '<a href="'.$_PAGE['Last'].'">Back</a>';
		elseif(credentialCheck('client') || credentialCheck('user'))
			echo '<a href="index.php">Back</a>';
		echo '</center>';
		echo "\n";
	}
	function clientView()
	{
		$userId = $_GET['u'];
		$targetDim = 150;
		global $dbh;
		echo '<center>';
		if(credentialCheck('client'))
			echoClientLinks();
		elseif(credentialCheck('user'))
			echoLinks();
		echo '<br>';
		if( credentialCheck('user') && $_GET['u'] == globalOut('user_key3') )
			echo "<a href=\"?switchView=true\">Admin View<a/><br />";
		$query = "
			SELECT
				count(IGALLERYID)
			FROM
				bi_gallery
			WHERE
				IUSERID = ? AND
				ISHOWPUBLIC = 1
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $userId);
		$runQuery->execute();
		$result = $runQuery->fetch(PDO::FETCH_BOTH);
		if( $result[0] < 1 )
			echo 'This commissioner hasn\'t setup their gallery';
		else
		{
			$query = "
				SELECT
					IIMAGELOCATION
				FROM
					bi_gallery
				WHERE
					ISHOWPUBLIC = 1 AND
					IUSERID = ?
			";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $_GET['u']);
			$runQuery->execute();
			$rowCount = 0;
			echo '<table border="1">';
			while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
			{
				$img = $runQueryArray['IIMAGELOCATION'];
				if( $rowCount === 4 )
					$rowCount = 0;
				if( $rowCount === 0 )
					echo '<tr>';
				echo '<td><a href="image.php?g=1&c='.$img.'">'.generateImage($img, $targetDim, 'nogallery').'</a></td>';
				if( $rowCount === 3 )
					echo '</tr>';
				$rowCount++;
			}
			echo '</table>';
		}
		echo '</center>';
	}
	function adminView()
	{
		global $dbh;
		$targetDim = 150;
		$query = "
			SELECT
				bi.IPHOTOID,
				bi.CLOCALLOCATION,
				bg.ISHOWPUBLIC
			FROM
				book_images bi
			INNER JOIN
				book_records br ON (br.IRECORDNUMBER = bi.IRECORDNUMBER)
			LEFT JOIN
				bi_gallery bg ON (bg.IPHOTOID = bi.IPHOTOID)
			WHERE
				br.IUSERID = ? AND
				bi.CLOCALLOCATION IS NOT NULL
			ORDER BY
				bi.IRECORDNUMBER DESC
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, globalOut('user_key3'));
		$runQuery->execute();
		echo '<center>';
		echoLinks();
		echo '<br>';
		echo "<a href=\"?switchView=true\">Client View<a/>";
		echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="post">';
		echo '<table border="1">';
		echo '<tr>';
		echo '<td>Image</td>';
		echo '<td>Status</td>';
		echo '<td>Make Private</td>';
		echo '<td>Make Public</td>';
		echo '</tr>';
		while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
		{
			$status = $runQueryArray['ISHOWPUBLIC'] != '1' ? 'Private' : 'Public';
			$iPhotoId = $runQueryArray['IPHOTOID'];
			echo '<tr>';
			echo '<td>'.generateImage($runQueryArray['CLOCALLOCATION'], $targetDim, 'nogallery').'</td>';
			echo '<td>'.$status.'</td>';
			if( $status === 'Private' )
				echo '<td></td><td><input type="checkbox" name="makePublic[]" value="'.$iPhotoId.'" /></td>';
			else
				echo '<td><input type="checkbox" name="makePrivate[]" value="'.$iPhotoId.'" /></td><td></td>';
			echo '</tr>';
		}
		echo '</table>';
		echo '<br />';
		echo '<input type="Submit" name="update" value="Update">';
		echo '</form>';
		echo '</center>';
	}
	if( isset($_GET['switchView']) )
	{
		if( globalOut('clientView') )
		{
			globalClear('clientView');
			header('Location: '.$_SERVER['PHP_SELF']);
		}
		else
		{
			globalIn('clientView', 'true');
			header('Location: '.$_SERVER['PHP_SELF'].'?u='.globalOut('user_key3'));
		}
	}
	if( credentialCheck('user') && !globalOut('clientView') && !isset($_GET['u']) && !isset($_GET['o']) )
	{
		if( isset( $_POST['update'] ) )
		{
			if( isset($_POST['makePublic']) || isset($_POST['makePrivate']) )
			{
				global $dbh;
				if( isset($_POST['makePublic']) )
				{
					foreach( $_POST['makePublic'] as $key=>$val )
					{
						$query = "
							SELECT
								count(IGALLERYID)
							FROM
								bi_gallery
							WHERE
								IPHOTOID = ?
						";
						$runQuery = $dbh->prepare($query);
						$runQuery->bindParam(1, $val);
						$runQuery->execute();
						$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
						if( $runQueryArray[0] < 1 )
						{
							$query = "
								INSERT INTO
									bi_gallery (
										IPHOTOID,
										IIMAGELOCATION,
										ISHOWPUBLIC,
										IUSERID
									)
									VALUES (
										?,
										(SELECT CLOCALLOCATION FROM book_images WHERE IPHOTOID = ?),
										1,
										?
									)
							";
							$runQuery = $dbh->prepare($query);
							$runQuery->bindParam(1, $val);
							$runQuery->bindParam(2, $val);
							$runQuery->bindParam(3, globalOut('user_key3'));
						}
						else
						{
							$query = "
								UPDATE
									bi_gallery
								SET
									ISHOWPUBLIC = 1
								WHERE
									IPHOTOID = ? AND
									IUSERID = ?
							";
							$runQuery = $dbh->prepare($query);
							$runQuery->bindParam(1, $val);
							$runQuery->bindParam(2, globalOut('user_key3'));
						}
						$runQuery->execute();
					}
				}
				if( isset($_POST['makePrivate']) )
				{
					foreach( $_POST['makePrivate'] as $key=>$val)
					{
						$query = "
							UPDATE
								bi_gallery
							SET
								ISHOWPUBLIC = 0
							WHERE
								IPHOTOID = ? AND
								IUSERID = ?
						";
						$runQuery = $dbh->prepare($query);
						$runQuery->bindParam(1, $val);
						$runQuery->bindParam(2, globalOut('user_key3'));
						$runQuery->execute();
					}
				}
			}
		}
		adminView();
	}
	elseif( ( (credentialCheck('client') || globalOut('clientView')) && isset($_GET['u']) ) || isset($_GET['u']) && !isset($_GET['o']) )
		clientView();
	elseif( isset($_GET['o']) )
	{
		commissionView($_GET['o']);
	}
	elseif( credentialCheck('user') )
	{
		header( 'Location: gallery.php?u='.globalOut('user_key3') );
	}
	elseif( credentialCheck('client') )
	{
		header( 'Location: galleryList.php' );
	}
	else
		echo 'You\'re not suppose to be here.';
?>
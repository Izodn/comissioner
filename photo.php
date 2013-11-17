<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
	if((!isset($_GET['IORDERNUMBER']) || $_GET['IORDERNUMBER'] == '') && (!isset($_POST['OrderNumber']) || $_POST['OrderNumber'] == ''))
	{
		die('You\'re not suppose to be here.');
	}
	if(isset($_POST['submit']))
	{
		global $dbh;
		if ((($_FILES["file"]["type"] == "image/jpeg")||($_FILES["file"]["type"] == "image/png")||($_FILES["file"]["type"] == "image/gif"))&&($_FILES["file"]["size"] < 20971520))
		{
			$userLocation = "clientSide/photos/".md5(globalOut('user_key3'))."/";
			$uploadLocation = $userLocation.md5($_POST['OrderNumber'])."/";
			if ($_FILES["file"]["error"] > 0)
			{
				echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
			}
			else
			{
				$queryfirst = "
				SELECT 
					bi.IIMAGECOUNT,
					br.IORDERNUMBER
				FROM 
					book_records br
				LEFT JOIN
					book_images bi ON (bi.IRECORDNUMBER = br.IRECORDNUMBER)
				WHERE 
					br.IORDERNUMBER= ? AND br.IUSERID = ?
				";
				$runQueryfirst = $dbh->prepare($queryfirst);
				$runQueryfirst->bindParam(1, $_POST['OrderNumber'], PDO::PARAM_STR, 225);
				$runQueryfirst->bindParam(2, globalOut('user_key3'), PDO::PARAM_STR, 225);
				$runQueryfirst->execute();
				$runQueryResultfirst = $runQueryfirst->fetch(PDO::FETCH_BOTH);
				if(safe($runQueryResultfirst[1]) == $_POST['OrderNumber'])
				{
					$imageCount = $runQueryResultfirst[0];
					$imageCount++;
				}
				else
				{
					die('That Order Number doesn\'t exist');
				}

				if (file_exists($uploadLocation . $_FILES["file"]["name"]))
				{
					//unlink($uploadLocation . $_FILES["file"]["name"]); //WE USED TO OVERWRITE THE FILE
					while( empty($stop) ) //ESSENTIALLY ADD A RANDOM NUMBER INTO THE MIX UNTIL THE FILE ISN'T FOUND
					{
						$filler = rand(0, 10000);
						if( !file_exists($uploadLocation.$filler.$_FILES["file"]["name"]) )
						{
							$_FILES["file"]["name"] = $filler.$_FILES["file"]["name"];
							$stop = true;
						}
					}
				}
				$userLocation = "clientSide/photos/".md5(globalOut('user_key3'))."/";
				if(!file_exists($userLocation))
				{
					mkdir($userLocation, 0777);
				}
				if(!file_exists($uploadLocation))
				{
					mkdir($uploadLocation, 0777);
				}
				$ie = $_FILES["file"]["name"];
				$ie = $_FILES["file"]["name"];
				move_uploaded_file($_FILES["file"]["tmp_name"], $uploadLocation . $_FILES["file"]["name"]);
				$ourFileName = $uploadLocation."/index.html";
				$ourFileHandle = fopen($ourFileName, 'w') or die("can't open file");
				fclose($ourFileHandle);
				$ourFileName = $userLocation."/index.html";
				$ourFileHandle = fopen($ourFileName, 'w') or die("can't open file");
				fclose($ourFileHandle);
				$remoteLocation = '<a href="'.$uploadLocation.$ie.'">'.$ie.'</a>';
				$photoDescription = $_POST['photoDescription'];
				$query = "
					INSERT INTO
						book_images (
							IRECORDNUMBER,
							CLOCALLOCATION,
							CREMOTELOCATION,
							CIMAGEDESCRIPTION,
							IIMAGECOUNT
						)
						VALUES (
							(
								SELECT DISTINCT
									IRECORDNUMBER
								FROM
									book_records
								WHERE
									IORDERNUMBER = ? AND
									IUSERID = ?
							),
							?,
							?,
							?,
							?
						)
				";
				$recordQuery = $dbh->prepare($query);
				$uploadLocation2 = $uploadLocation.$ie;
				$recordQuery->bindParam(1, $_POST['OrderNumber'], PDO::PARAM_STR, 225);
				$recordQuery->bindParam(2, globalOut('user_key3'), PDO::PARAM_STR, 225);
				$recordQuery->bindParam(3, $uploadLocation2, PDO::PARAM_STR, 225);
				$recordQuery->bindParam(4, $remoteLocation, PDO::PARAM_STR, 225);
				$recordQuery->bindParam(5, $photoDescription, PDO::PARAM_STR, 225);
				$recordQuery->bindParam(6, $imageCount, PDO::PARAM_STR, 225);
				$recordQuery->execute();
				$_POST['success'] = '1';
				$_POST['url'] = '<a href="clientprofile.php?IORDERNUMBER='.$_POST['OrderNumber'].'">Click Here</a> to view profile';
			}
		}
	}
	echo '<center>';
	echoLinks();
?>
	<form action="<?php echo htmlentities($_SERVER['PHP_SELF'].'?IORDERNUMBER='.$_GET['IORDERNUMBER']) ?>" method="post" enctype="multipart/form-data">
	<table>
		<br>
		<tr>
			<td></td><td><input type="hidden" name="OrderNumber" value='<?php if(isset($_GET['IORDERNUMBER'])){ echo $_GET['IORDERNUMBER'];} ?>' size='10' maxlength="12"></td>
		<tr>
		</tr>
			<td>Description of Photo:</td><td><textarea cols="26" rows="5" name="photoDescription"></textarea></td>
		</tr>
		</tr>
			<td colspan='2'><input type="file" name="file" id="file" /></td>
		<tr>
	</table>
	<input type="submit" name="submit" value="Submit" />
</form>
<?php
	if(isset($_POST['success']) && $_POST['success'] = '1')
	{
		echo '<font color="red"><h3>SUCCESS!</h3></font>';
		echo $_POST['url'];
	}
	echo '</center>';
?>
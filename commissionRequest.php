<?php 
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
	global $dbh;
	if((isset($_POST['submit']) && isset($_POST['Description']) && isset($_POST['Title'])) && ($_POST['Description'] != '' && $_POST['Title'] != ''))
	{
		$query = "
			INSERT INTO
				com_request
			(ICLIENTID, CREQUESTTITLE, CDESCRIPTION, CRESPONCE, CRESPONCESTATUS, ICREATEDTIME, IMODIFIEDTIME, IUSERID)
			VALUES
			(?, ?, ?, 'Created', 'Awaiting responce', NOW(), NOW(),
				(
					SELECT
						IUSERID
					FROM
						user_detail
					WHERE
						CUSERNAMEDETAIL = ?
				)
			)
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, globalOut('user_key2'), PDO::PARAM_STR, 225);
		$runQuery->bindParam(2, $_POST['Title'], PDO::PARAM_STR, 225);
		$runQuery->bindParam(3, $_POST['Description'], PDO::PARAM_STR, 225);
		$runQuery->bindParam(4, $_POST['Commissioner'], PDO::PARAM_STR, 225);
		$runQuery->execute();
		redirectHandler();
	}
	if( isset($_POST['submit']) && ((!isset($_POST['Description']) || safe($_POST['Description']) == '') || (!isset($_POST['Title']) || safe($_POST['Title']) == '')))
	{
		
		if((!isset($_POST['Description']) && !isset($_POST['Title'])) || (safe($_POST['Description']) == '' && safe($_POST['Title']) == '') )
		{
			$fail = "Title and Description";
		}
		elseif(!isset($_POST['Description']) || safe($_POST['Description']) == '')
		{
			$fail = "Description";
		}
		elseif(!isset($_POST['Title']) || safe($_POST['Title']) == '')
		{
			$fail = "Title";
		}
	}
	if(successMsg())
	{
		$success = 'Success';
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>BookKeeping</title>
	</head>
	<body>
		<center>
			<?php
				echoClientLinks();
			?>
			<form action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>" method="post">
				<br>
				<table>
					<tr>
					</tr>
					<?php
						$query = "
							SELECT DISTINCT
								ud.CUSERNAMEDETAIL,
								ud.IUSERID
							FROM
								user_detail ud
							INNER JOIN
								user_account ua ON (ua.IUSERID = ud.IUSERID)
							WHERE
								ua.IISACTIVE = '1'
						";
						$runQuery = $dbh->prepare($query);
						$runQuery->execute();
						echo '<tr>';
						echo '<td>Commissioner:</td>';
						echo '<td>';
						$select = '';
						if(isset($_GET['c']))
						{
							$query2 = "
								SELECT
									CUSERNAMEDETAIL
								FROM
									user_detail
								WHERE
									IUSERIDMD5 = ?
							";
							$runQuery2 = $dbh->prepare($query2);
							$runQuery2->bindParam(1, $_GET['c'], PDO::PARAM_STR, 225);
							$runQuery2->execute();
							$runQueryArray2 = $runQuery2->fetch(PDO::FETCH_BOTH);
							echo '<input type="hidden" name="Commissioner" value = "'.$runQueryArray2[0].'">';
							echo '<select disabled>';
						}
						else
						{
							echo '<select name="Commissioner">';
						}
						while( ($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH)))
						{
							if(isset($_GET['c']))
							{
								if(md5($runQueryArray[1]) == $_GET['c'])
								{
									$select = 'selected';
								}
							}
							echo '<option '.$select.' value="'.safe($runQueryArray[0]).'">'.safe($runQueryArray[0]).'</option>';
							$select = '';
						}
						echo '</select>';
						echo '</td>';
						echo '</tr>';
					?>
					<tr>
						<td>Commission Title:</td><td><input type="text" name="Title" size='32' maxlength="30"></td>
					</tr>
					<tr>
						<td>Commission Description:</td><td><textarea cols='32'rows="12" name="Description"></textarea></td>
					</tr>
					<tr>
						<td colspan=2>Please enter your requested commission by discription (ie. Style, colors, shading, backgrounds, etc).</td>
					</tr>
					<tr>
						<td></td><td><input type="submit" name="submit" value="Submit"></td>
						
					</tr>
					<?php
						if(isset($success))
						{
							echo '<tr>';
							echo '<td colspan="2"><center><font color="red">Success</font></center></td>';
							echo '</tr>';
						}
						if(isset($fail))
						{
							
							echo '<tr>';
							echo '<td colspan="2"><center><font color="red">Please fill out the '.$fail.'.</font></center></td>';
							echo '</tr>';
						}
					?>
				</table>
			</form>
		</center>
	</body>
</html>
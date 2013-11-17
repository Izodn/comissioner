<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
	global $_PAGE;
	echo '<center>';
	echoClientLinks();
	echo '<br>';
	if(isset($_GET['u']))
	{
		echo '<table border="1">';
		echo '<tr>';
		echo '<td>Finished Commissions</td><td>Unfinished Commissions</td><td>Canceled Commissions</td><td>Request Commission</td><td>Public Gallery</td>';
		echo '</tr>';
		renderUserProfile();
		echo '</table>';
		echo '<a href="'.$_PAGE['Last'].'">Back</a>';
	}
	elseif(isset($_POST['c']))
	{
		header('Location: commissionRequest.php?c='.$_POST['c']);
	}
	elseif(isset($_POST['g']))
	{
		header('Location: gallery.php?u='.$_POST['g']);
	}
	else
	{
		global $dbh;
		$query = "
			SELECT
				CUSERNAMEDETAIL,
				IUSERID
			FROM
				user_detail
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->execute();
		echo 'Commissioner Profiles';
		echo '<form name="" action="'.htmlentities($_SERVER['PHP_SELF']).'" method="GET">';
		echo '<select name="u">';
		while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
		{
			echo '<option value="'.md5($runQueryArray[1]).'">'.$runQueryArray[0].'</option>';
		}
		echo '</select>';
		echo '<input type="submit" name="submit" value="View">';
		echo '</form>';
	}
	echo '</center>';
?>
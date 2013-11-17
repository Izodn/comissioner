<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
	function defaultView()
	{
		echo "<center>\n";
		if(credentialCheck('user'))
			echoLinks();
		elseif(credentialCheck('client'))
			echoClientLinks();
		echo "<br />\n";
		global $dbh;
		$query = "
			SELECT DISTINCT
				g.IUSERID,
				d.CUSERNAMEDETAIL
			FROM
				bi_gallery g
			INNER JOIN
				user_detail d ON (d.IUSERID = g.IUSERID)
			WHERE
				g.ISHOWPUBLIC = 1
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->execute();
		$galleryList = array();
		while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
		{
			$galleryList[$runQueryArray['CUSERNAMEDETAIL']] = $runQueryArray['IUSERID'];
		}
		foreach($galleryList as $key => $value)
		{
			echo '<a href="gallery.php?u='.$value.'">'.$key.'</a><br>';
		}
		echo "</center>\n";
	}
	if(credentialCheck('user'))
	{
		echo defaultView();
	}
	elseif(credentialCheck('client'))
	{
		echo defaultView();	
	}
	/*
	else
	{
		echo 'To view this page you must login.';
	}
	*/
?>
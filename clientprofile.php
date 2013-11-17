<?php
	if(isset($_GET['clientview']))
	{
		global $dbh;
		require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
		if(globalOut('user_key3'))
		{
			$query = "
				SELECT
					IORDERNUMBER
				FROM
					book_records
				WHERE
					IORDERNUMBERMD5 = ? AND
					IUSERIDMD5 = ?
			";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $_GET['IORDERNUMBER'], PDO::PARAM_STR, 225);
			$runQuery->bindParam(2, $_GET['clientview'], PDO::PARAM_STR, 225);
			$runQuery->execute();
			$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
			header('Location: clientprofile.php?IORDERNUMBER='.$runQueryArray[0]);
			
		}
		elseif(globalOut('users') && globalOut('user_key1') && globalOut('user_key2') )
		{
			successMsg();
			if(isset($_POST['submit']) && isset($_POST['Comment']) && $_POST['Comment'] != '')
			{
				$query = "
					INSERT INTO
						com_comment
					(ICLIENTID, ICOMMENT, ICREATEDTIME, IRECORDNUMBER)
					VALUES
					(
						(
							SELECT 
								ICLIENTID 
							FROM 	
								book_records
							WHERE 
								IORDERNUMBERMD5 = ? AND
								IUSERIDMD5 = ?
						),
						?,
						NOW(),
						(
							SELECT
								IRECORDNUMBER
							FROM
								book_records
							WHERE
								IORDERNUMBERMD5 = ? AND
								IUSERIDMD5 = ?
						)
					)
				";
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $_GET['IORDERNUMBER'], PDO::PARAM_STR, 225);
				$runQuery->bindParam(2, $_GET['clientview'], PDO::PARAM_STR, 225);
				$runQuery->bindParam(3, $_POST['Comment'], PDO::PARAM_STR, 225);
				$runQuery->bindParam(4, $_GET['IORDERNUMBER'], PDO::PARAM_STR, 225);
				$runQuery->bindParam(5, $_GET['clientview'], PDO::PARAM_STR, 225);
				$runQuery->execute();
				redirectHandler();
			}
			elseif(isset($_POST['submit']) && ( !isset($_POST['Comment']) || $_POST['Comment'] == ''))
			{
				die('No comment');
			}
			$query = "
				SELECT
					CUSERNAME,
					CPASSWORD,
					ICLIENTID
				FROM
					client_account
				WHERE
					CUSERNAME = ? AND
					CPASSWORD = ? AND
					ICLIENTID = ?
			";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, globalOut('users'), PDO::PARAM_STR, 225);
			$runQuery->bindParam(2, globalOut('user_key1'), PDO::PARAM_STR, 225);
			$runQuery->bindParam(3, globalOut('user_key2'), PDO::PARAM_STR, 225);
			$runQuery->execute();
			$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
			if( safe($runQueryArray['CUSERNAME']) != globalOut('users') || safe($runQueryArray['CPASSWORD']) != globalOut('user_key1') || safe($runQueryArray['ICLIENTID']) != globalOut('user_key2') )
			{
				header('Location: index.php');
			}	
			echo '<center>';
			echoClientLinks();
			echo '<br>';
			$query = "
				SELECT
					ca.CUSERNAME
				FROM
					client_account ca
				INNER JOIN
					book_records br ON (br.ICLIENTID = ca.ICLIENTID)
				WHERE
					br.IORDERNUMBERMD5 = ? AND
					br.IUSERIDMD5 = ?
			";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $_GET['IORDERNUMBER'], PDO::PARAM_STR, 225);
			$runQuery->bindParam(2, $_GET['clientview'], PDO::PARAM_STR, 225);
			$runQuery->execute();
			$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
			if( safe($runQueryArray['CUSERNAME']) == globalOut('users') )
			{
				renderClientProfile();
				echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'?IORDERNUMBER='.$_GET['IORDERNUMBER'].'&clientview='.$_GET['clientview'].'" method="POST">';
				echo '<table>';
				echo '<br>Comments:';
				$query = "
					SELECT
						cc.ICOMMENT,
						cc.ICREATEDTIME,
						cc.IUSERID
					FROM
						com_comment cc
					INNER JOIN
						book_records br ON (br.IRECORDNUMBER = cc.IRECORDNUMBER)
					WHERE
						br.IUSERIDMD5 = ? AND
						br.IORDERNUMBERMD5 = ?
					ORDER BY
						cc.ICOMMENTID DESC
				";
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $_GET['clientview'], PDO::PARAM_STR, 225);
				$runQuery->bindParam(2, $_GET['IORDERNUMBER'], PDO::PARAM_STR, 225);
				$runQuery->execute();
				while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
				{
					echo '<tr>';
					if(!safe($runQueryArray['IUSERID']))
					{
						echo '<td>You: </td>';
					}
					else
					{
						echo '<td>Commissioner: </td>';
					}
					echo '<td>'.safe($runQueryArray['ICREATEDTIME']).': </td>';
					echo '<td>'.safe($runQueryArray['ICOMMENT']).'</td>';
					echo '</tr>';
				}
				
				echo '</table>';
				echo '<table>';
				echo '<td><input type="text" name="Comment" size="32" maxlength="225">';
				echo '<br><input type="submit" name="submit" value="submit"';
				echo '</table>';
				echo '</form>';	
				echo '</center>';
			}
			else
			{
				die('You\'re logged in as the wrong client to view this.');
			}
		}
		else
		{
			echo '<center>';
			renderClientProfile();
			echo '</center>';
		}
	}
	else
	{	
		require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
		successMsg();
		global $dbh;
		if(!globalOut('user_key3'))
		{
			header('Location: /index.php');
		}
		if(isset($_POST['submit']) && isset($_POST['Comment']))
		{
			$query = "
				INSERT INTO
					com_comment
				(ICLIENTID, ICOMMENT, ICREATEDTIME, IRECORDNUMBER, IUSERID)
				VALUES
				(
					(
						SELECT 
							ICLIENTID 
						FROM 	
							book_records
						WHERE 
							IORDERNUMBER = ? AND
							IUSERID = ?
					),
					?,
					NOW(),
					(
						SELECT
							IRECORDNUMBER
						FROM
							book_records
						WHERE
							IORDERNUMBER = ? AND
							IUSERID = ?
					),
					?
				)
			";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $_GET['IORDERNUMBER'], PDO::PARAM_STR, 225);
			$runQuery->bindParam(2, $_GET['user_key3'], PDO::PARAM_STR, 225);
			$runQuery->bindParam(3, $_POST['Comment'], PDO::PARAM_STR, 225);
			$runQuery->bindParam(4, $_GET['IORDERNUMBER'], PDO::PARAM_STR, 225);
			$runQuery->bindParam(5, globalOut('user_key3'), PDO::PARAM_STR, 225);
			$runQuery->bindParam(6, globalOut('user_key3'), PDO::PARAM_STR, 225);
			$runQuery->execute();
			redirectHandler();
		}
		elseif(isset($_POST['submit']) && !isset($_POST['Comment']))
		{
			die('No comment');
		}
		echo '<center>';
		echoLinks();
		echo '<br>';
		profileRender();

		echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'?IORDERNUMBER='.$_GET['IORDERNUMBER'].'" method="POST">';
		echo '<table>';
		echo '<br>Comments:';
		$query = "
			SELECT
				cc.ICOMMENT,
				cc.ICREATEDTIME,
				cc.IUSERID
			FROM
				com_comment cc
			INNER JOIN
				book_records br ON (br.IRECORDNUMBER = cc.IRECORDNUMBER)
			WHERE
				br.IUSERID = ? AND
				br.IORDERNUMBER = ?
			ORDER BY
				cc.ICOMMENTID DESC
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
		$runQuery->bindParam(2, $_GET['IORDERNUMBER'], PDO::PARAM_STR, 225);
		$runQuery->execute();
		while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
		{
			echo '<tr>';
			if(!safe($runQueryArray['IUSERID']))
			{
				echo '<td>Client: </td>';
			}
			else
			{
				echo '<td>You: </td>';
			}
			echo '<td>'.safe($runQueryArray['ICREATEDTIME']).': </td>';
			echo '<td>'.safe($runQueryArray['ICOMMENT']).'</td>';
			echo '</tr>';
		}
		
		echo '</table>';
		echo '<table>';
		echo '<td><input type="text" name="Comment" size="32" maxlength="225">';
		echo '<br><input type="submit" name="submit" value="submit"';
		echo '</table>';
		echo '</form>';	
		echo '</center>';

	}
?>
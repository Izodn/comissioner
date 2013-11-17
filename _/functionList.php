<?php
	function tableBuilder($query = '', $binds = array(), $results = 10, $header = array(), $display = array(), $style = 'border="1"')
	{
		/*
		*	AUTHOR: BRANDON BURTON	
		*	CREATED DATE: 06/13/2013
		*	SYNTAX:
		*
			$dsn = 'mysql:dbname=DATABASE;host=HOST';
			$user = 'USERNAME';
			$password = 'PASSWORD';
			$dbh = new PDO($dsn, $user, $password);
			global $dbh;
			$query = "
				SELECT
					*
				FROM
					book_records
				WHERE
					IUSERID = ? AND
					CISDELETED != 'Checked'
			";	
			$binds = array(globalOut('user_key3'));
			$results = 10;
			$header = array('Title','First Name','Last Name','Description','Price','Account Type','Input Time','Procress','Payment');
			$display = array('CTITLE','CFIRSTNAME','CLASTNAME','DDESCRIPTION','IPRICE','CACCOUNTTYPE','CIMPUTTIME','CPROGRESSTYPE','CPAYMENTSTATUS');
			$table = tableBuilder($query, $binds, $results, $header, $display);
			echo $table;
		*/
		$table = null;
		global $dbh;
		$count = 0;
		$bindCount = 1;
		$runQuery = $dbh->prepare($query);
		foreach($binds as $a)
		{
			$runQuery->bindParam($bindCount, $binds[$count]);
			$count++;
		}
		$runQuery->execute();
		$table = $table.'<table '.$style.'>'."\n";
		echo '<tr>'."\n";
		foreach($header as $a)
		{
			$table = $table.'<td>'.$a.'</td>'."\n";
		}
		$table = $table.'</tr>'."\n";
		while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
		{
			$table = $table.'<tr>'."\n";
			foreach($display as $a)
			{
				$table = $table.'<td>'.$runQueryArray[$a].'</td>'."\n";
			}
			$table = $table.'</tr>'."\n";
		}
		return $table;
	}
	function contains($var = '', $var2 = '')
	{
		if(stripos($var, $var2) !== false)
		{
			return true;
		}
		return false;
	}
	function inArray($array = array(), $var = '')
	{
		/*	AUTHOR: BRANDON BURTON
		*	CREATED DATE: JUNE 12, 2013
		*	SYNTAX:
		*	if(inArray(array('foo', 'bar'), 'bar'))
		*	{
		*		echo 'True';
		*	}
		*	else
		*	{
		*		echo 'False';
		*	}
		*/
		if(getType($array) !== 'array' || $array == array())
		{
			writeLog('Function: inArray expected array', null);
			return false;
		}
		if((getType($var) !== 'string') || $var == '')
		{
			if(getType($var) == 'integer')
			{
				$var = ''.$var.'';
			}
			else
			{
				writeLog('Function: inArray expected string', null);
				return false;
			}
		}
		foreach($array as $a)
		{
			if(getType($a) == 'string')
			{
				if($a == $var)
				{
					return true;
				}
			}
			elseif(getType($a) == 'array')
			{
				if(inArray($a, $var))
				{
					return true;
				}
			}
			else
			{
				writeLog('$a contained a non-string non-array', null);
				return false;
			}
		}
		return false;
	}
	function tableNav($position = 'null', $type = 'null', $results = '')
	{
		$page = (isset($_GET['page']) && $_GET['page'] / 2 != $_GET['page']) ? $_GET['page'] : 0;
		$allowedPosition = array('start', 'end');
		$allowedType = array(
			'adminProgress', 		//0
			'adminTrash', 			//1
			'adminSearch', 			//2
			'adminPendingComm',		//3
			'clientComm', 			//4
			'clientPendingComm',	//5
			'adminIPIndex',			//6
			'adminErrorLogs'		//7
		);
		if(!inArray($allowedPosition, $position))
		{
			writeLog('$position not expect '.htmlentities($position).'', null);
			return false;
		}
		if(!inArray($allowedType, $type))
		{
			writeLog('$type not expect '.htmlentities($type).'', null);
			return false;
		}
		if($results != intval($results))
		{
			writeLog('$results not expect '.htmlentities($results).'', null);
			return false;
		}
		global $dbh;
		$tableNavStart = '< Previous';
		$tableNavSeperator = ' | ';
		$tableNavEnd = 'Next >';
		$from = null;
		$limit = (($page * $results) + $results) + 1;
		if($limit == 1)
		{
			$limit = 0;
		}
		$where = '1 = 1';
		$databaseLink = array(
			'book_records',	//0
			'book_records',	//1
			'book_records',	//2
			'com_request',	//3
			'book_records',	//4
			'com_request',	//5
			'ip_ban',		//6
			'errorLogs'		//7
		);
		$searchLink = '';
		if( (isset($_GET['FirstName']) && $_GET['FirstName'] ) || (isset($_GET['LastName']) && $_GET['LastName'] ) || (isset($_GET['Email']) && $_GET['Email'] ) )
		{
			if(isset($_GET['FirstName']) && $_GET['FirstName'] )
			{
				$searchLink = $searchLink.' CFIRSTNAME = ? AND';
			}
			if(isset($_GET['LastName']) && $_GET['LastName'] )
			{
				$searchLink = $searchLink.' CLASTNAME = ? AND';
			}
			if(isset($_GET['Email']) && $_GET['Email'] )
			{
				$searchLink = $searchLink.' CEMAIL = ? AND';
			}
			$searchLink = $searchLink." CISDELETED != 'Checked' AND";
		}
		$whereLink = array(
			"CISDELETED != 'Checked' AND", 																		//0
			"CISDELETED = 'Checked' AND", 																		//1
			$searchLink, 																						//2
			"CRESPONCE != 'Deleted' AND CRESPONCE != 'ClientDeleted' AND CRESPONCESTATUS != 'Declined' AND", 	//3
			"1 = 1 AND", 																						//4
			"CRESPONCE != 'ClientDeleted' AND", 																//5
			"1=1 OR",																							//6
			"IISARCHIVED IS NULL"																				//7
			);
		$count = 0;
		foreach($allowedType as $a)
		{
			if($a == $type)
			{
				$from = $databaseLink[$count];
				$where = $whereLink[$count];
			}
			$count++;
		}
		if(contains($type, 'admin'))
		{
			if(contains($type, 'errorLogs'))
			{
				$query = "
					SELECT
						*
					FROM
						".$from."
					WHERE
						".$where."
					LIMIT ".$limit.", 1
				";
			}
			else
			{
				$query = "
					SELECT
						*
					FROM
						".$from."
					WHERE
						".$where."
						IUSERID = ?
					LIMIT ".$limit.", 1
				";
			}
		}
		elseif(contains($type, 'client'))
		{
			$query = "
				SELECT
					*
				FROM
					".$from."
				WHERE
					".$where."
					ICLIENTID = ?
				LIMIT ".$limit.", 1
			";
		}
		$runQuery = $dbh->prepare($query);
		if( (isset($_GET['FirstName']) && $_GET['FirstName'] ) || (isset($_GET['LastName']) && $_GET['LastName'] ) || (isset($_GET['Email']) && $_GET['Email'] ) )
		{
			$count = 1;
			if(isset($_GET['FirstName']) && $_GET['FirstName'] )
			{
				$runQuery->bindParam($count, $_GET['FirstName']);
				$count++;
			}
			if(isset($_GET['LastName']) && $_GET['LastName'] )
			{
				$runQuery->bindParam($count, $_GET['LastName']);
				$count++;
			}
			if(isset($_GET['Email']) && $_GET['Email'] )
			{
				$runQuery->bindParam($count, $_GET['Email']);
				$count++;
			}
			$runQuery->bindParam($count, globalOut('user_key3'));
		}
		else
		{
			if(contains($type, 'admin'))
			{
				$runQuery->bindParam(1, globalOut('user_key3'));
			}
			else
			{
				$runQuery->bindParam(1, globalOut('user_key2'));
			}
		}
		$runQuery->execute();
		$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
		$buildMe = '?';
		$count = 0;
		if(isset($_GET) && $_GET)
		{
			$get = $_GET;
			foreach($get as $a)
			{
				if(key($get) == 'page')
				{
				}
				else
				{
					if($count == 0)
					{
						$buildMe =$buildMe.key($get).'='.$a;
					}
					else
					{
						$buildMe =$buildMe.'&'.key($get).'='.$a;
					}
					next($get);
				}
				$count++;
			}
		}
		if(isset($_GET) && $_GET)
		{
			$buildMe = $buildMe.'&page=';
		}
		else
		{
			$buildMe = $buildMe.'page=';
		}
		if($runQueryArray != array())
		{
			$tmp = $page + 1;
			$tableNavEnd = '<a href="'.$buildMe.$tmp.'">'.$tableNavEnd.'</a>';
		}
		if($page != 0)
		{
			$tmp = $page - 1;
			$tableNavStart = '<a href="'.$buildMe.''.$tmp.'">'.$tableNavStart.'</a>';
		}
		$resultList = array(
			'10',
			'20',
			'40',
			'50',
			'100'
		);
		$resultsPerPage = '
			<form name="" action="'.htmlentities($_SERVER['PHP_SELF']).'" method="GET">
				Results per page: 
				<select name="resultsPerPage" onChange="form.submit()">';
		$count = 0;
		foreach($resultList as $a)
		{
			if(isset($_GET['resultsPerPage']) && $_GET['resultsPerPage'])
			{
				if($_GET['resultsPerPage'] == $a)
				{
					$resultsPerPage = $resultsPerPage.'
						<option value="'.$a.'" selected="selected">'.$a.'</option>
					';
				}
				else
				{
					$resultsPerPage = $resultsPerPage.'
						<option value="'.$a.'">'.$a.'</option>
					';
				}
			}
			else
			{
				$resultsPerPage = $resultsPerPage.'
					<option value="'.$a.'">'.$a.'</option>
				';
			}
			$count++;
		}
		$resultsPerPage = $resultsPerPage.'
				</select>
			</form>
			<!--<br>-->';
		if($position == 'start')
		{
			$tableNav = $resultsPerPage;
			$tableNav = $tableNav.$tableNavStart.$tableNavSeperator.$tableNavEnd;
		}
		else
		{
			$tableNav = $tableNavStart.$tableNavSeperator.$tableNavEnd;
		}
		return $tableNav;
	}
	function loginCheck()
	{
		if(globalOut('users') && globalOut('user_key1') && globalOut('user_key2') && globalOut('user_key3'))
		{
			return true;
		}
		elseif(globalOut('users') && globalOut('user_key1') && globalOut('user_key2'))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	function searchBind()
	{
		global $a;
		$count = 0;
		foreach($a as $array)
		{
			if(isset($array) && $array != '')
			{
				$count++;
			}
		}
		$h = 0;
		foreach($a as $array)
		{
			if(!isset($count2))
			{
				if(isset($array) && $array != '')
				{
					$count2 = $h;
				}
				else
				{
					$h++;
				}
			}
			else
			{
				$h++;
			}
		}
		$count3 = 0;
		if($count > 1)
		{
			while($count3 < $count - 1)
			{
				if(isset($a[$count2]) && $a[$count2] != '')
				{
					$a[$count2] = $a[$count2].' AND ';
				}
				$count2++;
				$count3++;
			}
		}
		$b = '';
		$count = 0;
		foreach($a as $array)
		{
			
			if(isset($array) && $array != '')
			{
				$b = $b.$array;
			}
			$count++;
		}
		$fieldType = '';
		global $queryType;
		global $fieldArray;
		global $queryWhere;
		global $fieldWhere;
		global $joinType;
		global $joinWhere;
		global $whereClause;
		global $orderClause;
		foreach ($fieldArray as $f)
		{
			if(isset($f) && $f != '')
			{
				$fieldType = $fieldType.$f.' ';
			}
		}
		$query = '
			'.$queryType.' 
				'.$fieldType.'
			'.$queryWhere.' 
				'.$fieldWhere.'
			'.$joinType.'
				'.$joinWhere.'
			'.$whereClause.'
				'.$b.' AND
			br.IUSERID = ?
			'.$orderClause.'
		';
		return $query;
	}
	function redirectHandler($var)
	{
		if(isset($var) && $var)
		{
			globalIn('redirectHandler', $var);
			globalIn('success', '1');
			header('Location: redirectHandler.php');
		}
		else
		{
			$pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
			if ($_SERVER["SERVER_PORT"] != "80")
			{
				$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			} 
			else 
			{
				$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			}
			globalIn('redirectHandler', $pageURL);
			globalIn('success', '1');
			header('Location: redirectHandler.php');
		}
	}
	function successMsg()
	{
		if(globalOut('success') )
		{
			globalClear('success');
			return true;
		}
	}
	function echoLinks() //RENDER USER LINKS
	{
		echo '<a href="/progress.php">Commissions</a>'."\n";
		echo ' | '."\n";
		echo '<a href="/galleryList.php">Public Galleries</a>'."\n";
		echo ' | '."\n";
		echo '<a href="/settings.php">Settings</a>'."\n";
		echo ' | '."\n";
		echo '<a href="/reports.php">Reports</a>'."\n";
		if(globalOut('users') == md5('Izodn') || (globalOut('user_key4') && globalOut('user_key4') == md5('Izodn')))
		{
			echo ' | '."\n";
			echo '<a href="/userManagement.php">Admin Tools</a>'."\n";
		}
		echo ' | '."\n";
		echo '<a href="/login.php">Logout</a>'."\n";
		echo '<br>'."\n";
	}
	function echoCommissionLinks() //RENDER USER LINKS
	{
		echo '<a href="/adminIndex.php">Imput</a>'."\n";
		echo ' | '."\n";
		echo '<a href="/progress.php">Progress</a>'."\n";
		echo ' | '."\n";
		echo '<a href="/adminPendingCom.php">Pending Commission</a>'."\n";
		echo ' | '."\n";
		echo '<a href="/search.php">Search</a>'."\n";
		echo ' | '."\n";
		echo '<a href="/archive.php">Archive</a>'."\n";
		echo '<br>'."\n";
	}
	function echoAdminLinks() //RENDER SUPERUSER LINKS
	{
		if(globalOut('users') == md5('Izodn') || (globalOut('user_key4') && globalOut('user_key4') == md5('Izodn')))
		{
			global $phpMyAdminURL;
			echo '<a href="'.$phpMyAdminURL.'" target="blank">phpMyAdmin</a>'."\n";
			echo ' | '."\n";
			echo '<a href="/Register.php">Register</a>'."\n";
			echo ' | '."\n";
			echo '<a href="/userManagement.php">User Manage</a>'."\n";
			echo ' | '."\n";
			echo '<a href="/userDetail.php">Login As</a>'."\n";
			echo ' | '."\n";
			echo '<a href="/ipIndex.php">IP List</a>'."\n";
			echo ' | '."\n";
			echo '<a href="/errorLogList.php">Error Logs</a>'."\n";
			echo ' | '."\n";
			echo '<a href="/version.php">Version</a>'."\n";
			echo '<br>'."\n";
		}
		else
		{
			header('Location: login.php');
		}
	}
	function echoClientLinks() //RENDER CLIENT LINKS
	{
		echo '<a href="/index.php">Home</a>';
		echo ' | ';
		echo '<a href="/galleryList.php">Public Galleries</a>';
		echo ' | ';
		echo '<a href="/commissionRequest.php">Request A Commission</a>';
		echo ' | ';
		echo '<a href="/commissionPending.php">Pending Commissions</a>';
		echo ' | ';
		echo '<a href="/userProfile.php">Commissioner Profiles</a>';
		if(globalOut('user_key4') && globalOut('user_key4') == md5('Izodn'))
		{
			echo ' | ';
			echo '<a href="/userManagement.php">Admin Tools</a>';
		}
		echo ' | ';
		echo '<a href="/clientLogin.php">Logout</a>';
		echo '<br>';
	}
	function profileRender() //DISPLAY A COMMISSION PAGE FOR A SPECIFIC ORDERNUMBER
	{
		global $dbh;
		if(isset($_GET['IORDERNUMBER']))
		{
			$query = "SELECT IORDERNUMBER FROM book_records WHERE IORDERNUMBER= ? AND IUSERID = ?";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $_GET['IORDERNUMBER'], PDO::PARAM_STR, 225);
			$runQuery->bindParam(2, globalOut('user_key3'), PDO::PARAM_STR, 225);
			$runQuery->execute();
			$runQueryResult = $runQuery->fetch(PDO::FETCH_BOTH);
			if(safe($runQueryResult[0]) == $_GET['IORDERNUMBER'])
			{
				$IORDERNUMBER = $_GET['IORDERNUMBER'];
				$query = "
				SELECT DISTINCT 
					br.CTITLE,
					br.DDESCRIPTION,
					br.CFIRSTNAME,
					br.IPRICE,
					br.CACCOUNTTYPE,
					br.CIMPUTTIME,
					br.CPROGRESSTYPE,
					br.CPAYMENTSTATUS,
					bi.CREMOTELOCATION,
					bi.CIMAGEDESCRIPTION,
					bi.CLOCALLOCATION,
					bi.IRECORDNUMBER
				FROM 
					book_records br 
				LEFT JOIN book_images bi ON (bi.IRECORDNUMBER = br.IRECORDNUMBER)
				WHERE 
					br.IORDERNUMBER = ?  AND br.IUSERID = ?
				";
				$recordQuery = $dbh->prepare($query);
				$recordQuery->bindParam(1, $IORDERNUMBER, PDO::PARAM_STR, 225);
				$recordQuery->bindParam(2, globalOut('user_key3'), PDO::PARAM_STR, 225);
				$recordQuery->execute();
				$recordQueryResult = $recordQuery->fetch(PDO::FETCH_BOTH);
				$query = "
					SELECT
						CLASTNAME
					FROM
						book_records
					WHERE
						IORDERNUMBER = ? AND
						IUSERID = ?
				";
				$runQuery = $dbh->prepare($query);
				$runQuery->bindParam(1, $IORDERNUMBER, PDO::PARAM_STR, 225);
				$runQuery->bindParam(2, globalOut('user_key3'), PDO::PARAM_STR, 225);
				$runQuery->execute();
				$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
				$count = 0;
				$SQLColumns = array('Title','Description','Client Name','Price','Account Type','Input Time','Progress','Payment','Image Location');
				echo '<br>';
				echo '<table border="1">';
				while($count != 9)
				{
					
					echo '<tr>';
					echo '<td>'.$SQLColumns[$count].'</td>';
					if($SQLColumns[$count]=='Image Location' && safe($recordQueryResult[$count])!='')
					{
						echo '<td><a href="gallery.php?o='.$recordQueryResult['IRECORDNUMBER'].'">Visit Gallery</a><a href="photo.php?IORDERNUMBER='.$_GET['IORDERNUMBER'].'"><img src="images/upload.jpg" align="right"></a></td>';
					}
					elseif($SQLColumns[$count]=='Image Location' && safe($recordQueryResult[$count])=='')
					{
						echo '<td>None<a href="photo.php?IORDERNUMBER='.$_GET['IORDERNUMBER'].'"><img src="images/upload.jpg" align="right"></a></td>';
					}
					elseif($SQLColumns[$count]=='Client Name')
					{
						echo '<td>'.safe($recordQueryResult[$count]).' '.safe($runQueryArray[0]).'</td>';
					}
					elseif(!safe($recordQueryResult[$count]))
					{
						echo '<td>None</td>';
					}
					else
					{
						echo '<td>'.safe($recordQueryResult[$count]).'</td>';
					}
					echo '</tr>';
					$count++;
				}
				
					//REMOVED BY BRANDON 09/04/2013
				$clientPro=$_GET['IORDERNUMBER'];
				$clientPro=md5($clientPro);
				echo '<tr>';
				echo '<td>Client View</td><td><a href="clientprofile.php?IORDERNUMBER='.$clientPro.'&clientview='.md5(globalOut('user_key3')).'">Click Here</a></td>';
				echo '</tr>';
				
				echo '</table>';
			}
			else
			{
				die('That profile doesn\'t exist');
			}
		}
		else
		{
			die('You\'ve entered the wrong Profile Number');
		}
	}
	function renderClientProfile() //RENDER A COMMISSION PAGE BY ORDERNUMBERMD5 AND IUSERIDMD5 FOR CLIENT OR NON-LOGIN USERS
	{
		global $dbh;
		if(isset($_GET['IORDERNUMBER']) && isset($_GET['clientview']))
		{
			$query = "
			SELECT 
				IORDERNUMBERMD5,
				IORDERNUMBER
			FROM 
				book_records 
			WHERE 
				IORDERNUMBERMD5= ? AND 
				IUSERIDMD5 = ?
			";
			$runQuery = $dbh->prepare($query);
			$runQuery->bindParam(1, $_GET['IORDERNUMBER'], PDO::PARAM_STR, 225);
			$runQuery->bindParam(2, $_GET['clientview'], PDO::PARAM_STR, 225);
			$runQuery->execute();
			$runQueryResult = $runQuery->fetch(PDO::FETCH_BOTH);
			if(safe($runQueryResult['IORDERNUMBERMD5']) == $_GET['IORDERNUMBER'])
			{
				$IORDERNUMBER = $_GET['IORDERNUMBER'];
				$clientview = $_GET['clientview'];
				$query = "
				SELECT DISTINCT 
					ud.CUSERNAMEDETAIL,
					br.CTITLE,
					br.DDESCRIPTION,
					br.IPRICE,
					br.CIMPUTTIME,
					br.CPROGRESSTYPE,
					br.CPAYMENTSTATUS,
					bi.CREMOTELOCATION,
					bi.CIMAGEDESCRIPTION,
					br.ICLIENTID,
					ca.CPASSWORD,
					br.IUSERIDMD5
				FROM 
					book_records br 
				LEFT JOIN book_images bi ON (bi.IRECORDNUMBER = br.IRECORDNUMBER)
				INNER JOIN client_account ca ON (ca.ICLIENTID = br.ICLIENTID)
				INNER JOIN user_detail ud ON (ud.IUSERID = br.IUSERID)
				WHERE 
					br.IORDERNUMBERMD5 = ? AND 
					br.IUSERIDMD5 = ?
				";
				$recordQuery = $dbh->prepare($query);
				$recordQuery->bindParam(1, $IORDERNUMBER, PDO::PARAM_STR, 225);
				$recordQuery->bindParam(2, $clientview, PDO::PARAM_STR, 225);
				$recordQuery->execute();
				$recordQueryResult = $recordQuery->fetch(PDO::FETCH_BOTH);
				if(safe($recordQueryResult['CPASSWORD']) != '' && !globalOut('users'))
				{
					die('Please <a href="clientLogin.php">login</a> to view this profile');
				}
				$count = 0;
				$SQLColumns = array('Commissioner', 'Commission Title', 'Description','Price','Input Time','Progress','Payment','Uploaded Image');
				echo '<table border="1">';
				while($count != 8)
				{
					echo '<tr>';
					echo '<td>'.$SQLColumns[$count].'</td>';
					//START USER FRIENDLY FORMATTING
					if($SQLColumns[$count] == "Progress")
					{
						if(safe($recordQueryResult['CPROGRESSTYPE']) == '' || safe($recordQueryResult['CPROGRESSTYPE']) == 'NoProgress')
						{
							echo '<td>No Progress</td>';
						}
						elseif(safe($recordQueryResult['CPROGRESSTYPE']) == 'InProgress')
						{
							echo '<td>In Progress</td>';
						}
						else
						{
							echo '<td>'.safe($recordQueryResult[$count]).'</td>';
						}
					}
					elseif($SQLColumns[$count] == "Payment")
					{
						if(safe($recordQueryResult['CPAYMENTSTATUS']) == 'NoPayment')
						{
							echo '<td>No Payment</td>';
						}
						elseif(safe($recordQueryResult['CPAYMENTSTATUS']) == 'InProgress')
						{
							echo '<td>In Progress</td>';
						}
						else
						{
							echo '<td>'.safe($recordQueryResult[$count]).'</td>';
						}
					}
					elseif($SQLColumns[$count]=='Uploaded Image' && safe($recordQueryResult[$count])!='')
					{
						if(safe($recordQueryResult[$count])!='')
						{
							$query = "
								SELECT
									bi.IRECORDNUMBER
								FROM
									book_images bi
								INNER JOIN
									book_records br ON (br.IRECORDNUMBER = bi.IRECORDNUMBER)
								WHERE
									br.IORDERNUMBERMD5 = ? AND
									br.IUSERIDMD5 = ?
							";
							$runQuery = $dbh->prepare($query);
							$runQuery->bindParam(1, $_GET['IORDERNUMBER'], PDO::PARAM_STR, 225);
							$runQuery->bindParam(2, $_GET['clientview'], PDO::PARAM_STR, 225);
							$runQuery->execute();
							$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
							$a = $runQueryArray[0];
							echo '<td><a href="gallery.php?o='.$runQueryArray[0].'">Visit Gallery</a></td>';
						}
						elseif(safe($recordQueryResult[$count])=='')
						{
							echo '<td>None</td>';
						}
					}
					elseif($SQLColumns[$count]=='Commissioner')
					{
						if(loginCheck())
						{
							echo '<td><a href="userProfile.php?u='.$recordQueryResult['IUSERIDMD5'].'&submit=View">'.$recordQueryResult[$count].'</td>';
						}
						else
						{
							echo '<td>'.$recordQueryResult[$count].'</td>';
						}
					}
					elseif(!safe($recordQueryResult[$count]))
					{
						echo '<td>None</td>';
					}
					//STOP USER FRIENDLY FORMATTING
					else
					{
						echo '<td>'.safe($recordQueryResult[$count]).'</td>';
					}
					echo '</tr>';
					$count++;
				}
				echo '</table>';
				if(!safe($recordQueryResult[10]))
				{
					echo '<a href="clientRegister.php?IORDERNUMBER='.$_GET['IORDERNUMBER'].'&clientview='.$_GET['clientview'].'">Register Account</a>';
				}
			}
			else
			{
				die('That profile doesn\'t exist');
			}
		}
		else
		{
			die('You\'ve entered the wrong Profile Number');
		}
	}
	function echoSearch() //RENDER NEW TABLE WITH SEARCH RESULTS
	{
		global $dbh;
		$results = (isset($_GET['resultsPerPage']) && $_GET['resultsPerPage'] / 2 != $_GET['resultsPerPage']) ? $_GET['resultsPerPage'] : 10;
		$sth=$dbh->prepare(searchBind());
		$array = array();
		$get = array($_GET['FirstName'], $_GET['LastName'], $_GET['Email']);
		$count = 0;
		$type = '';
		foreach($get as $geta)
		{
			if(isset($geta) && $geta !== '')
			{
				$array[$count] = $geta;
				$type = $type.'s';
				$count++;
			}
		}
		$count = 0;
		$number=1;
		foreach($array as $a)
		{
			$sth->bindParam($number, $array[$count], PDO::PARAM_STR, 225);
			$number++;
			$count++;
		}
		$sth->bindParam($number, globalOut('user_key3'), PDO::PARAM_STR, 225);
		$sth->execute();
		echo '<table border="1">';
		echo '<tr>';
		echoTableHeader();
		echo '<td>Archive</td>';
		echo '</tr><tr>';
		$count=1;
		while($row = $sth->fetch(PDO::FETCH_BOTH))
		{	
			if ($row["CISDELETED"] != 'Checked')
			{
				$var = 'returnNumber'.$count;
				if(safe($row["CPROGRESSTYPE"])==""){$selected1 = "selected";} else{$selected1 = "";}
				if(safe($row["CPROGRESSTYPE"])=="Finished"){$selected2 = "selected";} else{$selected2 = "";}
				if(safe($row["CPROGRESSTYPE"])=="InProgress"){$selected3 = "selected";} else{$selected3 = "";}
				if(safe($row["CPROGRESSTYPE"])=="Canceled"){$selected4 = "selected";} else{$selected4 = "";}
				if(safe($row["CPAYMENTSTATUS"])=="" OR $row["CPAYMENTSTATUS"]=="NoPayment"){$selected5 = "selected";} else{$selected5 = "";}
				if(safe($row["CPAYMENTSTATUS"])=="Paid"){$selected6 = "selected";} else{$selected6 = "";}
				if(safe($row["CPAYMENTSTATUS"])=="InProgress"){$selected7 = "selected";} else{$selected7 = "";}
				if(safe($row["CPAYMENTSTATUS"])=="Canceled"){$selected8 = "selected";} else{$selected8 = "";}
				echo '<tr>';
				echo '<td><a href=\'clientprofile.php?IORDERNUMBER='.safe($row["IORDERNUMBER"]).'\'>'.safe($row["CTITLE"]).'</a></td>';
				echo '<td>'.safe($row["CFIRSTNAME"]).'</td>';
				echo '<td>'.safe($row["CLASTNAME"]).'</td>';
				echo '<td>'.safe($row["DDESCRIPTION"]).'</td>';
				echo '<td>'.safe($row["IPRICE"]).'</td>';
				echo '<td>'.safe($row["CACCOUNTTYPE"]).'</td>';
				echo '<td>'.safe($row["CIMPUTTIME"]).'</td>';
				echo '<td>';
				echo '<select name="'.safe($row["IRECORDNUMBER"]).'ProgressType">';
				echo '<option value="NoProgress"'.$selected1.'>NoProgress</option>';
				echo '<option value="Finished"'.$selected2.'>Finished</option>';
				echo '<option value="InProgress"'.$selected3.'>In Progress</option>';
				echo '<option value="Canceled"'.$selected4.'>Canceled</option>';
				echo '</select>';
				echo '</td>';
				echo '<td>';
				echo '<select name="'.safe($row["IRECORDNUMBER"]).'PaymentStatus">';
				echo '<option value="NoPayment"'.$selected5.'>No Payment</option>';
				echo '<option value="Paid"'.$selected6.'>Paid</option>';
				echo '<option value="InProgress"'.$selected7.'>In Progress</option>';
				echo '<option value="Canceled"'.$selected8.'>Canceled</option>';
				echo '</select>';
				echo '</td>';
				echo '<input type="hidden" name="Checkbox[]" value='.safe($row["IRECORDNUMBER"]).'>';
				echo '<td><input type="checkbox" name="Checkbox2[]" value='.safe($row["IRECORDNUMBER"]).'></td>';
				echo '</tr>';
				$count++;
			}
			else
			{
				$count++;
			}
		}
		echo '</table>';
		echo tableNav('end', 'adminSearch', $results);
		echo '<br>';
		echo '<br>';
		echo'<input type="submit" name="submit" value="Update ">';
		echo '<br>';
		echo'<input type="submit" name="delete" value="Archive Selected">';
	}
	function echoTableHeader() //RENDER A TABLE HEADER WITH LINKS SUPPORTED IN SORTING
	{
		$tableHeader = array('Title','First Name','Last Name','Description','Price','Account Type','Imput Time','Progress','Payment');
		$databaseHeader = array('CTITLE','CFIRSTNAME','CLASTNAME','DDESCRIPTION','IPRICE','CACCOUNTTYPE','CIMPUTTIME','CPROGRESSTYPE','CPAYMENTSTATUS');
		$count = 0;
		foreach($tableHeader as $headerValue)
		{
			$shortValue = $databaseHeader[$count];
			$count ++;
			if(isset($_GET['order']))
			{
				if( isset($_GET['FirstName']) || isset($_GET['LastName']) || isset($_GET['Email']) )
				{
					if($_GET['order'] == 'ASC')
					{
						$tmp = '<td><a href="?FirstName='.$_GET['FirstName'].'&LastName='.$_GET['LastName'].'&Email='.$_GET['Email'].'&sort='.$shortValue.'&order=DESC';
					}
					else
					{
						$tmp = '<td><a href="?FirstName='.$_GET['FirstName'].'&LastName='.$_GET['LastName'].'&Email='.$_GET['Email'].'&sort='.$shortValue.'&order=ASC';
					}
				}
				else
				{
					if($_GET['order'] == 'ASC')
					{
						$tmp = '<td><a href="?sort='.$shortValue.'&order=DESC';
					}
					else
					{
						$tmp = '<td><a href="?sort='.$shortValue.'&order=ASC';
					}
				}
			}
			elseif(isset($_GET['FirstName'])||isset($_GET['LastName'])||isset($_GET['Email']))
			{
				$tmp = '<td><a href="?FirstName='.$_GET['FirstName'].'&LastName='.$_GET['LastName'].'&Email='.$_GET['Email'].'&sort='.$shortValue.'&order=ASC';
			}
			else
			{
				$tmp = '<td><a href="?sort='.$shortValue.'&order=ASC';
			}
			if(isset($_GET['page']) && $_GET['page'])
			{
				$tmp = $tmp.'&page='.$_GET['page'];
			}
			$tmp = $tmp.'">'.$headerValue.'</a></td>';
			echo $tmp;
		}
	}
	function echoAdminPendingHeader() //RENDER A TABLE HEADER WITH LINKS SUPPORTED IN SORTING
	{
		$tableHeader = array('Client','Title','Description','Created Date');
		$databaseHeader = array('cd.CUSERNAMEDETAIL','cr.CREQUESTTITLE','cr.CDESCRIPTION','cr.ICREATEDTIME');
		$count = 0;
		foreach($tableHeader as $headerValue)
		{
			$shortValue = $databaseHeader[$count];
			$count ++;
			if(isset($_GET['order']))
			{
				if( isset($_GET['FirstName']) || isset($_GET['LastName']) || isset($_GET['OrderNumber']) || isset($_GET['Email']) )
				{
					if($_GET['order'] == 'ASC')
					{
						$tmp = '<td><a href="?FirstName='.$_GET['FirstName'].'&LastName='.$_GET['LastName'].'&OrderNumber='.$_GET['OrderNumber'].'&Email='.$_GET['Email'].'&sort='.$shortValue.'&order=DESC';
					}
					else
					{
						$tmp = '<td><a href="?FirstName='.$_GET['FirstName'].'&LastName='.$_GET['LastName'].'&OrderNumber='.$_GET['OrderNumber'].'&Email='.$_GET['Email'].'&sort='.$shortValue.'&order=ASC';
					}
				}
				else
				{
					if($_GET['order'] == 'ASC')
					{
						$tmp = '<td><a href="?sort='.$shortValue.'&order=DESC';
					}
					else
					{
						$tmp = '<td><a href="?sort='.$shortValue.'&order=ASC';
					}
				}
			}
			elseif(isset($_GET['FirstName'])||isset($_GET['LastName'])||isset($_GET['OrderNumber'])||isset($_GET['Email']))
			{
				$tmp = '<td><a href="?FirstName='.$_GET['FirstName'].'&LastName='.$_GET['LastName'].'&OrderNumber='.$_GET['OrderNumber'].'&Email='.$_GET['Email'].'&sort='.$shortValue.'&order=ASC';
			}
			else
			{
				$tmp = '<td><a href="?sort='.$shortValue.'&order=ASC';
			}
			if(isset($_GET['page']) && $_GET['page'])
			{
				$tmp = $tmp.'&page='.$_GET['page'];
			}
			$tmp = $tmp.'">'.$headerValue.'</a></td>';
			echo $tmp;
		}
	}
	function echoPendingHeader() //RENDER A TABLE HEADER WITH LINKS SUPPORTED IN SORTING
	{
		$tableHeader = array('Commissioner','Title','Description','Created Date');
		$databaseHeader = array('ud.CUSERNAMEDETAIL','cr.CREQUESTTITLE','cr.CDESCRIPTION','cr.ICREATEDTIME');
		$count = 0;
		foreach($tableHeader as $headerValue)
		{
			$shortValue = $databaseHeader[$count];
			$count ++;
			if(isset($_GET['order']))
			{
				if( isset($_GET['FirstName']) || isset($_GET['LastName']) || isset($_GET['OrderNumber']) || isset($_GET['Email']) )
				{
					if($_GET['order'] == 'ASC')
					{
						$tmp = '<td><a href="?FirstName='.$_GET['FirstName'].'&LastName='.$_GET['LastName'].'&OrderNumber='.$_GET['OrderNumber'].'&Email='.$_GET['Email'].'&sort='.$shortValue.'&order=DESC';
					}
					else
					{
						$tmp = '<td><a href="?FirstName='.$_GET['FirstName'].'&LastName='.$_GET['LastName'].'&OrderNumber='.$_GET['OrderNumber'].'&Email='.$_GET['Email'].'&sort='.$shortValue.'&order=ASC';
					}
				}
				else
				{
					if($_GET['order'] == 'ASC')
					{
						$tmp = '<td><a href="?sort='.$shortValue.'&order=DESC';
					}
					else
					{
						$tmp = '<td><a href="?sort='.$shortValue.'&order=ASC';
					}
				}
			}
			elseif(isset($_GET['FirstName'])||isset($_GET['LastName'])||isset($_GET['OrderNumber'])||isset($_GET['Email']))
			{
				$tmp = '<td><a href="?FirstName='.$_GET['FirstName'].'&LastName='.$_GET['LastName'].'&OrderNumber='.$_GET['OrderNumber'].'&Email='.$_GET['Email'].'&sort='.$shortValue.'&order=ASC';
			}
			else
			{
				$tmp = '<td><a href="?sort='.$shortValue.'&order=ASC';
			}
			if(isset($_GET['page']) && $_GET['page'])
			{
				$tmp = $tmp.'&page='.$_GET['page'];
			}
			$tmp = $tmp.'">'.$headerValue.'</a></td>';
			echo $tmp;
		}
	}
	function echoTable() //RENDER COMMISSION PROGRESS TABLE
	{
		global $dbh;
		$results = (isset($_GET['resultsPerPage']) && $_GET['resultsPerPage'] / 2 != $_GET['resultsPerPage']) ? $_GET['resultsPerPage'] : 10;
		$limit = null;
		$page = (isset($_GET['page']) && $_GET['page'] / 2 != $_GET['page']) ? $_GET['page'] : 0;
		if($page == 0)
		{
			$limit = 'LIMIT 0, '.$results;
		}
		else
		{
			$tmp = $page * $results;
			$limit = 'LIMIT '.$tmp.', '.$results;
		}
		echo '<table border="1">';
		echo '<tr>';
		echoTableHeader();
		echo '<td>Archive</td>';
		echo '</tr>';
		echo '<tr>';
		if(isset($_GET['sort']))
		{
			if(isset($_GET['order']))
			{
				$query = 'SELECT * FROM book_records WHERE CISDELETED != \'Checked\' AND IUSERID = ? ORDER BY Lower('.safe($_GET['sort']).') '.safe($_GET['order']).' '.$limit.'';
				$result = $dbh->prepare($query);
				$result->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
			}
			else
			{
				$query = 'SELECT * FROM book_records WHERE CISDELETED != \'Checked\' AND IUSERID = ? ORDER BY Lower('.safe($_GET['sort']).') '.$limit.' ';
				$result = $dbh->prepare($query);
				$result->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
			}
		}
		else
		{
			$query = 'SELECT * FROM book_records WHERE CISDELETED != \'Checked\' AND IUSERID = ? '.$limit;
			$result = $dbh->prepare($query);
			$result->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
		}
		$result->execute();
		$count=1;
		while($row = $result->fetch(PDO::FETCH_BOTH))
		{
			$var = 'returnNumber'.$count;
			if(safe($row["CPROGRESSTYPE"])=="NoProgress"){$selected1 = "selected";} else{$selected1 = "";}
			if(safe($row["CPROGRESSTYPE"])=="Finished"){$selected2 = "selected";} else{$selected2 = "";}
			if(safe($row["CPROGRESSTYPE"])=="InProgress"){$selected3 = "selected";} else{$selected3 = "";}
			if(safe($row["CPROGRESSTYPE"])=="Canceled"){$selected4 = "selected";} else{$selected4 = "";}
			if(safe($row["CPAYMENTSTATUS"])=="NoPayment"){$selected5 = "selected";} else{$selected5 = "";}
			if(safe($row["CPAYMENTSTATUS"])=="Paid"){$selected6 = "selected";} else{$selected6 = "";}
			if(safe($row["CPAYMENTSTATUS"])=="InProgress"){$selected7 = "selected";} else{$selected7 = "";}
			if(safe($row["CPAYMENTSTATUS"])=="Canceled"){$selected8 = "selected";} else{$selected8 = "";}
			echo '<tr>';
			echo '<td><a href=\'clientprofile.php?IORDERNUMBER='.safe($row["IORDERNUMBER"]).'\'>'.safe($row["CTITLE"]).'</a></td>';
			echo '<td>'.safe($row["CFIRSTNAME"]).'</td>';
			echo '<td>'.safe($row["CLASTNAME"]).'</td>';
			echo '<td>'.safe($row["DDESCRIPTION"]).'</td>';
			echo '<td>'.safe($row["IPRICE"]).'</td>';
			echo '<td>'.safe($row["CACCOUNTTYPE"]).'</td>';
			echo '<td>'.safe($row["CIMPUTTIME"]).'</td>';
			echo '<td>';
			echo '<select name="'.safe($row["IRECORDNUMBER"]).'ProgressType">';
			echo '<option value="NoProgress"'.$selected1.'>NoProgress</option>';
			echo '<option value="Finished"'.$selected2.'>Finished</option>';
			echo '<option value="InProgress"'.$selected3.'>In Progress</option>';
			echo '<option value="Canceled"'.$selected4.'>Canceled</option>';
			echo '</select>';
			echo '</td>';
			echo '<td>';
			echo '<select name="'.safe($row["IRECORDNUMBER"]).'PaymentStatus">';
			echo '<option value="NoPayment"'.$selected5.'>No Payment</option>';
			echo '<option value="Paid"'.$selected6.'>Paid</option>';
			echo '<option value="InProgress"'.$selected7.'>In Progress</option>';
			echo '<option value="Canceled"'.$selected8.'>Canceled</option>';
			echo '</select>';
			echo '</td>';
			echo '<input type="hidden" name="Checkbox[]" value='.safe($row["IRECORDNUMBER"]).'>';
			echo '<td><input type="checkbox" name="Checkbox2[]" value='.safe($row["IRECORDNUMBER"]).'></td>';
			echo '</tr>';
			$count++;
		}
		echo '</table>';
		echo tableNav('end', 'adminProgress', $results);
		echo '<br><br>';
		echo'<input type="submit" name="submit" value="Update">';
		echo '<br>';
		echo'<input type="submit" name="delete" value="Archive Selected">';
	}
	function echoClientTableHeader() //RENDER A TABLE HEADER WITH LINKS SUPPORTED IN SORTING
	{
		$tableHeader = array( 'Commission Title', 'Description','Commissioner','Price','Imput Time','Progress','Payment');
		$databaseHeader = array('br.CTITLE','br.DDESCRIPTION','ud.CUSERNAMEDETAIL','br.IPRICE', 'br.CIMPUTTIME','br.CPROGRESSTYPE','br.CPAYMENTSTATUS');
		$count = 0;
		foreach($tableHeader as $headerValue)
		{
			$shortValue = $databaseHeader[$count];
			$count ++;
			if(isset($_GET['order']))
			{
				if(isset($_GET['FirstName'])||isset($_GET['LastName'])||isset($_GET['OrderNumber']))
				{
					if($_GET['order'] == 'ASC')
					{
						$tmp = '<td><a href="?FirstName='.$_GET['FirstName'].'&LastName='.$_GET['LastName'].'&OrderNumber='.$_GET['OrderNumber'].'&sort='.$shortValue.'&order=DESC';
					}
					else
					{
						$tmp = '<td><a href="?FirstName='.$_GET['FirstName'].'&LastName='.$_GET['LastName'].'&OrderNumber='.$_GET['OrderNumber'].'&sort='.$shortValue.'&order=ASC';
					}
				}
				else
				{
					if($_GET['order'] == 'ASC')
					{
						$tmp = '<td><a href="?sort='.$shortValue.'&order=DESC';
					}
					else
					{
						$tmp = '<td><a href="?sort='.$shortValue.'&order=ASC';
					}
				}
			}
			elseif(isset($_GET['FirstName'])||isset($_GET['LastName'])||isset($_GET['OrderNumber']))
			{
				$tmp = '<td><a href="?FirstName='.$_GET['FirstName'].'&LastName='.$_GET['LastName'].'&OrderNumber='.$_GET['OrderNumber'].'&sort='.$shortValue.'&order=ASC';
			}
			else
			{
				$tmp = '<td><a href="?sort='.$shortValue.'&order=ASC';
			}
			if(isset($_GET['page']) && $_GET['page'])
			{
				$tmp = $tmp.'&page='.$_GET['page'];
			}
			$tmp = $tmp.'">'.$headerValue.'</a></td>';
			echo $tmp;
		}
	}
	function echoClientTable() //RENDER CLIENT'S COMMISSION PROGRESS TABLE
	{
		global $dbh;
		$results = (isset($_GET['resultsPerPage']) && $_GET['resultsPerPage'] / 2 != $_GET['resultsPerPage']) ? $_GET['resultsPerPage'] : 10;
		$limit = null;
		$page = (isset($_GET['page']) && $_GET['page'] / 2 != $_GET['page']) ? $_GET['page'] : 0;
		if($page == 0)
		{
			$limit = 'LIMIT 0, '.$results;
		}
		else
		{
			$tmp = $page * $results;
			$limit = 'LIMIT '.$tmp.', '.$results;
		}
		echo tableNav('start', 'clientComm', $results);
		echo '<table border="1">';
		echo '<tr>';
		echoClientTableHeader();
		echo '</tr>';
		if(isset($_GET['sort']))
		{
			if(isset($_GET['order']))
			{
				$query = "
					SELECT 
						br.CTITLE,
						br.DDESCRIPTION,
						ud.CUSERNAMEDETAIL,
						br.IPRICE,
						br.CIMPUTTIME,
						br.CPROGRESSTYPE,
						br.CPAYMENTSTATUS,
						br.CISDELETED,
						br.IRECORDNUMBER
					FROM 
						book_records br
					INNER JOIN
						user_detail ud ON (ud.IUSERID = br.IUSERID)
					WHERE 
						br.ICLIENTID = ?
					ORDER BY 
						Lower(".$_GET['sort'].") ".$_GET['order']."
					".$limit."
				";
				$result = $dbh->prepare($query);
				$result->bindParam(1, globalOut('user_key2'), PDO::PARAM_STR, 225);
			}
			else
			{
				$query = "
					SELECT 
						ud.CUSERNAMEDETAIL,
						br.CTITLE,
						br.DDESCRIPTION,
						br.IPRICE,
						br.CIMPUTTIME,
						br.CPROGRESSTYPE,
						br.CPAYMENTSTATUS,
						br.CISDELETED,
						br.IRECORDNUMBER
					FROM 
						book_records br
					INNER JOIN
						user_detail ud ON (ud.IUSERID = br.IUSERID)
					WHERE 
						br.ICLIENTID = ?
					ORDER BY 
						Lower(".$_GET['sort'].")
					".$limit."
				";
				$result = $dbh->prepare($query);
				$result->bindParam(1, globalOut('user_key2'), PDO::PARAM_STR, 225);
			}
		}
		else
		{
			$query = "
				SELECT 
					ud.CUSERNAMEDETAIL,
					br.CTITLE,
					br.DDESCRIPTION,
					br.IPRICE,
					br.CIMPUTTIME,
					br.CPROGRESSTYPE,
					br.CPAYMENTSTATUS,
					br.CISDELETED,
					br.IRECORDNUMBER
				FROM 
					book_records br
				INNER JOIN
					user_detail ud ON (ud.IUSERID = br.IUSERID)
				WHERE 
					br.ICLIENTID = ?
				".$limit."
			";
			$result = $dbh->prepare($query);
			$result->bindParam(1, globalOut('user_key2'), PDO::PARAM_STR, 225);
		}
		$result->execute();
		$count=1;
		while( ($row = $result->fetch(PDO::FETCH_BOTH)))
		{
			$query2 = "
				SELECT
					IORDERNUMBERMD5,
					IUSERIDMD5
				FROM
					book_records
				WHERE
					ICLIENTID = ? AND
					CTITLE = ? AND
					DDESCRIPTION = ?
			";
			$runQuery2 = $dbh->prepare($query2);
			$runQuery2->bindParam(1, globalOut('user_key2'), PDO::PARAM_STR, 225);
			$runQuery2->bindParam(2, $row['CTITLE'], PDO::PARAM_STR, 225);
			$runQuery2->bindParam(3, $row['DDESCRIPTION'], PDO::PARAM_STR, 225);
			$runQuery2->execute();
			$runQueryArray2 = $runQuery2->fetch(PDO::FETCH_BOTH);
			//START USER FRIENDLY FORMATTING
			if(safe($row['CPROGRESSTYPE']) == '' || $row['CPROGRESSTYPE'] == 'NoProgress')
			{
				$row['CPROGRESSTYPE'] = 'No Progress';
			}
			elseif(safe($row['CPROGRESSTYPE']) == 'InProgress')
			{
				$row['CPROGRESSTYPE'] = 'In Progress';
			}
			if(safe($row['CPAYMENTSTATUS']) == 'NoPayment')
			{
				$row['CPAYMENTSTATUS'] = 'No Payment';
			}
			elseif(safe($row['CPAYMENTSTATUS']) == 'InProgress')
			{
				$row['CPAYMENTSTATUS'] = 'In Progress';
			}	
			//STOP USER FRIENDLY FORMATTING
			echo '<tr>';
			echo '<td><a href="clientprofile.php?IORDERNUMBER='.safe($runQueryArray2['IORDERNUMBERMD5']).'&clientview='.safe($runQueryArray2['IUSERIDMD5']).'">'.safe($row['CTITLE']).'</a></td>';
			echo '<td>'.safe($row['DDESCRIPTION']).'</td>';
			echo '<td><a href="userProfile.php?u='.$runQueryArray2['IUSERIDMD5'].'&submit=View">'.$row['CUSERNAMEDETAIL'].'</td>';
			echo '<td>'.safe($row['IPRICE']).'</td>';
			echo '<td>'.safe($row['CIMPUTTIME']).'</td>';
			echo '<td>'.safe($row['CPROGRESSTYPE']).'</td>';
			echo '<td>'.safe($row['CPAYMENTSTATUS']).'</td>';
			echo '</tr>';
			$count++;
		}
		echo '</table>';
		echo tableNav('end', 'clientComm', $results);
		echo '<br>';
		echo '<br>';
	}
	function echoTrash() //RENDER ARCHIVED COMMISSIONS
	{
		global $dbh;
		$results = (isset($_GET['resultsPerPage']) && $_GET['resultsPerPage'] / 2 != $_GET['resultsPerPage']) ? $_GET['resultsPerPage'] : 10;
		$limit = null;
		$page = (isset($_GET['page']) && $_GET['page'] / 2 != $_GET['page']) ? $_GET['page'] : 0;
		if($page == 0)
		{
			$limit = 'LIMIT 0, '.$results;
		}
		else
		{
			$tmp = $page * $results;
			$limit = 'LIMIT '.$tmp.', '.$results;
		}
		echo '<table border="1">';
		echo '<tr>';
		echoTableHeader();
		echo '<td>Update</td>';
		echo '</tr><tr>';
		if(isset($_GET['sort']))
		{
			if(isset($_GET['order']))
			{
				$query = 'SELECT * FROM book_records WHERE IUSERID = ? ORDER BY Lower('.safe($_GET['sort']).') '.safe($_GET['order']).' '.$limit;
				$result = $dbh->prepare($query);
				$result->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
			}
			else
			{
				$query = 'SELECT * FROM book_records WHERE IUSERID = ? ORDER BY Lower('.safe($_GET['sort']).') '.$limit;
				$result = $dbh->prepare($query);
				$result->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
			}
		}
		else
		{
			$query = 'SELECT * FROM book_records WHERE IUSERID = ? '.$limit;
			$result = $dbh->prepare($query);
			$result->bindParam(1, globalOut('user_key3'), PDO::PARAM_STR, 225);
		}
		
		$result->execute();
		$count=1;
		while( ($row = $result->fetch(PDO::FETCH_BOTH)))
		{
			if ($row["CISDELETED"] == 'Checked')
			{
				$var = 'returnNumber'.$count;
				if(safe($row["CPROGRESSTYPE"])==""){$selected1 = "selected";} else{$selected1 = "";}
				if(safe($row["CPROGRESSTYPE"])=="Finished"){$selected2 = "selected";} else{$selected2 = "";}
				if(safe($row["CPROGRESSTYPE"])=="InProgress"){$selected3 = "selected";} else{$selected3 = "";}
				if(safe($row["CPROGRESSTYPE"])=="Canceled"){$selected4 = "selected";} else{$selected4 = "";}
				if(safe($row["CPAYMENTSTATUS"])=="" OR $row["CPAYMENTSTATUS"]=="NoPayment"){$selected5 = "selected";} else{$selected5 = "";}
				if(safe($row["CPAYMENTSTATUS"])=="Paid"){$selected6 = "selected";} else{$selected6 = "";}
				if(safe($row["CPAYMENTSTATUS"])=="InProgress"){$selected7 = "selected";} else{$selected7 = "";}
				if(safe($row["CPAYMENTSTATUS"])=="Canceled"){$selected8 = "selected";} else{$selected8 = "";}
				echo '<tr>';
				echo '<td><a href=\'clientprofile.php?IORDERNUMBER='.safe($row["IORDERNUMBER"]).'\'>'.safe($row["CTITLE"]).'</a></td>';
				echo '<td>'.safe($row["CFIRSTNAME"]).'</td>';
				echo '<td>'.safe($row["CLASTNAME"]).'</td>';
				echo '<td>'.safe($row["DDESCRIPTION"]).'</td>';
				echo '<td>'.safe($row["IPRICE"]).'</td>';
				echo '<td>'.safe($row["CACCOUNTTYPE"]).'</td>';
				echo '<td>'.safe($row["CIMPUTTIME"]).'</td>';
				echo '<td>'.safe($row["CPROGRESSTYPE"]).'</td>';
				echo '<td>'.safe($row["CPAYMENTSTATUS"]).'</td>';
				echo '<td><input type="checkbox" name="Checkbox[]" value='.safe($row["IRECORDNUMBER"]).'></td>';
				echo '</tr>';
				$count++;
			}
			else
			{
				$count++;
			}
		}
		echo '</table>';
		echo tableNav('end', 'adminTrash', $results);
		echo '<br>';
		echo '<br>';
		echo'<input type="submit" name="submit" value="Renew Selected">';
	}
	function renderUserProfile()
	{
		global $dbh;
		$query = "
			SELECT
				IRECORDNUMBER
			FROM
				book_records
			WHERE
				IUSERIDMD5 = ? AND
				CPROGRESSTYPE = 'Finished'
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $_GET['u'], PDO::PARAM_STR, 225);
		$runQuery->execute();
		$ccf = 0;
		while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
		{
			$ccf++;
		}
		$query = "
			SELECT
				IRECORDNUMBER
			FROM
				book_records
			WHERE
				IUSERIDMD5 = ? AND 
				(
					CPROGRESSTYPE != 'Finished' AND
					CPROGRESSTYPE != 'Canceled'
				)
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $_GET['u'], PDO::PARAM_STR, 225);
		$runQuery->execute();
		$ccu = 0;
		while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
		{
			$ccu++;
		}
		$query = "
			SELECT
				IRECORDNUMBER
			FROM
				book_records
			WHERE
				IUSERIDMD5 = ? AND
				CPROGRESSTYPE = 'Canceled'
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $_GET['u'], PDO::PARAM_STR, 225);
		$runQuery->execute();
		$ccc = 0;
		while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
		{
			$ccc++;
		}
		$comCount = array($ccf, $ccu, $ccc);
		$query = "
			SELECT
				ud.CUSERNAMEDETAIL
			FROM
				user_detail ud
			INNER JOIN
				book_records br ON (br.IUSERID = ud.IUSERID)
			WHERE
				IUSERIDMD5 = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $_GET['u'], PDO::PARAM_STR, 225);
		$runQuery->execute();
		$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
		echo $runQueryArray[0];
		echo '<tr>';
		foreach($comCount as $com)
		{
			echo '<td>'.$com.'</td>';
		}
		echo '<form name="" action="'.htmlentities($_SERVER['PHP_SELF']).'" method="POST">';
		echo '<input type="hidden" name="c" value="'.$_GET['u'].'">';
		echo '<td><input type="submit" name="comRequest" value="Request Commission"></td>';
		echo '</form>';
		$query = "
			SELECT
				IUSERID
			FROM
				book_records
			WHERE
				IUSERIDMD5 = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, $_GET['u']);
		$runQuery->execute();
		$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
		echo '<form name="" action="'.htmlentities($_SERVER['PHP_SELF']).'" method="POST">';
		echo '<input type="hidden" name="g" value="'.$runQueryArray['IUSERID'].'">';
		echo '<td><input type="Submit" name="visitGallery" value="Visit Gallery"></td>';
		echo '</form>';
		echo '</tr>';
		return $comCount;
	}
?>
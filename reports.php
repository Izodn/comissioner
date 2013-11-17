<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
	function generateReport($string)
	{
		$tmpDir = $_SERVER['DOCUMENT_ROOT'].'/tmp/';
		$reportDir = globalOut('user_key3').'/';
		$fileName = 'report.csv';
		if(!file_exists($tmpDir))
			mkdir($tmpDir);
		if(!file_exists($tmpDir.$reportDir))
			mkdir($tmpDir.$reportDir);
		$file = $tmpDir.$reportDir.$fileName;
		$fileHandle = fopen($file, 'w');
		fwrite($fileHandle, $string);
		fclose($fileHandle);
		header('Location: tmp/'.globalOut('user_key3').'/'.$fileName.'');
	}
	function dateToSqlDate($date)
	{
		$tmp = explode("/", $date);
		foreach($tmp as $key=>$val)
		{
			$tmp[$key] = trim($val);
		}
		return($tmp[2]."-".$tmp[0]."-".$tmp[1]);
	}
	function defaultView()
	{
?>
		<!DOCTYPE html>
		<html>
			<head>
				<title>Reports</title>
				<script>
					function submitReportType()
					{
						document.getElementById("reportType").submit();
					}
					function selectChange()
					{
						var selectBox = document.getElementById("selectBox");
						if(selectBox.value == "Financials")
						{
							document.getElementById("datePicker").innerHTML = "Start Date: <input name=\"startDate\" size=\"10\" type=\"text\" placeholder=\"MM/DD/YYY\"> End Date: <input name=\"endDate\" size=\"10\" type=\"text\" placeholder=\"MM/DD/YYY\">";
						}
						else
						{
							document.getElementById("datePicker").innerHTML = "";
						}
					}
				</script>
			</head>
			<body>
				<center>
					<?php
						echoLinks();
					?>
					<br />
					<form id="reportType" action="<?php if(!empty($_SERVER['PHP_SELF'])){echo htmlentities($_SERVER['PHP_SELF']);}?>" method="GET">
					<div id="datePicker"></div>
					Report Type: 
					<select id="selectBox" name="reportType" onChange=selectChange()>
						<option>Commissions</option>
						<option>Financials</option>
					</select>
					<button onClick=submitReportType()>Run</button>
					</form>
				</center>
			</body>
		</html>
<?php
	}
	function commissionView()
	{
?>
		<!DOCTYPE html>
		<html>
			<head>
				<title>Report: <?php echo htmlentities($_GET['reportType']) ?></title>
				<script>
				function getDownload()
				{
					window.open("<?php echo $_SERVER['PHP_SELF'].'?reportType='.$_GET['reportType'].'&'; ?>download=true");
				}
				</script>
			</head>
			<body>
				<center>
					<?php
						echoLinks();
					?>
					<br />
					<div id="reportTable">
						<button onClick=getDownload()>Download Report</button>
						<table border="1">
							<tr>
								<td>Submitted Date</td>
								<td>Client Name</td>
								<td>Client Email</td>
								<td>Commission Title</td>
								<td>Commission Description</td>
							</tr>
<?php
	global $dbh;
	$query="
		SELECT
			br.CIMPUTTIME,
			br.CFIRSTNAME,
			br.CLASTNAME,
			cd.CUSERNAMEDETAIL,
			br.CTITLE,
			br.DDESCRIPTION
		FROM
			book_records br
		INNER JOIN
			client_detail cd ON (br.ICLIENTID = cd.ICLIENTID)
		WHERE
			br.IUSERID = ?
	";
	$runQuery = $dbh->prepare($query);
	$runQuery->bindParam(1, globalOut('user_key3'));
	$runQuery->execute();
	echo '<div id="reportTable">';
	while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
	{
		$column = $runQueryArray;
		$time = explode(' ', $column['CIMPUTTIME']);
		$time[1] = explode(':', $time[1]);
		if($time[1][0] < '12')
		{
			$ampm = 'AM';
		}
		else
		{
			$time[1][0] = $time[1][0] - 12;
			$ampm = 'PM';
		}
			
		$timeMaster = $time[0].", ".$time[1][0].":".$time[1][1].$ampm;
		echo '<tr>';
		echo '<td>'.$timeMaster.'</td>';
		echo '<td>'.$column['CLASTNAME'].', '.$column['CFIRSTNAME'].'</td>';
		echo '<td>'.$column['CUSERNAMEDETAIL'].'</td>';
		echo '<td>'.$column['CTITLE'].'</td>';
		echo '<td>'.$column['DDESCRIPTION'].'</td>';
		echo '</tr>';
	}
?>
						</table>
						<button onClick=getDownload()>Download Report</button>
					<div>
				</center>
			</body>
		</html>
<?php
	}
	function commissionsDownload()
	{
		global $dbh;
		$query="
			SELECT
				br.CIMPUTTIME,
				br.CFIRSTNAME,
				br.CLASTNAME,
				cd.CUSERNAMEDETAIL,
				br.CTITLE,
				br.DDESCRIPTION
			FROM
				book_records br
			INNER JOIN
				client_detail cd ON (br.ICLIENTID = cd.ICLIENTID)
			WHERE
				br.IUSERID = ?
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, globalOut('user_key3'));
		$runQuery->execute();
		$csv = '"Submitted Date","Client Name","Client Email","Commission Title","Commission Description"'."\n";
		while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
		{
			foreach($runQueryArray as $key=>$val)
			{
				$val = html_entity_decode($val);
				$runQueryArray[$key] = $val;
			}
			$column = $runQueryArray;
			$time = explode(' ', $column['CIMPUTTIME']);
			$time[1] = explode(':', $time[1]);
			if($time[1][0] < '12')
			{
				$ampm = 'AM';
			}
			else
			{
				$time[1][0] = $time[1][0] - 12;
				$ampm = 'PM';
			}
				
			$timeMaster = $time[0].", ".$time[1][0].":".$time[1][1].$ampm;
			$csv .= '"'.$timeMaster.'",';
			$csv .= '"'.$column['CLASTNAME'].', '.$column['CFIRSTNAME'].'",';
			$csv .= '"'.$column['CUSERNAMEDETAIL'].'",';
			$csv .= '"'.$column['CTITLE'].'",';
			$csv .= '"'.$column['DDESCRIPTION'].'"';
			$csv .= "\n";
		}
		generateReport($csv);
	}
	function financialsView($startDate = null, $endDate = null)
	{
		$pattern = "%^[0-1][0-9]/[0-3][0-9]/[0-9]{4}$%";
		if(preg_match($pattern, $startDate) && preg_match($pattern, $endDate))
			$dateSelect = true;
		else
			$dateSelect = false;
?>
		<!DOCTYPE html>
		<html>
			<head>
				<title>Report: <?php echo htmlentities($_GET['reportType']) ?></title>
				<script>
				function getDownload()
				{
					<?php
						if($dateSelect === true)
							echo 'window.open("'.$_SERVER['PHP_SELF'].'?reportType='.$_GET['reportType'].'&startDate='.$startDate.'&endDate='.$endDate.'&download=true");';
						else
							echo 'window.open("'.$_SERVER['PHP_SELF'].'?reportType='.$_GET['reportType'].'&download=true");';
					?>
				}
				</script>
			</head>
			<body>
				<center>
					<?php
						echoLinks();
					?>
					<br />
					<?php
						if($dateSelect === true)
							echo "Dates: ".$startDate." TO ".$endDate."\n";
					?>
					<div id="reportTable">
						<button onClick=getDownload()>Download Report</button>
						<table border="1">
							<tr>
								<td>Submitted Date</td>
								<td>Client Name</td>
								<td>Client Email</td>
								<td>Commission Title</td>
								<td>Payment Type</td>
								<td>Commission Cost</td>
							</tr>
<?php
	global $dbh;
	if($dateSelect === true)
	{
		$startDate =  dateToSqlDate($startDate);
		$endDate =  dateToSqlDate($endDate);
		//echo "\n<br>startDate = ".$startDate."\n<br>\nendDate = ".$endDate; //Debugging
		$dateInput = "
			AND (DATE(br.CIMPUTTIME) BETWEEN '".$startDate."' AND '".$endDate."')
		";
	}
	else
		$dateInput = "";
	$query="
			SELECT DISTINCT
				br.CIMPUTTIME,
				br.CFIRSTNAME,
				br.CLASTNAME,
				cd.CUSERNAMEDETAIL,
				br.CTITLE,
				br.CACCOUNTTYPE,
				br.IPRICE
			FROM
				book_records br
			INNER JOIN
				client_detail cd ON (br.ICLIENTID = cd.ICLIENTID)
			LEFT JOIN
				user_settings us ON (us.CTYPE = 'paymentMethod' AND us.CVALUE = br.CACCOUNTTYPE)
			WHERE
				us.ICURRENCYTYPE = 1 AND 
				br.IUSERID = ?".$dateInput."
		";
	$runQuery = $dbh->prepare($query);
	$runQuery->bindParam(1, globalOut('user_key3'));
	$runQuery->execute();
	$cost = 0;
	echo '<div id="reportTable">';
	while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
	{
		$column = $runQueryArray;
		$comCost = preg_replace("%[^0-9,.]%", "", $column['IPRICE']);
		$tmp = explode('.', $comCost);
		if(empty($tmp[1]))
			$comCost .= '.00';
		$cost = $cost+$comCost;
		$time = explode(' ', $column['CIMPUTTIME']);
		$time[1] = explode(':', $time[1]);
		if($time[1][0] < '12')
		{
			$ampm = 'AM';
		}
		else
		{
			$time[1][0] = $time[1][0] - 12;
			$ampm = 'PM';
		}
			
		$timeMaster = $time[0].", ".$time[1][0].":".$time[1][1].$ampm;
		echo '<tr>';
		echo '<td>'.$timeMaster.'</td>';
		echo '<td>'.$column['CLASTNAME'].', '.$column['CFIRSTNAME'].'</td>';
		echo '<td>'.$column['CUSERNAMEDETAIL'].'</td>';
		echo '<td>'.$column['CTITLE'].'</td>';
		echo '<td>'.$column['CACCOUNTTYPE'].'</td>';
		echo '<td>$'.$comCost.'</td>';
		echo '</tr>';
	}
	echo '<tr>';
	echo '<td></td><td></td><td></td><td></td><td></td>';
	echo '<td>Total: $'.$cost.'</td>';
	echo '</tr>';
?>
						</table>
						<button onClick=getDownload()>Download Report</button>
					<div>
				</center>
			</body>
		</html>
<?php
	}
	function financialsDownload()
	{
		$cost = 0;
		if( !empty($_GET['startDate']) && !empty($_GET['endDate']) )
		{
			$startDate = $_GET['startDate'];
			$endDate = $_GET['endDate'];
			$pattern = "%^[0-1][0-9]/[0-3][0-9]/[0-9]{4}$%";
			if(preg_match($pattern, $startDate) && preg_match($pattern, $endDate))
			{
				$startDate =  dateToSqlDate($startDate);
				$endDate =  dateToSqlDate($endDate);
				$dateInput = "
					AND (DATE(br.CIMPUTTIME) BETWEEN '".$startDate."' AND '".$endDate."')
				";
			}
			else
				$dateInput = "";
		}
		else
			$dateInput = "";
		global $dbh;
		$query="
			SELECT DISTINCT
				br.CIMPUTTIME,
				br.CFIRSTNAME,
				br.CLASTNAME,
				cd.CUSERNAMEDETAIL,
				br.CTITLE,
				br.CACCOUNTTYPE,
				br.IPRICE
			FROM
				book_records br
			INNER JOIN
				client_detail cd ON (br.ICLIENTID = cd.ICLIENTID)
			LEFT JOIN
				user_settings us ON (us.CTYPE = 'paymentMethod' AND us.CVALUE = br.CACCOUNTTYPE)
			WHERE
				us.ICURRENCYTYPE = 1 AND 
				br.IUSERID = ?".$dateInput."
		";
		$runQuery = $dbh->prepare($query);
		$runQuery->bindParam(1, globalOut('user_key3'));
		$runQuery->execute();
		$csv = '"Submitted Date","Client Name","Client Email","Commission Title","Payment Type","Commission Cost"'."\n";
		while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
		{
			foreach($runQueryArray as $key=>$val)
			{
				$val = html_entity_decode($val);
				$runQueryArray[$key] = $val;
			}
			$column = $runQueryArray;
			$comCost = preg_replace("%[^0-9,.]%", "", $column['IPRICE']);
			$tmp = explode('.', $comCost);
			if(empty($tmp[1]))
				$comCost .= '.00';
			$cost = $cost+$comCost;
			$time = explode(' ', $column['CIMPUTTIME']);
			$time[1] = explode(':', $time[1]);
			if($time[1][0] < '12')
			{
				$ampm = 'AM';
			}
			else
			{
				$time[1][0] = $time[1][0] - 12;
				$ampm = 'PM';
			}
				
			$timeMaster = $time[0].", ".$time[1][0].":".$time[1][1].$ampm;
			$csv .= '"'.$timeMaster.'",';
			$csv .= '"'.$column['CLASTNAME'].', '.$column['CFIRSTNAME'].'",';
			$csv .= '"'.$column['CUSERNAMEDETAIL'].'",';
			$csv .= '"'.$column['CTITLE'].'",';
			$csv .= '"'.$column['CACCOUNTTYPE'].'",';
			$csv .= '"$'.$comCost.'",';
			$csv .= "\n";
		}
		$csv .= '"","","","","",';
		$csv .= '"Total: $'.$cost.'"';
		generateReport($csv);
	}
	if(empty($_GET))
	{
		defaultView();
	}
	elseif(isset($_GET['download']))
	{
		if(empty($_GET['reportType']))
			writeLog('$_GET[\'reportType\'] was empty on report download', null, 'HARD');
		else
			$reportType = $_GET['reportType'];
		if(strToLower($reportType) === 'commissions')
			commissionsDownload();
		elseif(strToLower($reportType) === 'financials')
			financialsDownload();
	}
	elseif(strToLower($_GET['reportType']) === 'commissions')
	{
		commissionView();
	}
	elseif(strToLower($_GET['reportType']) === 'financials')
	{
		if( !empty($_GET['startDate']) && !empty($_GET['endDate']) )
			financialsView($_GET['startDate'], $_GET['endDate']);
		else
			financialsView();
	}
?>

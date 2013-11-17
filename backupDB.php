<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/database.php';
	function createFile($filePath, $permissions = 0777)
	{
		if(file_exists($filePath))
			return false;
		$fileHandle = fopen($filePath, 'w');
		fclose($fileHandle);
		chmod($filePath, $permissions);
		if(file_exists($filePath))
			return true;
		else
			return false;
	}
	function readFromFile($filePath)
	{
		if(!file_exists($filePath))
			return false;
		$fileHandle = fopen($filePath, 'rb');
		$contents = '';
		while (!feof($fileHandle)) 
		{
			$contents .= fread($fileHandle, 8192);
		}
		fclose($fileHandle);
		return ''.$contents.'';
	}
	function writeToFile($filePath, $string, $override = true)
	{
		if(!file_exists($filePath))
			return false;;
		$openType = ($override === true)? 'w' : 'a+';
		$fileHandle = fopen($filePath, $openType);
		$existingText = readFromFile($filePath);
		if($existingText === false)
			return false;
		fwrite($fileHandle, $string);
		$currentText = readFromFile($filePath);
		if($currentText === false)
			return false;
		fclose($fileHandle);
		if($existingText === $currentText && $existingText !== $string)
			return false;
		else
			return true;
	}
	function backupDB( $dbLoc = null, $dbName = null, $dbUser = null, $dbPass = null, $dropTable = false)
	{
		if( $dbLoc===null || $dbName===null || $dbUser===null || $dbPass===null || 
		getType($dbLoc) !== 'string' || getType($dbName) !== 'string' || getType($dbUser) !== 'string' || getType($dbPass) !== 'string')
			return false;
		$db = 'mysql:dbname='.$dbName.';host='.$dbLoc;
		try{
			$dbh = new PDO($db, $dbUser, $dbPass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
		}
		catch(PDOException $e){
			echo $e->getMessage();
			die();
		}
		$master = "";
		$query = "SHOW TABLES IN $dbName";
		$runQuery = $dbh->prepare($query);
		$runQuery->execute();
		$count=0;
		while( $runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH) )
		{
			$tables[$count] = $runQueryArray[0];
			$count++;
		}
		$timeStamp = date('YmdHis');
		foreach( $tables as $table )
		{
			//BEGIN UNSETS
			unset($primaryKey);
			unset($query);
			unset($runQuery);
			unset($column);
			unset($results);
			unset($backup);
			unset($count);
			unset($i);
			unset($dir);
			unset($file);
			unset($fullPath);
			unset($b);
			unset($c);
			//END UNSETS
			$query = "SHOW fields FROM $table";
			$runQuery = $dbh->prepare($query);
			$runQuery->execute();
			$count = 0;
			while( $runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH) )
			{
				if( isset($runQueryArray['Field']) )
					$column[$count]['Field'] = $runQueryArray['Field'];
				if( isset($runQueryArray['Type']) )
					$column[$count]['Type'] = $runQueryArray['Type'];
				if( isset($runQueryArray['Null']) )
					$column[$count]['Null'] = ($runQueryArray['Null'] === 'NO') ? 'NOT NULL' : 'DEFAULT NULL';
				if( isset($runQueryArray['Key']) )
					$column[$count]['Key'] = $runQueryArray['Key'];
				if( isset($runQueryArray['Default']) )
					$column[$count]['Default'] = ($runQueryArray['Type'] === 'timestamp') ? "CURRENT_TIMESTAMP" : "'".$runQueryArray['Default']."'";
				if( isset($runQueryArray['Extra']) )
					$column[$count]['Extra'] = ($runQueryArray['Extra'] === "") ? null : strToUpper($runQueryArray['Extra']);
				$count++;
			}
			if($dropTable === true)
				$backup = "DROP TABLE IF EXISTS ".$table.";\n";
			else
				$backup = "";
			$backup .= "CREATE TABLE IF NOT EXISTS `".$table."` (\n";
			for($i=0;$i<count($column);$i++)
			{
				if(isset($column[$i]))
				{
					$a = $column[$i];
					if( empty($primaryKey) )//There should only be one primary.
						$primaryKey = (isset($a['Key']) && $a['Key'] === 'PRI' ) ? ",\n  PRIMARY KEY (`".$a['Field']."`)" : "";
					if( $i > 0 )
						$backup .= ",\n  ";
					else
						$backup .= "  ";
					if( isset($a['Field']) )
						$backup .= "`".$a['Field']."`";
					if( isset($a['Type']) )
						$backup .= " ".$a['Type'];
					if( isset($a['Null']) )
						$backup .= " ".$a['Null'];
					if( isset($a['Default']) )
						$backup .= " DEFAULT ".$a['Default'];
					if( isset($a['Extra']) )
						$backup .= " ".$a['Extra'];
				}
			}
			$backup .= $primaryKey."\n)";
			$query = "SHOW TABLE STATUS LIKE '".$table."'";
			$runQuery = $dbh->prepare($query);
			$runQuery->execute();
			$runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH);
			/*
			if( isset($runQueryArray['Engine']) )
				$backup .= " ENGINE=".$runQueryArray['Engine']." ";
			if( isset($runQueryArray['Collation']) )
			{
				$tmp = explode('_', $runQueryArray['Collation']);
				$runQueryArray['Collation'] = trim($tmp[0]);
				$backup .= " DEFAULT CHARSET=".$runQueryArray['Collation'];
			}
			if( isset($runQueryArray['Auto_increment']) )
				$backup .= " AUTO_INCREMENT=".$runQueryArray['Auto_increment'];
			*/
			$backup .= ";\n";
			$query = "SELECT Count(*) FROM $table";
			$runQuery = $dbh->prepare($query);
			$runQuery->execute();
			$runQueryArray = $runQuery->fetch(PDO::FETCH_NUM);
			if( !empty($runQueryArray[0]) )
			{
				$backup .= "INSERT INTO `".$table."` (";
				foreach( $column as $key=>$val )
				{
					if( isset($b) )
					{
						$backup .= ", ";
					}
					$backup .= "`".$val['Field']."`";
					$b = true;
				}
				$backup .= ") VALUES\n";
				$query = "SELECT * FROM $table";
				$runQuery = $dbh->prepare($query);
				$runQuery->execute();
				while( $results = $runQuery->fetch(PDO::FETCH_NUM) )
				{
					if(!isset($c))
					{
						$backup .= "(";
						$c = true;
					}
					else
						$backup .= ",\n(";
					for($i=0;$i<count($results);$i++)
					{
						$results[$i] = str_replace("\n", '\\n', $results[$i]);
						$results[$i] = str_replace("\r", '\\r', $results[$i]);
						$results[$i] = str_replace("\\", "\\\\", $results[$i]);
						$results[$i] = str_replace("'", "''", $results[$i]);
						if(strpos($column[$i]['Type'],'int') !== false && $results[$i] !== '') 
							$results[$i] = intVal($results[$i]);
						if( $results[$i] === '' )
							$results[$i] = "NULL";
						elseif( getType($results[$i]) === 'string' )
							$results[$i] = "'".$results[$i]."'";
						if($i > 0)
							$backup .= ", ";
						$backup .= $results[$i];
					}
					$backup .= ")";
				}
				$backup .= ";\n\n";
			}
			$dir1 = $_SERVER['DOCUMENT_ROOT'].'/DBBackup/';
			$dir = $dir1.$timeStamp.'/';
			$file = $table.'.sql';
			if( !file_exists( $dir ) )
			{
				if( !file_exists( $dir1 ) )
				{
					mkdir( $dir1, 0777 );
					chmod( $dir1, 0777 );
				}
				mkdir( $dir, 0777 );
				chmod($dir, 0777);
			}
			$fullPath = $dir.$file;
			createFile($fullPath);
			writeToFile($fullPath, $backup, true);
			$master .= $backup;
		}
		$dir = $_SERVER['DOCUMENT_ROOT'].'/DBBackup/'.$timeStamp.'/';
		$file = '_masterBackup.sql';
		$fullPath = $dir.$file;
		createFile($fullPath);
		writeToFile($fullPath, $master, true);
		return $master;
	}
	if( isset( $_GET['c'] ) && $_GET['c'] === '6b39c8462cac5db2bf7aac16b6e71c2c' )
	{
		$HOST = $_SERVER['HTTP_HOST'];
		if($HOST == 'localhost')
		{
			$dbLoc = 'localhost';
			$dbName = 'bookKeeping';
			$dbUser = 'Administrator';
			$dbPass = 'Bloodtype1Bronte#1';
		}
		elseif($HOST == '192.168.56.101')
		{
			$dbLoc = 'localhost';
			$dbName = 'bookKeeping';
			$dbUser = 'Administrator';
			$dbPass = 'b4bonzi';
		}
		else
		{
			$dbLoc = 'VampBookKeeping.db.10600189.hostedresource.com';
			$dbName = 'VampBookKeeping';
			$dbUser = 'VampBookKeeping';
			$dbPass = 'Bronte#1';
		}
		$dumpTable = true;
		echo '<textarea style="margin: 2px; width: 1900px; height: 930px;">'.backupDB( $dbLoc, $dbName, $dbUser, $dbPass, $dumpTable ).'</textarea>';
	}
	else
		header('Location: http://www.google.com/');
?>
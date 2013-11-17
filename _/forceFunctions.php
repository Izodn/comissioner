<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/env.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/pageTracking.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/globalBank.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/dbh.php';
	function main()
	{
		sessionStart();
		setEnvironment();
	}
	function trackPages() //Used for accurate devLogging
	{
		global $_PAGE;
		$history = array();
		$date = date('h:i:s_Y-n-d');
		$currentPage = $_PAGE['Current'];
		$count = 0;
		if(globalOut('OBJECT_HISTORY'))
		{
			foreach(globalOut('OBJECT_HISTORY') as $a)
			{
				$history[$count] = $a;
				$count++;
			}
			if(isset($_POST) && $_POST != array())
			{
				$a = array();
				$a[0] = $date;
				$a[1] = 'POST';
				$a[2] = $currentPage;
				$count2 = 3;
				$a[$count2] = $_POST;
				$history[$count] = $a;
				globalIn('OBJECT_HISTORY', $history);
			}
			elseif(isset($_GET) && $_GET != array())
			{
				$a = array();
				$a[0] = $date;
				$a[1] = 'GET';
				$a[2] = $currentPage;
				$count2 = 3;
				$a[$count2] = $_GET;
				$history[$count] = $a;
				globalIn('OBJECT_HISTORY', $history);
			}
			else
			{
				$a = array();
				$a[0] = $date;
				$a[1] = '';
				$a[2] = $currentPage;
				$history[$count] = $a;
				globalIn('OBJECT_HISTORY', $history);
			}
		}
		else
		{
			$a = array();
			$a[0] = $date;
			$a[1] = '';
			$a[2] = $currentPage;
			$history[$count] = $a;
			globalIn('OBJECT_HISTORY', $history);
		}
	}
	function outputBufferOn()
	{
		ob_start();
	}
	function dump($var, $arg = '')
	{
		if(getType($var) != 'array' && getType($var) != 'object')
		{
			echo "Cannot dump a non-array!\n<br>\n";
			return false;
		}
		if( getType($var) === 'object' )
		{
			$tmp = $var;
			unset($var);
			$var = (array) $tmp;
		}
		if(isset($arg) && $arg == 'noStyle')
		{
			$master = '';
		}
		else
		{
			$master = '
			<style type="text/css">
				.debug
				{
					border-style: solid;
					border-width: 1px;
					margin: 10px;
				}
			</style>
			';
		}
		$master = $master.'<table id="debug" class="debug">';
		while(!isset($exit))
		{
			$a = current($var);
			$key = key($var);
			if(($key == '' && $a == ''))
			{
				$exit = 1;
			}
			else
			{
				$master = $master.'<tr>';
				if(getType($var[$key]) == 'array' || getType($var[$key]) == 'object')
				{
					$master = $master.'<td><font color="FF2020">'.htmlentities($key).'</font><td>'.getType($var[$key]).'()</td></td><td>=></td>';
				}
				else
				{
					$master = $master.'<td><font color="FF2020">'.htmlentities($key).'</font><td>'.getType($var[$key]).'('.strlen($var[$key]).')</td></td><td>=></td>';
				}
				$master = $master.'<td>';
				if((getType($var[$key]) == 'array'  || getType($var[$key]) == 'object') && $key !== 'GLOBALS')
				{
					$tmp = dump($var[$key], 'noStyle');
					$master = $master.$tmp;
				}
				else
				{
					$master = $master.htmlentities('"'.$a.'"');
				}
				$master = $master.'</td>';
				$master = $master.'</tr>';
				next($var);
			}
		}
		$master = $master.'</table>';
		return $master;
	}
	function dump_raw($var) // Right now this is just a very complicated way of saying x = y where y is an array
	{
		$result = array();
		while(!isset($exit))
		{
			$a = current($var);
			$key = key($var);
			if(($key == '' && $a == ''))
			{
				$exit = 1;
			}
			else
			{
				if((getType($var[$key]) == 'array' || getType($var[$key]) == 'object') && $key !== 'GLOBALS')
				{
					$result[$key] = dump_raw($var[$key]);
				}
				else
				{
					$result[$key] = $a;
				}
				next($var);
			}
		}
		return $result;
	}
	function setEnvironment()
	{
		if(!empty($_SESSION['ENV']['OutputBuffer']) && $_SESSION['ENV']['OutputBuffer']==="1")
		{
			outputBufferOn();
		}
		if(!empty($_SESSION['ENV']['DebugMode']) && $_SESSION['ENV']['DebugMode']==="1") 
		{
			$debugEnv = 1;
		}
		if(!empty($_SESSION['ENV']['TrackPages']) && $_SESSION['ENV']['TrackPages']==="1")
		{
			trackPages();
		}
		elseif(empty($_SESSION['ENV']['TrackPages']) || $_SESSION['ENV']['TrackPages']!=="1")
		{
			if(globalOut('OBJECT_HISTORY'))
			{
				globalClear('OBJECT_HISTORY');
			}
		}
		if(isset($debugEnv) && $debugEnv == 1)
		{
			?>
			<style type="text/css">
				.divVisible 
				{
					position: absolute;
					z-indez: 2;
					display: block;
					margin-top: 50px;
					border-style: dotted;
					border-width: 1px;
					background-color: #FFFFFF;
					opacity: 0.85;
				}
				.divHidden 
				{
					position: absolute;
					z-indez: 2;
					display: none;
					margin-top: 50px;
					<!--background-color: #FFFFFF;-->
				}
				.debug
				{
					border-style: solid;
					border-width: 1px;
					margin: 10px;
				}
				.Button1
				{
					margin-top: 25px;
					position: absolute;
					z-indez: 2;
				}
			</style>
			<script type="text/javascript">
				var divID = "MyDiv";
				function CollapseExpand() 
				{
					var divObject = document.getElementById(divID);
					var currentCssClass = divObject.className;
					if (divObject.className == "divVisible")
						divObject.className = "divHidden";
					else
						divObject.className = "divVisible";
				}
			</script>
				<input class="Button1" id='Button1' type='button' value='DebugInfo' onclick='return CollapseExpand()'/>
				<div id="MyDiv" class="divHidden">
					<?php
						global $_PAGE;
						global $dbh;
						if(isset($_POST)&&$_POST!=array()){echo '$_POST<br>';echo dump($_POST);echo '<br>';}
						if(isset($_GET)&&$_GET!=array()){echo '$_GET<br>';echo dump($_GET);echo '<br>';}
						if(isset($_SESSION)&&$_SESSION!=array()){echo '$_SESSION<br>';echo dump($_SESSION);echo '<br>';}
						if(isset($_COOKIE)&&$_COOKIE!=array()){echo '$_COOKIE<br>';echo dump($_COOKIE);echo '<br>';}
						if(isset($_SERVER)&&$_SERVER!=array()){echo '$_SERVER<br>';echo dump($_SERVER);echo '<br>';}
						if(isset($_PAGE)&&$_PAGE!=array()){echo '$_PAGE<br>';echo dump($_PAGE);echo '<br>';}
						//if(isset($GLOBALS)&&$GLOBALS!=array()){echo '$GLOBALS<br>';echo dump($GLOBALS);echo '<br>';} //RETURN LIST OF ALL GLOBAL VARIABLES
					?>
				</div>
			<?php
		}
	}
	function sessionStart()
	{
		if(!isset($_SESSION))
		{
			session_start();
		}
	}
	main();
?>
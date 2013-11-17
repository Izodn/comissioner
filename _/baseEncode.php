<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/includeList.php';
	getHeader();
	getBody();
	echo '<center>'."\n<br>\n";
	if(globalOut('user_key4') && globalOut('user_key5') && globalOut('user_key6') && globalOut('user_key7') && globalOut('user_key3'))
	{
		echoLinks();
	}
	elseif(globalOut('user_key4') && globalOut('user_key5') && globalOut('user_key6') && globalOut('user_key2'))
	{
		echoClientLinks();
	}
	else
	{
		echoLinks();
	}
	echoAdminLinks();
	echo '</center>'."\n<br>\n";
/*
	Encryption Alrogithm:

	1. Get value of key in hex
		i. First turn key into ASCII-based dec
		ii. Turn into hex
	2. Get intended length of hash
		i. Given by user input
	3. Get length of key
		i. if keyLength > hashLength then trim
	4. Add hex value of key to end of each character in key
	5. Transform each character into a hex
	
	HANDY CODE:
	ASCII/Dec->Hex: dechex($number);
	Char->ASCII: ord($char);
*/
	function getPile()
	{
		$p1 = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$p2 = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'Y', 'X', 'Z');		
		$p3 = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'y', 'x', 'z');
		$pile = array_merge($p1, $p2, $p3);
		return $pile;
	}
	function getRemainder($var)
	{
		$var = floatval($var);
		while($var>=1)
		{
			$var = $var - 1;
		}
		return($var);
	}
	function getInt($var)
	{
		$var = floatval($var);
		return $var - getRemainder($var);
	}
	function calculateBase($var, $base)
	{
		$base = intval($base);
		if(isset($base) && !$base)
		{
			echo 'Base needs to be a int';
			exit;
		}
		$var = intval($var);
		$base = intval($base);
		$a = array();
		$done = 0;
		$count = 0;
		while($done != 1)
		{
			$a[$count] = $var / $base;
			if($a[$count]<1){$done = 1;}
			$var = getInt($a[$count]);
			$a[$count] = getRemainder($a[$count]) * $base;
			$count++;
		}
		$count = 0;
		$pile = getPile();
		foreach($a as $p)
		{
			if(isset($pile[$p]))
			{
				$a[$count] = $pile[$p];
			}
			else
			{
				echo 'Character pool not large enough.';
				$a = array();
				exit;
			}
			$count++;
		}
		$b = array();
		$count = $count - 1;
		$count2 = 0;
		while($count >= 0)
		{
			$b[$count2] = $a[$count];
			$count2++;
			$count--;
		}
		$b = implode($b);
		return $b;
	}
	function toBase($var, $base, $stack)
	{
		if(strlen($var)>1)
		{
			$varArray = str_split($var);
			$count=0;
			foreach($varArray as $a)
			{
				if($stack == 1)
				{
					$varArray[$count] = calculateBase(ord($a.''), $base).'<br>';
				}
				else
				{
					$varArray[$count] = calculateBase(ord($a.''), $base);
				}
				$count++;
			}
			$var = implode($varArray);
			return $var;
		}
		return $var =  calculateBase(ord($var.''), $base);
	}
	if(isset($_POST['submit']))
	{
		if(isset($_POST['encode']) && $_POST['encode'] && isset($_POST['base']) && $_POST['base'])
		{
			$key = $_POST['encode'];
			$base = $_POST['base'];
			if(isset($_POST['stack']) && $_POST['stack'])
			{
				$tmp = toBase($key, $base, 1);
			}
			else
			{
				$tmp = toBase($key, $base, 0);
			}
			$success = $tmp;
		}
	}
?>
	<script>
		function htmlenIn()
		{
			var string = document.getElementById("htmlen").value
			document.getElementById("htmlenOut").innerHTML = string
		}
	</script>
		<form autocomplete="off" autocapitalize="off" name="" aciton=<?php if(isset($_SERVER['PHP_SELF'])&&$_SERVER['PHP_SELF']){echo $_SERVER['PHP_SELF'];} ?> method="POST">
			<table>
				<tr>
					<td>Endode:</td><td><input type="text" name="encode"></td>
				</tr>
				<tr>
					<td>Base:</td><td><input type="text" name="base"></td>
				</tr>
				<tr>
					<td>Stacked:</td><td><input type="checkbox" name="stack"></td>
				</tr>
				<tr>
					<td><input type="submit" name="submit" value="Submit"></td>
				</tr>
			</table>
		</form>
		htmlentities(<input id="htmlen" type="text" name="htmlen" />)
		<button onClick="htmlenIn()">Encode</button>
		<div id="htmlenOut"></div>
		<?php
			if(isset($success))
			{
				echo '<font color="FF0000">'.$success.'</font>';
			}
			getFooter();
		?>
		
		
		
		
		
		
		
		
		
		
		
		
		
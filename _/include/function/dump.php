<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	function dump($var, $arg = '')
	{
		if(isset($arg) && $arg == 'noStyle')
		{
			$master = "\n<table>\n";
		}
		else
		{
			$master = '<table style="border-style:solid;border-width:1px;margin:10px;">'."\n";
		}
		if(getType($var) != 'array' && getType($var) != 'object')
		{
			$master .= "<tr>\n";
			$master .= '<td>'.getType($var).'('.strLen($var).')'.'</td>'."\n".'<td>'.'=>'.'"'.htmlentities($var).'"'.'</td>'."\n".'';
			$master .= "</tr>\n";
			return $master;
		}
		if(getType($var) === 'object')
			$var = (array) $var;
		while(!isset($exit))
		{
			$a = current($var);
			$key = key($var);
			if(($key === null))
			{
				$exit = 1;
			}
			else
			{
				$master = $master.'<tr>'."\n";
				if(getType($var[$key]) == 'array' || getType($var[$key]) == 'object')
				{
					$master = $master.'<td><font color="FF2020">'.htmlentities($key).'</font></td>'."\n".'<td>'.getType($var[$key]).'()</td>'."\n".'<td>=></td>'."\n".'';
				}
				else
				{
					$master = $master.'<td><font color="FF2020">'.htmlentities($key).'</font></td>'."\n".'<td>'.getType($var[$key]).'('.strlen($var[$key]).')</td>'."\n".'<td>=></td>'."\n".'';
				}
				$master = $master.'<td>'."\n";
				if((getType($var[$key]) == 'array'  || getType($var[$key]) == 'object') && $key !== 'GLOBALS')
				{
					$tmp = dump($var[$key], $arg);
					$master = $master.$tmp;
				}
				else
				{
					$master = $master.htmlentities('"'.$a.'"');
				}
				$master = $master.'</td>'."\n".'';
				$master = $master.'</tr>'."\n".'';
				next($var);
			}
		}
		$master = $master.'</table>'."\n".'';
		return $master;
	}
?>
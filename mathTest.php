<?php
	function mult($a, $b, $var = '')	//$a AND $b MUST BE >= 1
	{
		$c = 0;
		$count = 0;
		if(strlen($b) > 4 && $var != 'old')
		{
			$digLen = strlen($b);
			$count = 1;
			$start = 1;
			while($count < $digLen)
			{
				$start = $start * 10;
				$count++;
			}
			$leftOver = $b - $start;
			return $leftOver;
		}
		else
		{
			$count = 1;
			if($a >= $b)
			{
				$largeNum = $a;
				$smallNum = $b;
			}
			else
			{
				$largeNum = $b;
				$smallNum = $a;
			}
			$c = $largeNum;
			while($count < $smallNum)
			{
				$c = $c + $largeNum;
				$count++;
			}
		}
		return $c;
	}
	date_default_timezone_set('America/Los_Angeles');
	$startTime = date('YmdHis');
	set_time_limit(30);
	$b = mult(9999999999999999, 3, 'old');
	$endTime = date('YmdHis');
	$timeTaken = $endTime - $startTime;
	echo $b."\n<br>\n";
	echo 'Calculated in '.$timeTaken.' seconds';
?>

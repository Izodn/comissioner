<?php
	/*
	*	CREATED BY: Brandon Burton
	*	CREATED DATE: 03/22/2013
	*	EXAMPLE:
$queryBuild[0] = 'SELECT';
$queryBuild[1] = array('p.EmpID', 'p.Sal', pl.LastPaid);
$queryBuild[2] = 'FROM';
$queryBuild[3] = 'payroll p';
$queryBuild[4] = 'INNER JOIN';
$queryBuild[5] = 'payroll_logs pl ON (pl.EmpID = p.EmpID)';
$queryBuild[6] = 'WHERE';
$queryBuild[7] = array('p.FirstName = ?', 'p.LastName = ?');
$queryBuild[8] = '';
$queryBinds = array('John', 'Smith');
$query = $queryGet->queryMake($queryBuild);
$runQuery = $dbh->prepare($query);
$number = 1;
$count = 0;
foreach($queryBinds as $a)
{
	$runQuery->bindParam($number, $queryBinds[$count], PDO::PARAM_STR, 225);
	$count++;
	$number++;
}
$runQuery->execute();
$count = 0;
while($runQueryArray = $runQuery->fetch(PDO::FETCH_BOTH))
{
	foreach($runQueryArray as $a)
	{
		echo $a;
	}
	$count++;
}
	*/
	$queryGet = new queryGet;
	class queryGet
	{
		public function queryMake($queryBuild)
		{
			$queryType=$queryBuild[0];
			$fieldArray=$queryBuild[1];
			$queryWhere=$queryBuild[2];
			$fieldWhere=$queryBuild[3];
			$joinType=$queryBuild[4];
			$joinWhere=$queryBuild[5];
			$whereClause=$queryBuild[6];
			$a=$queryBuild[7];
			$orderClause=$queryBuild[8];
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
					'.$b.'
				'.$orderClause.'
			';
			return $query;
		}
	}
?>
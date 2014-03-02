<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	function strToMoney($str) {
		if($str === str_replace(".", "", $str)) { //If no decimal, add .00 to end
			$str .= ".00";
		}
		$cost = intval(str_replace("$", "", str_replace(".", "", $str)));
		return $cost;
	}
	function moneyToStr($str) {
		$pre = '$';
		$strLen = strLen($str);
		if($strLen <= 2) {
			$pre .= '0'; //0 dollars
			if($strLen <= 1) {
				$str = '0'.$str; //If only 1 digit, prepend '0;
				$strLen++; //Add one, since we've added a character
			}
		}
		$lastTwo = $str[$strLen-2].$str[$strLen-1];
		for($a=0;$a<$strLen-2;$a++) {
			$pre .= $str[$a];
		}
		return $pre.'.'.$lastTwo;
	}
?>
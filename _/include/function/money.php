<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
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
		$lastTwo = $str[$strLen-2].$str[$strLen-1];
		for($a=0;$a<$strLen-2;$a++) {
			$pre .= $str[$a];
		}
		return $pre.'.'.$lastTwo;
	}
?>
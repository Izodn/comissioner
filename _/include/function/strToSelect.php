<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	function strToSelect($name="", $selOptions, $attributes='', $selected = null) {
		$selLen = count($selOptions);
		$selStr = '<select name="'.$name.'"'.($attributes!==''?' '.$attributes:'').'>';
		for($a=0;$a<$selLen;$a++) {
			$selStr .= '<option'.($selected===$selOptions[$a]?' selected="selected"':'').'>'.$selOptions[$a].'</option>';
		}
		$selStr .= '</select>';
		return $selStr;
	}
?>
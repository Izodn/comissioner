<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	function strToInput($type, $name, $val='', $other='') {
		return '<input type="'.$type.'" name="'.$name.'" value="'.$val.'" '.$other.'>';
	}
?>
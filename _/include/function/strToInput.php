<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	function strToInput($type, $name, $val='') {
		return '<input type="'.$type.'" name="'.$name.'" value="'.$val.'">';
	}
?>
<?php
	function genSelect($options, $selected = null, $name = null, $id = null, $attr = null) {
		$elementSt = '<select';
		$elementSt .= $name !== null ? ' name = "'.$name.'"' : '';
		$elementSt .= $id !== null ? ' id = "'.$id.'"' : '';
		$elementSt .= $attr !== null ? ' '.$attr : '';
		$elementSt .= '>';
		$elementEn = '</select>';
		$optionStr = '';
		$optionCt = count($options);
		for($i = 0;$i<$optionCt;$i++) {
			$optionStr .= '<option';
			$optionStr .= $selected === $options[$i] ? ' selected="selected"' : '';
			$optionStr .= '>'.$options[$i].'</option>';
		}
		return $elementSt.$optionStr.$elementEn;
	}
?>
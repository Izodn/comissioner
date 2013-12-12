<?php
	function toMoney( $input, $centIncluded = true ) {
		$input = ''.$input.''; //Trans to string
		$inputArr = array(); 
		$stop = false;
		$i=0;
		while($stop == false) { //Create array for each digit
			if(isset($input[$i])) {
				$inputArr[$i] = $input[$i];
			}
			else {
				$stop = true;
			}
			$i++;
		}
		$inputArrCount = count($inputArr);
		$output = $centIncluded == true ? ''.str_replace($inputArr[$inputArrCount-2].$inputArr[$inputArrCount-1], '', $input) : $input;
		$output .= $centIncluded == true ? '.'.$inputArr[$inputArrCount-2].$inputArr[$inputArrCount-1] : '.00';
		return '$'.$output;
	}
?>
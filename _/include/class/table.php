<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/function/dump.php';
	class table{
		var $headerArr;
		var $dataArr;
		var $attributesString = '';
		var $tableStr;
		var $hiddenColumns = array();
		function __construct($headerArr=array(), $dataArr=array()) { //Expects 0-Indexed arrays
			$realHeaders = array();
			$headerLen = count($headerArr);
			$realHeaders = array();
			$realData = array();
			for($a=0;$a<$headerLen;$a++) {
				$realHeaders[$headerArr[$a]] = $headerArr[$a];
			}
			$dataLen = count($dataArr);
			for($a=0;$a<$dataLen;$a++) {
				for($b=0;$b<$headerLen;$b++) {
					$realData[$a][$headerArr[$b]] = $dataArr[$a][$b];
				}
			}
			$this->headerArr = $realHeaders;
			$this->dataArr = $realData;
		}
		function buildTable() {
			$buildMe = '<table'.$this->attributesString.'><tbody>'; //Implement style / class later
			$headerLen = count($this->headerArr);
			$dataLen = count($this->dataArr);
			if($this->headerArr !== array()) { //If a header array is specified
				$buildMe .= '<tr>';
				foreach($this->headerArr as $keyA=>$valA) {
					if(!isset($this->hiddenColumns[$valA])) { //If there's no hide data
						$buildMe .= '<th>'.$valA.'</th>';
					}
					else {
						if($this->hiddenColumns[$valA]['excludeTh'] !== true) //If the header isn't set to hide
							$buildMe .= '<th>';
						if($this->hiddenColumns[$valA]['hideHeader'] !== true) //If the header isn't set to hide
							$buildMe .= $valA;
						if($this->hiddenColumns[$valA]['excludeTh'] !== true) //If the header isn't set to hide
							$buildMe .= '</th>';
					}
				}
				$buildMe .= '</tr>';
			}
			foreach($this->dataArr as $keyA=>$valA) {
				$buildMe .= '<tr>';
				foreach($valA as $keyB=>$valB) {
					if(!isset($this->hiddenColumns[$keyB])) { //If there's no hideColumn data
						$buildMe .= '<td>'.$valB.'</td>';
					}
					else {
						if($this->hiddenColumns[$keyB]['excludeTd'] !== true) //If we're not excluding <td>
							$buildMe .= '<td>';
						if($this->hiddenColumns[$keyB]['hideData'] !== true) //If the data isn't set to hide
							$buildMe .= $valB;
						if($this->hiddenColumns[$keyB]['excludeTd'] !== true) //If we're not excluding <td>
							$buildMe .= '</td>';
					}
				}
				$buildMe .= '</tr>';
			}
			$buildMe .= '</tbody></table>';
			$this->tableStr = $buildMe;
		}
		function setAttr($attr, $val) {
			$this->attributesString .= ' '.$attr.'="'.$val.'"';
		}
		function getTable($noBuild = false) {
			if(!$noBuild)
				$this->buildTable();
			return $this->tableStr;
		}
		function changeData($row, $column, $funcName, $args = array()) {
			if(!function_exists($funcName)) //If the function isn't included/undeclared, do nothing
				return false;
			if(strToLower($row) === 'all') { //If they specify all rows of a specific column
				$dataArr = $this->dataArr; //Save locally for quicker access (I think)
				$dataLen = count($dataArr);
				for($a=0;$a<$dataLen;$a++) {
					$newArgs = $args;
					$newArgs[count($args)] = $dataArr[$a][$column];
					$dataArr[$a][$column] = call_user_func_array($funcName, $newArgs);
				}
				$this->dataArr = $dataArr; //Reset the obj's dataArr with the modified one
			}
			else {
				$newArgs = $args;
				$newArgs[count($args)] = $this->dataArr[$row][$column];
				$this->dataArr[$row][$column] = call_user_func_array($funcName, $newArgs);
			}
		}
		function hideColumn($columnName, $hideHeader = false, $hideData = true, $excludeTh = false, $excludeTd = false) {
			$this->hiddenColumns[$columnName] = array(
				'hideHeader'	=>	$hideHeader,
				'hideData'		=>	$hideData,
				'excludeTh'		=>	$excludeTh,
				'excludeTd'		=>	$excludeTd
			);
		}
	}
?>
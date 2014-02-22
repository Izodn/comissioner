<?php
	class table{
		var $headerArr;
		var $dataArr;
		var $tableStr;
		function __construct() {
			$this->headerArr = $headerArr;
			$this->dataArr = $dataArr;
		}
		function buildTable() {
			$buildMe = '<table><tbody>'; //Implement style / class later
			$headerlen = count($headerArr);
			$datalen = count($dataArr);
			$buildMe .= '<tr>';
			for($a=0;$a<$headerLen;$a++) {
				$buildMe .= '<th>'.$this->headerArr[$a].'</th>';
			}
			$buildMe .= '</tr>';
			for($a=0;$a<$dataLen;$a++) {
				$dataSubLen = count($this->dataArr[$a]);
				$buildMe .= '<tr>';
				for($b=0;$b<$dataSubLen;$b++) {
					$buildMe .= '<td>'.$this->headerArr[$a][$b].'</td>';
				}
				$buildMe .= '</tr>';
			}
			$buildMe .= '</tbody></table>';
			$this->tableStr = $buildMe;
		}
		function getTable() {
			return $this->tableStr;
		}
	}
?>
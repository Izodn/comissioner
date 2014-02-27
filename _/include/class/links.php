<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	class links {
		var $userType;
		var $linkArr = array();
		var $linkArr2 = array();
		function __construct($userObj) {
			$this->userType = $userObj->getUserType();
			if($this->userType === "commissioner" || $this->userType === "superuser") {
				$this->addLink("Commissions", "/index.php");
				$this->addLink("Public Galleries", "#");
				$this->addLink("Settings", "settings.php");
				$this->addLink("Reports", "#");
				if($this->userType === "superuser")
					$this->addLink("Admin Tools", "#");
				$this->addLink("Logout", "/logout.php");
				if( in_array(htmlentities($_SERVER['PHP_SELF']), array('/index.php', '/progress.php', '/commission.php', '/gallery.php'))) {
					$this->addLink2('Input', '/index.php');
					$this->addLink2('Progress', 'progress.php');
					$this->addLink2('Pending Commission', '#');
					$this->addLink2('Search', '#');
					$this->addLink2('Archive', 'progress.php?archives=');
				}
			}
			if($this->userType === "client") {
				$this->addLink("Home", "/");
				$this->addLink("Logout", "/logout.php");
			}
		}
		function addLink($name, $location) {
			$this->linkArr[count($this->linkArr)] = array($name, $location);
		}
		function addLink2($name, $location) {
			$this->linkArr2[count($this->linkArr2)] = array($name, $location);
		}
		function removeLink($name, $location=null) { //location can be provided for a more specific removal
			//Needs work
		}
		function getLinks($seperator = " | ") {
			$links = "";
			$linkArrLen = count($this->linkArr);
			for($a=0;$a<$linkArrLen;$a++) {
				if( $a > 0 ) {
					$links .= $seperator;
				}
				$links .= '<a href="'.$this->linkArr[$a][1].'">'.$this->linkArr[$a][0].'</a>';
			}
			$linkArrLen = count($this->linkArr2);
			if($linkArrLen > 0)
				$links .= '<br>';
			for($a=0;$a<$linkArrLen;$a++) {
				if( $a > 0 ) {
					$links .= $seperator;
				}
				$links .= '<a href="'.$this->linkArr2[$a][1].'">'.$this->linkArr2[$a][0].'</a>';
			}
			return $links."\n<br><br>\n";
		}
	}
?>
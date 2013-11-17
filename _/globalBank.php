<?php
	/*	NAME: GLOBAL VARIABLE BANK
	*	AUTHOR: Brandon Burton
	*	DATE: 03/21/2013
	*
	*	Input Global
	*	syntax: "globalIn('Title', $var);"
	*	
	*	Output Global
	*	syntax: "globalOut('Title');"
	*
	*	Clear GLobal
	*	syntax: "globalClear('Title');"
	*
	*	Destroy Global
	*	syntax: "globalDestroy();"
	*/
	function globalIn($title, $var) 
	{	
		if(isset($_SESSION[$title]))
		{
			globalClear($title);
		}
		$_SESSION[$title] = $var;
		if(globalOut($title))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	function globalOut($title)
	{
		if(isset($_SESSION[$title]))
		{
			return $_SESSION[$title];
		}
		else
		{
			return false;
		}
	}
	function globalClear($title)
	{
		if(isset($_SESSION[$title]))
		{
			$_SESSION[$title] = array();
			unset($_SESSION[$title]); 
		}
		if(!globalOut($title))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	function globalDestroy()
	{
		$_SESSION = array();
		$_COOKIE = array();
		session_destroy();
	}
?>
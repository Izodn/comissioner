<?php
	function baseInclude($var)
	{
		require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/'.$var.'.inc';
	}
	function getHeader()
	{
		baseInclude('header');
	}
	function getBody()
	{
		baseInclude('body');
	}
	function getFooter()
	{
		baseInclude('footer');
	}
	function setHeader($set = null)
	{
		if($set == 'HARD')
		{
		
		}
	}
?>
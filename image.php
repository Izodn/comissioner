<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/application.php'; //ALWAYS INCLUDE THIS
	require_once $_SERVER['DOCUMENT_ROOT'].'/_/include/environment.php';
	define('COM_IMAGE_DIR', $env['IMAGE_LIB']);
	function getImage($var = '')
	{
		if($var === '')
		{
			return false;
		}
		$var = $_SERVER['DOCUMENT_ROOT'].'/'.$var;
		if(!file_exists($var))
		{
			return false;
		}
		$tmp = explode(".",$var);
		$extention = $tmp[count($tmp)-1];
		$fileTypes = array("jpg"=>"JPEG", "jpeg"=>"JPEG", "png"=>"PNG", "gif"=>"GIF");
		$imageType = "JPEG";//DEFAULT
		foreach($fileTypes as $key=>$val)
		{
			if( $extention === $key )
			{
				$imageType = $val;
			}
		}
		header("Content-type: image/".$imageType);
		$image = file_get_contents($var);
		return $image;
	}
	if( isset($_GET['n']) && $_GET['n'] )
	{
		if($image = getImage(COM_IMAGE_DIR.htmlentities($_GET['n'])))
		{
			echo $image;
		}
		else
		{
			echo "There was an error getting the image requested.\n<br>\n";
		}
	}
	else
	{
		//header('Location: /');
	}
?>
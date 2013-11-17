<?php
	function getImage($var = '', $noGallery = null)
	{
		if($var === '')
		{
			return false;
		}
		$var = ($noGallery === null) ? $_SERVER['DOCUMENT_ROOT']."/gallery/".$var : $_SERVER['DOCUMENT_ROOT'].'/'.$var;
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
	if(isset($_GET['c']) && $_GET['c'] && !isset($_GET['g']))
	{
		if($image = getImage(htmlentities($_GET['c'])))
		{
			if($image = getImage($_GET['c']))
			{
				echo $image;
			}
		}
		else
		{
			echo "There was an error getting the image requested.\n<br>\n";
		}
	}
	elseif(isset($_GET['c']) && $_GET['c'] && isset($_GET['g']))
	{
		if($image = getImage($_GET['c'], true))
		{
			if($image = getImage($_GET['c'], true))
			{
				echo $image;
			}
		}
		else
		{
			echo "There was an error getting the image requested.\n<br>\n";
		}
	}
	else
	{
		header('Location: /');
	}
?>
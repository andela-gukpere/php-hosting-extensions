<?php
function is_url_exist($url){
    $ch = curl_init($url);    
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if($code == 200){
       $status = true;
    
    }else{
      $status = false;
    }
    curl_close($ch);
   return $status;
}
$imgs = @$_GET["image"];
$image=false;
if(isset($_POST["img"]))
{

    $image = $_POST["img"];
    $h = intval($_POST["h"]);
    $w = intval($_POST["w"]);
    $id = intval($_POST["id"]);
}
else if(isset($imgs))
{
	$img= explode("/i/", $imgs);
	list($w,$h,$id)=explode("/", $img[0]);
	$w = intval($w);
	$h = intval($h);
	$w = $w?$w:100;
	$h = $h?$h:$w;
	if(is_array($img) && count($img)>1 &&($img[1])) {
	 	$image = $img[1];
	 	$image = urldecode(base64_decode($image));
	}
	else {
		$image = "logo";
	}
	//$image = is_array($img)&&count($img)>1?$img[1]:substr($imgs,2,strlen($imgs));	
}

if($image && $h && $w)
{
  if($image == "logo"){
     $filename="logo.png";
  }
  else {
	  $image =  "http://".$image;
	  $name = explode("/",$image);
	  $name = strtolower(end($name));
	  //$ext = pathinfo($name, PATHINFO_EXTENSION);
	  $filename = "content/".$id."-".$name;
	  if(!file_exists($filename))
	  {
	    $exists = is_url_exist($image);
	    if(!$exists)$filename="logo.png";
	    else copy($image,$filename);
	  }
  }
}
function headerCache()
{
	$cache_expire = 60*60*24*365;
	header("Pragma: public");
	header("Cache-Control: max-age=".$cache_expire);
	header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$cache_expire) . ' GMT');	
}
function resize_pic($img,$x,$y)
    {
	     $img = urldecode($img);
        if($img&&strlen($img)>5)
        {
           $final_ext;
           $img_old;
           if(preg_match("/\.jpg|\.jpeg/i", $img))
           {
                $img_old = imagecreatefromjpeg($img);
                $final_ext = ".jpg";
				$t = 1;
           }
           else if(stristr($img,".gif"))
           {
               $img_old = imagecreatefromgif($img);
               $final_ext = ".gif";
			   $t = 2;
           }
           else if(stristr($img,".png"))
           {
               $img_old = imagecreatefrompng($img);
               $final_ext = ".png";
			   $t = 3;
           }
           else
           {
               $img_old = false;
		$t= false;
           }
           if($img_old && $t)
           {
               list($xx, $yy) = getimagesize($img);
               $newx;
               $newy;
              
		if($xx < $yy)
               {
                   $newx = $x;
                   $newy = intval(($yy / $xx) * $x);
               }
               else
               {
                   $newy = $y;
                   $newx = intval(($xx / $yy) * $y);
               }
                if($xx < $x || $yy < $y)
               {
           	      $newx = $xx;
           	      $newy = $yy;
	 		//header("location: $img");exit();
               }
               try
               {
                   $new_img = imagecreatetruecolor($newx,$newy);
				   if($final_ext == ".gif" || $final_ext == ".png"){
					imagecolortransparent($new_img, imagecolorallocatealpha($new_img, 0, 0, 0, 127));
					imagealphablending($new_img, false);
					imagesavealpha($new_img, true);
					}
                  if(!$new_img);
                   {
                      //throw new Exception("could not save");    
                   }
                   imagecopyresampled($new_img,$img_old,0,0,0,0,$newx,$newy,$xx,$yy);
               }
               catch(Exception $err)
               {
                   echo $err->getMessage();
                   return false;
               }
             switch($t)
			  {
					  case 1:
						  headerCache();				  
						  header("Content-type:image/jpg");
						  imagejpeg($new_img);
					  break;
					case 2:		
						headerCache();	
						header("Content-type:image/gif");
						imagegif($new_img);
					break;
					case 3:
						headerCache();
						header("Content-type:image/png");
						imagepng($new_img);
					break;
					default:
						header("location: $img");
					break;
			  }
			 exit();
		   }
		   else
		   {
				header("location: $img");
		   }
        }
		else
		{
			header("location: $img");
		}
  }

if($image)
{
	resize_pic($filename,$w,$h);
}  
else if(isset($_GET["imgthumb"]))
{
	$w=intval($_GET["w"]);
	$h=intval($_GET["h"]);
	resize_pic($_GET["imgthumb"],$w,$h);	
}

?>
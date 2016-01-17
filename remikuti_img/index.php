<?php
$imgs = @$_GET["image"];
$image=false;
if(isset($imgs))
{
	$img= explode("/i/", $imgs);
	list($w,$h)=explode("/", $img[0]);
	$w = intval($w);
	$h = intval($h);
	$w = $w?$w:100;
	$h = $h?$h:$w;
	$image = is_array($img)&&count($img)>1?$img[1]:substr($imgs,2,strlen($imgs));
	$image = "http://".$image;
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
               if($xx < $x || $yy < $y)
               {
           
				  header("location: $img");exit();
               }
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
	resize_pic($image,$w,$h);
}  
else if(isset($_GET["imgthumb"]))
{
	$w=intval($_GET["w"]);
	$h=intval($_GET["h"]);
	resize_pic($_GET["imgthumb"],$w,$h);	
}

?>
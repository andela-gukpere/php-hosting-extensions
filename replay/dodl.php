<?php
	$url = $_REQUEST["url"];
	$img = $_REQUEST["img"];
	$artist = $_REQUEST["artist"];
	$title = $_REQUEST["title"];
	$res = "An error occured";
	set_time_limit(0);

	function unclean($str)
	{
		$str=str_replace("&#039;","'",$str);
		$str=str_replace("&#147;",'"',$str);
		$str=str_replace("&lt;","<",$str);
		$str=str_replace("&gt;",">",$str);
		$str = preg_replace("/\&\#039\;/", "`", $str);
		$str = preg_replace("/\&\#\d+\;/", "", $str);
		$str=trim($str);	
		return $str;
	}
	function cuttitle($title,$len,$_end=false)
	{
		$len=intval($len);
		return trim(strlen(trim($title))>$len?substr($title,0,($len-3)).($_end?"":"..."):$title);
	}
	function blogurl($title)
	{
		$title = cuttitle($title,67,true);
		$title = preg_replace("/[\\/\\\:*?\"<>,|%#$\s]/","-",unclean($title));
		$title = preg_replace("/“|”|’|‘/", "", $title);
		$title = preg_replace("/--|---|----/","-",$title);
		return $title;
	}
	function curl_get_file_size( $url ) {
	  // Assume failure.
	  $result = -1;

	  $curl = curl_init( $url );

	  // Issue a HEAD request and follow any redirects.
	  curl_setopt( $curl, CURLOPT_NOBODY, true );
	  curl_setopt( $curl, CURLOPT_HEADER, true );
	  curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
	  curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
	  curl_setopt( $curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.11 Safari/537.36");

	  $data = curl_exec( $curl );
	  curl_close( $curl );

	  if( $data ) {
	    $content_length = "unknown";
	    $status = "unknown";

	    if( preg_match( "/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches ) ) {
	      $status = (int)$matches[1];
	    }

	    if( preg_match( "/Content-Length: (\d+)/", $data, $matches ) ) {
	      $content_length = (int)$matches[1];
	    }

	    // http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
	    if( $status == 200 || ($status > 300 && $status <= 308) ) {
	      $result = $content_length;
	    }
	  }

	  return $result;
	}

	if(isset($url))
	{
		$md5 = isset($_REQUEST["md5"])?$_REQUEST["md5"]:false;
		$url = $md5?urldecode(base64_decode($url)):$url;
		$title = $md5?urldecode(base64_decode($title)):$title;
		$artist = $md5?urldecode(base64_decode($artist)):$artist;
		$img = $md5?urldecode(base64_decode($img)):$img;

//echo "$url<BR/>$title<Br/>$artist<Br/>$img";
//exit();
		//$dl = !$md5?true:false;
		//if($md5 && $md5 == md5($url))
		//{
			$dl = true;
		//}

		if($dl)
		{
			$name = explode("/",$url);
			$name = end($name);
			$ext = pathinfo($name, PATHINFO_EXTENSION);
			$filename = "mp3/".blogurl($title."__".$artist).".".$ext;

			if(file_exists($filename))
			{
				$wait = round(filesize($filename)/(1024*1024));
				if(date("U")-60<filectime($filename))sleep($wait);
				header("location: $filename");
			}
			else
			{
				$copy = @copy($url,$filename);	
				if($copy)
				{
					$TextEncoding='UTF-8';
					
					require_once('getid3/getid3.php');
					
					$getID3 = new getID3;
					$getID3->setOption(array('encoding'=>$TextEncoding));

					require_once('getid3/write.php');
					$tagwriter = new getid3_writetags;
					$tagwriter->filename = $filename;
					$tagwriter->tagformats = array('id3v2.3');
					$tagwriter->overwrite_tags = true;

					$tagwriter->tag_encoding = $TextEncoding;

					$fd = fopen($img, 'rb');
					//$filesize = curl_get_file_size($url);
					$APICdata = stream_get_contents($fd);// || fread($fd, $filesize);//
					fclose ($fd);	
					list($APIC_width, $APIC_height, $APIC_imageTypeID) = GetImageSize($img);
					$imagetypes = array(1=>'gif', 2=>'jpeg', 3=>'png');
					$TagData = array(
						'title'         => array($title),
						'artist'        => array($artist),
						'album'         => array('ReplayList'),
						//'year'          => array('2014'),
						//'genre'         => array('Rock'),
						'comment'       => array('Follow @ReplayList'),
						//'track'         => array('04/16'),
						//'popularimeter' => array('email'=>'user@example.net', 'rating'=>128, 'data'=>0),
					);

					if (isset($imagetypes[$APIC_imageTypeID])) 
					{
						$TagData['attached_picture'][0]['data']          = $APICdata;
						$TagData['attached_picture'][0]['picturetypeid'] = 3;
						$TagData['attached_picture'][0]['description']   = $title;
						$TagData['attached_picture'][0]['mime']          = 'image/'.$imagetypes[$APIC_imageTypeID];
					}
					$tagwriter->tag_data = $TagData;
					$tagwriter->WriteTags();
				}
				//else 
				//echo "cannot copy $url into $filename";


				if(file_exists($filename))
				$final_file = "http://".$_SERVER["HTTP_HOST"]."$filename";
				else $final_file = $url;

				header("location: $final_file");
				
				/*$size = filesize($filename);
				
    			header('HTTP/1.1 206 Partial Content');

				header("Pragma: public");
				header("Cache-Control: public");
				header("Content-Description: File Transfer");
				header("Content-Type: audio/mpeg");
				header("Content-Transfer-Encoding: binary");
				header("Content-Length: $size");
				header("Content-disposition: attachment;filename=$name");
				readfile($filename);*/

				//@unlink($filename);
			}
			exit();
		}
		else
		{
			$res = "Bad MD5-checksum";
		}
	}
	else $res ="Incomplete query params";

exit("<script>var res = '$res' var p = parent.window;if(p){p.$('#func #msg #msgtxt').html(res);p.doModal('msg');}else document.write(res)</script>");
?>
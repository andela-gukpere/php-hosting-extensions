<?php
$folder = "mp3/*";
$files = glob($folder); 
foreach($files as $file){ 
  if(is_file($file))
    unlink($file); 
}
?>
<?php
include_once ("class_files.php");

$new_image = new Upload_Image();
$new_image->get_file();

$new_image->set_dimensions(200,200,"../site_pictures/200/");
$new_image->save_image();

$new_image->set_dimensions(150,150,"../site_pictures/150/");
$new_image->save_image();

$new_image->set_dimensions(100,100,"../site_pictures/100/");
$new_image->save_image();
unset($new_image);

?>
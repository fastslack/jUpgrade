<?php
/**
 * jUpgrade
 *
 * @author      Matias Aguirre
 * @email       maguirre@matware.com.ar
 * @url         http://www.matware.com.ar
 * @license     GNU/GPL
 */

$filename = 'joomla16.zip';
//print_r($_REQUEST);
$dir = $_REQUEST['root']."/jupgrade";
//echo $dir;
if(!is_dir($dir)){
	mkdir($dir);
}

$zip = new ZipArchive;
if ($zip->open($filename) === TRUE) {
    $zip->extractTo($dir);
    $zip->close();
    echo 1;
} else {
    echo 0;
}

?>

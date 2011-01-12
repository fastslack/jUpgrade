<?php
/**
 * jUpgrade
 *
 * @version			$Id$
 * @package			MatWare
 * @subpackage	com_jupgrade
 * @author      Matias Aguirre <maguirre@matware.com.ar>
 * @link        http://www.matware.com.ar
 * @license			GNU General Public License version 2 or later; see LICENSE.txt
 */
define('_JEXEC',		1);
define('JPATH_BASE',	dirname(__FILE__));
define('DS',			DIRECTORY_SEPARATOR);

require_once JPATH_BASE.DS.'defines.php';
require_once JPATH_BASE.DS.'jupgrade.class.php';


//$file = "http://localhost/joomla16.zip";
$url = "http://anonymous:@joomlacode.org/svn/joomla/development/branches/jupgrade/pack/joomla16.zip";
$sizefile = JPATH_ROOT.DS.'tmp'.DS.'size.tmp';
$zipfile = JPATH_ROOT.DS.'tmp'.DS.'joomla16.zip';

/*
	Getting the size of the zip
 */
$chGetSize = curl_init();

// Set a valid user agent
curl_setopt($chGetSize, CURLOPT_URL, $url);
curl_setopt($chGetSize, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chGetSize, CURLOPT_HEADER, false);
// Don’t download the body content
curl_setopt($chGetSize, CURLOPT_NOBODY, true);
// Run the curl functions to process the request
$chGetSizeStore = curl_exec($chGetSize);
$chGetSizeError = curl_error($chGetSize);
$chGetSizeInfo = curl_getinfo($chGetSize);
// Close the connection
curl_close($chGetSize);// Print the file size in bytes
// Debug
// print_r($chGetSizeInfo);

$length = $chGetSizeInfo['download_content_length'];

// Open file to write
$size = fopen($sizefile, 'wb');
if ($size == FALSE){
  print "File not opened<br>";
  exit;
} 

// Write and close the file
fwrite($size, $length);
fclose($size);

/*
	Getting the zip
 */
$out = fopen($zipfile, 'wb');
if ($out == FALSE){
  print "File not opened<br>";
  exit;
} 

// Create a curl connection
$chGetFile = curl_init();
curl_setopt($chGetFile, CURLOPT_URL, $url);
curl_setopt($chGetFile, CURLOPT_TIMEOUT, 250);
curl_setopt($chGetFile, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chGetFile, CURLOPT_HEADER, false);
curl_setopt($chGetFile, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($chGetFile, CURLOPT_FILE, $out);
// Run the curl functions to process the request
$chGetFileStore = curl_exec($chGetFile);
$chGetFileError = curl_error($chGetFile);
$chGetFileInfo = curl_getinfo($chGetFile);
// Write and close the file
curl_close($chGetFile);
fclose($out);

if (file_exists($zipfile)) {
    echo 1;
} else {
    echo 0;
}


?>

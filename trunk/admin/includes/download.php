<?php
/**
 * jUpgrade
 *
 * @version		$Id$
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @author      Matias Aguirre <maguirre@matware.com.ar>
 * @link        http://www.matware.com.ar
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

//$file = "http://localhost/joomla16.zip";
$file = "http://anonymous:@joomlacode.org/svn/joomla/development/branches/jupgrade/pack/joomla16.zip";

//sleep(5);
// Create a curl connection
$chGetSize = curl_init();

// Set a valid user agent
curl_setopt($chGetSize, CURLOPT_URL, $file);
//curl_setopt($chGetSize, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11");
//curl_setopt($chGetSize, CURLOPT_FILE, $out);
curl_setopt($chGetSize, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chGetSize, CURLOPT_HEADER, false);
//curl_setopt($chGetSize, CURLOPT_HTTPAUTH, CURLAUTH_BASIC); 
//curl_setopt($chGetSize, CURLOPT_USERPWD, '[anonymous]:[]');

// Don’t download the body content
curl_setopt($chGetSize, CURLOPT_NOBODY, true);

// Run the curl functions to process the request
$chGetSizeStore = curl_exec($chGetSize);
$chGetSizeError = curl_error($chGetSize);
$chGetSizeInfo = curl_getinfo($chGetSize);

// Close the connection
curl_close($chGetSize);// Print the file size in bytes

//print_r($chGetSizeInfo);

$size = fopen('size.tmp', 'wb');
if ($size == FALSE){
  print "File not opened<br>";
  exit;
} 

//echo $chGetSizeInfo['download_content_length']."\n";
fwrite($size, $chGetSizeInfo['download_content_length']);
fclose($size);

$out = fopen('joomla16.zip', 'wb');
if ($out == FALSE){
  print "File not opened<br>";
  exit;
} 

// Create a curl connection
$chGetFile = curl_init();

curl_setopt($chGetFile, CURLOPT_URL, $file);
curl_setopt($chGetFile, CURLOPT_TIMEOUT, 150);
curl_setopt($chGetFile, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chGetFile, CURLOPT_HEADER, false);
curl_setopt($chGetFile, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($chGetFile, CURLOPT_FILE, $out);
//curl_setopt($chGetFile, CURLOPT_HTTPAUTH, CURLAUTH_BASIC); 
//curl_setopt($chGetFile, CURLOPT_USERPWD, '[anonymous]:[]');


$chGetFileStore = curl_exec($chGetFile);
$chGetFileError = curl_error($chGetFile);
$chGetFileInfo = curl_getinfo($chGetFile);

//print_r($chGetFileInfo);

curl_close($chGetFile);
fclose($out);
//echo 11;

?>

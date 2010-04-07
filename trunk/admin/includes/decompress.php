<?php
/**
 * jUpgrade
 *
 * @author      Matias Aguirre
 * @email       maguirre@matware.com.ar
 * @url         http://www.matware.com.ar
 * @license     GNU/GPL
 */

define( 'DS', DIRECTORY_SEPARATOR );

require_once('..'.DS.'libraries'.DS.'pclzip.lib.php');

$filename = 'joomla16.zip';

$path = str_replace('&#96;', '\\', $_REQUEST['root']); 
$dir = $path.DS."jupgrade";

$archive = new PclZip($filename);

if ($archive->extract(PCLZIP_OPT_PATH, $dir) == 0) {
  die("Error : ".$archive->errorInfo(true));
}

?>

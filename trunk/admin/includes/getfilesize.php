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

//sleep(2);
$filename = 'joomla16.zip';

if (file_exists($filename)) {
   $size = filesize($filename);
} else {
   echo 212;
	 exit;
}

//echo filesize('size.tmp');

if (file_exists('size.tmp')) {
  $handle = fopen('size.tmp', 'r');
	$total = trim(fread($handle, 18));
} else {
   echo 121;
	 exit;
}

//unlink('size.tmp');
$percent = $size / $total * 100;
$percent = round($percent);
echo "{$percent},{$size},{$total}";
//echo $percent;
//echo "$total - $size";

?>

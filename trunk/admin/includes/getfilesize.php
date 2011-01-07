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

$sizefile = JPATH_ROOT.DS.'tmp'.DS.'size.tmp';
$zipfile = JPATH_ROOT.DS.'tmp'.DS.'joomla16.zip';

if (file_exists($zipfile)) {
   $size = filesize($zipfile);
} else {
   echo 212;
	 exit;
}

if (file_exists($sizefile)) {
  $handle = fopen($sizefile, 'r');
	$total = trim(fread($handle, 18));
} else {
   echo 121;
	 exit;
}

$percent = $size / $total * 100;
$percent = round($percent);
echo "{$percent},{$size},{$total}";

?>

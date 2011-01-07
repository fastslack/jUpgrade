<?php
/**
 * jUpgrade
 *
 * @author      Matias Aguirre
 * @email       maguirre@matware.com.ar
 * @url         http://www.matware.com.ar
 * @license     GNU/GPL
 */

define('_JEXEC',		1);
//define('JPATH_BASE',	dirname(dirname(dirname(dirname(dirname(__FILE__))))));
define('JPATH_BASE',	dirname(__FILE__));
define('DS',			DIRECTORY_SEPARATOR);

require_once JPATH_BASE.DS.'defines.php';
require_once JPATH_BASE.DS.'jupgrade.class.php';

$tmp = JPATH_ROOT.DS.'tmp';

if (!is_writable(JPATH_ROOT)) {
	echo JPATH_ROOT;
	exit;
}

if (!is_writable($tmp)) {
	echo $tmp;
	exit;
}

echo "OK";

?>

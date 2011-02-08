<?php
/**
 * jUpgrade
 *
 * @version		$Id$
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @copyright	Copyright 2006 - 2011 Matias Aguire. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @author		Matias Aguirre <maguirre@matware.com.ar>
 * @link		http://www.matware.com.ar
 */

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__FILE__));

$parts = explode(DS, JPATH_BASE);
$newparts = array();
for($i=0;$i<count($parts)-4;$i++){
	//echo $parts[$i] . "\n";
	$newparts[] = $parts[$i];

}

define('JPATH_ROOT',			implode(DS, $newparts));
define('JPATH_SITE',			JPATH_ROOT);
define('JPATH_CONFIGURATION', 	JPATH_ROOT);
define('JPATH_ADMINISTRATOR', 	JPATH_ROOT.'/administrator');
define('JPATH_XMLRPC', 		JPATH_ROOT.'/xmlrpc');
define('JPATH_LIBRARIES',	 	JPATH_ROOT.'/libraries');
define('JPATH_PLUGINS',		JPATH_ROOT.'/plugins'  );
define('JPATH_INSTALLATION',	JPATH_ROOT.'/installation');
define('JPATH_THEMES'	   ,	JPATH_BASE.'/templates');
define('JPATH_CACHE',			JPATH_BASE.'/cache');

require_once JPATH_LIBRARIES.'/joomla/methods.php';
require_once JPATH_LIBRARIES.'/joomla/factory.php';
require_once JPATH_LIBRARIES.'/joomla/import.php';
require_once JPATH_LIBRARIES.'/joomla/error/error.php';
require_once JPATH_LIBRARIES.'/joomla/base/object.php';
require_once '../libraries/pclzip.lib.php';

require(JPATH_ROOT.DS."configuration.php");

$zipfile = JPATH_ROOT.'/tmp/joomla16.zip';
$dir = JPATH_ROOT.'/jupgrade';

if (file_exists($zipfile)) {
	$archive = new PclZip($zipfile);

	if ($archive->extract(PCLZIP_OPT_PATH, $dir) == 0) {
		die("Error : ".$archive->errorInfo(true));
	}
	echo 1;
}
else {
	echo 0;
}

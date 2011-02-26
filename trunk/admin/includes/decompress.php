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

// Includes
require_once JPATH_BASE.'/defines_old.php';
require_once JPATH_BASE.'/jupgrade.class.php';
require_once '../libraries/pclzip.lib.php';

// jUpgrade class
$jupgrade = new jUpgrade;

// Getting the component parameter with global settings
$params = $jupgrade->getParams();

$zipfile = JPATH_ROOT.'/tmp/joomla16.zip';

// downloading Molajo instead Joomla zip
if ($params->mode == 1) {
	$zipfile = JPATH_ROOT.'/tmp/molajo16.zip';
}

$dir = JPATH_ROOT.'/jupgrade';

if (file_exists($zipfile)) {
	$archive = new PclZip($zipfile);

	if ($archive->extract(PCLZIP_OPT_PATH, $dir) == 0) {
		die("Error : ".$archive->errorInfo(true));
	}
	echo 1;
}

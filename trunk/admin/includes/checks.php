<?php
/**
 * jUpgrade
 *
 * @version		$Id
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @copyright	Copyright 2006 - 2011 Matias Aguire. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @author		Matias Aguirre <maguirre@matware.com.ar>
 * @link		http://www.matware.com.ar
 */

define('_JEXEC',		1);
define('JPATH_BASE',	dirname(__FILE__));
define('DS',			DIRECTORY_SEPARATOR);

require_once JPATH_BASE.'/defines.php';
require_once JPATH_BASE.'/jupgrade.class.php';

// Check Curl
$ext = get_loaded_extensions();

if (!in_array("curl", $ext)) {
	echo "401: cURL not loaded";
	exit;
}

// Check dirs
if (!is_writable(JPATH_ROOT)) {
	echo "402: ".JPATH_ROOT." is unwritable";
	exit;
}

$tmp = JPATH_ROOT.'/tmp';

if (!is_writable($tmp)) {
	echo "403: {$tmp} is unwritable";
	exit;
}

echo "OK";


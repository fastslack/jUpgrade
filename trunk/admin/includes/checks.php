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

require_once JPATH_BASE.'/defines_old.php';
require_once JPATH_BASE.'/jupgrade.class.php';

/**
 * Initialize jupgrade class
 */
$jupgrade = new jUpgrade;

/**
 * Requirements
 */
$requirements = $jupgrade->getRequirements();

/**
 * Checking tables
 */
$query = "SHOW TABLES";
$jupgrade->db_new->setQuery($query);
$tables = $jupgrade->db_new->loadResultArray();

if (!in_array('j16_jupgrade_categories', $tables)) {
	echo "401: j16_jupgrade_categories table not exist";
	exit;
}

if (!in_array('j16_jupgrade_menus', $tables)) {
	echo "402: j16_jupgrade_menus table not exist";
	exit;
}

if (!in_array('j16_jupgrade_modules', $tables)) {
	echo "403: j16_jupgrade_modules table not exist";
	exit;
}

if (!in_array('j16_jupgrade_steps', $tables)) {
	echo "404: j16_jupgrade_steps table not exist";
	exit;
}

/**
 * Check if j16_jupgrade_steps is fine
 */
$query = "SELECT COUNT(id) FROM `j16_jupgrade_steps`";
$jupgrade->db_new->setQuery($query);
$nine = $jupgrade->db_new->loadResult();

if ($nine < 10) {
	echo "405: j16_jupgrade_steps is not valid";
	exit;
}

/**
 * Check Curl
 */
$ext = get_loaded_extensions();

if (!in_array("curl", $ext)) {
	echo "406: cURL not loaded";
	exit;
}

/**
 * Check dirs
 */
if (!is_writable(JPATH_ROOT)) {
	echo "407: ".JPATH_ROOT." is unwritable";
	exit;
}

$tmp = JPATH_ROOT.'/tmp';

if (!is_writable($tmp)) {
	echo "408: {$tmp} is unwritable";
	exit;
}

/**
 * Compare the PHP version
 */
if (!version_compare($requirements['phpMust'], $requirements['phpIs'], '<')) {
	echo "409: PHP 5.2+ or greater is required";
	exit;
}

/**
 * Compare the MYSQL version
 */
if (!version_compare($requirements['mysqlMust'], $requirements['mysqlIs'])) {
	echo "410: MySQL 5.0+ or greater is required";
	exit;
}
echo "OK";


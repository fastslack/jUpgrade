<?php
/**
 * jUpgrade
 *
 * @version		  $Id:
 * @package		  MatWare
 * @subpackage	com_jupgrade
 * @author      Matias Aguirre <maguirre@matware.com.ar>
 * @link        http://www.matware.com.ar
 * @copyright		Copyright 2006 - 2011 Matias Aguire. All rights reserved.
 * @license		  GNU General Public License version 2 or later; see LICENSE.txt
 */

define('_JEXEC', 1);
define('JPATH_BASE', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);
require_once JPATH_BASE.'/defines.php';
require_once JPATH_BASE.'/jupgrade.class.php';

// jUpgrade class
$jupgrade = new jUpgrade;

// Select the steps
$query = "SELECT * FROM j16_jupgrade_steps AS s WHERE s.status != 1 AND s.extension = 1 ORDER BY s.id ASC LIMIT 1";
$jupgrade->db_new->setQuery($query);
$step = $jupgrade->db_new->loadObject();

// Check for query error.
$error = $jupgrade->db_new->getErrorMsg();

if ($step->name == 'extensions') {

	require_once JPATH_BASE.'/extensions.php';

	$extension = new jUpgradeExtensions($step);
	$extension->upgrade();

}else{

	// Try to load the adapter object
	$filename = dirname(__FILE__).DS.'adapters'.DS.strtolower($step->name).'.php';
	$types = array('/^com_(.+)$/e', '/^mod_(.+)$/e', '/^plg_(.+)_(.+)$/e');
	$classes = array("'jUpgradeComponent'.ucfirst('\\1')", "'jUpgradeModule'.ucfirst('\\1')", "'jUpgradePlugin'.ucfirst('\\1').ucfirst('\\2')");

	if (file_exists($filename)) {
		require_once($filename);
		$class = preg_replace($types, $classes, $step->name);
		if (!class_exists($class)) {
			return false;
		}

		$extension = new $class($step);
		$success = $extension->upgradeExtension();
	}
}

if (!$extension || $extension->isReady()) {
	// updating the status flag
	$query = "UPDATE j16_jupgrade_steps SET status = 1"
	." WHERE name = '{$step->name}'";
	$jupgrade->db_new->setQuery($query);
	$jupgrade->db_new->query();

	// Check for query error.
	$error = $jupgrade->db_new->getErrorMsg();

	// Check the lastes step id
	$query = "SELECT id FROM j16_jupgrade_steps ORDER BY id DESC LIMIT 1";
	$jupgrade->db_new->setQuery($query);
	$lastid = $jupgrade->db_new->loadResult();

	// Check for query error.
	$error = $jupgrade->db_new->getErrorMsg();
	echo ";|;".$step->id.";|;".$step->name.";|;".$lastid;
}
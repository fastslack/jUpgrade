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

//print_r($step);

// Check for query error.
$error = $jupgrade->db_new->getErrorMsg();


if ($step->name == 'extensions') {

	require_once JPATH_BASE.'/extensions.php';

	$extensions = new jUpgradeExtensions;
	$extensions->upgrade();

}else{

	//echo $step->name;

	// Try to load the adapter object
	$filename = dirname(__FILE__).DS.'adapters'.DS.strtolower($step->name).'.php';

	if (file_exists($filename)) {
		require_once($filename);
		$class = 'jUpgradeExtensions'.ucfirst($step->name);
		if (!class_exists($class)) {
			return false;
		}

		$adapter = new $class();
		$adapter->upgrade();
	}
}

// updating the status flag
$query = "UPDATE j16_jupgrade_steps SET status = 1"
." WHERE name = '{$step->name}'";
$jupgrade->db_new->setQuery($query);
$jupgrade->db_new->query();

// Check for query error.
$error = $jupgrade->db_new->getErrorMsg();

echo ";|;".$step->id.";|;".$step->name;

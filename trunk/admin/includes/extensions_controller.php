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
require_once JPATH_BASE.'/extensions.php';

// jUpgrade class
$jupgrade = new jUpgrade;

// Check the last step id
$query = "SELECT id FROM jupgrade_steps ORDER BY id DESC LIMIT 1";
$jupgrade->db_new->setQuery($query);
$lastid = $jupgrade->db_new->loadResult()+1;

// Check for query error.
$error = $jupgrade->db_new->getErrorMsg();

// Select the step
$query = "SELECT * FROM jupgrade_steps AS s WHERE s.status != 1 AND s.extension = 1 ORDER BY s.id ASC LIMIT 1";
$jupgrade->db_new->setQuery($query);
$step = $jupgrade->db_new->loadObject();
if (!$step) {
	// No steps to run, terminate
	echo ";|;{$lastid};|;ready;|;{$lastid}";
	return;
}
$step->lastid = $lastid;

// Check for query error.
$error = $jupgrade->db_new->getErrorMsg();

$extension = jUpgradeExtensions::getInstance($step);
$success = $extension->upgradeExtension();

echo ";|;".$step->id.";|;".$extension->output().";|;".$step->lastid;

if ($extension->isReady()) {
	// updating the status flag
	$query = "UPDATE jupgrade_steps SET status = 1"
	." WHERE name = '{$step->name}'";
	$jupgrade->db_new->setQuery($query);
	$jupgrade->db_new->query();

	// Check for query error.
	$error = $jupgrade->db_new->getErrorMsg();
}

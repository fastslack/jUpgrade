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

// No direct access.
defined('_JEXEC') or die;

// Require the base controller
require_once JPATH_COMPONENT.'/controller.php' ;

// Require specific controller if requested
$controller	= JRequest::getVar('controller', 'cpanel');
$task		= JRequest::getVar('task');

if ($controller) {
	$path = JPATH_COMPONENT.'/controllers/'.$controller.'.php';

	if (file_exists($path)) {
		require_once $path;
	}
	else {
		$controller = 'cpanel';
	}
}

// Create the controller
$classname	= 'jupgradeController'.$controller;
$controller = new $classname( );

// Perform the Request task
$controller->execute( $task );

// Redirect if set by the controller
$controller->redirect();

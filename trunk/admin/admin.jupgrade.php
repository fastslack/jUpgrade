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

// no direct access
defined('_JEXEC') or die('Restricted access');

// Require the base controller
require_once( JPATH_COMPONENT.DS.'controller.php' );

// Require specific controller if requested
$controller = JRequest::getVar('controller', 'cpanel');
$task = JRequest::getVar('task');

if($controller) {
  $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
  if (file_exists($path)) {
  	require_once $path;
  } else {
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

?>

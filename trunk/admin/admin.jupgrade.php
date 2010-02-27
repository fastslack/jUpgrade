<?php
/**
 * jUpgrade
 *
 * @author      Matias Aguirre
 * @email       maguirre@matware.com.ar
 * @url         http://www.matware.com.ar
 * @license     GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Require the base controller
require_once( JPATH_COMPONENT.DS.'controller.php' );

// Require specific controller if requested
$controller = JRequest::getVar('controller', 'cpanel');
$task = JRequest::getVar('task');
//echo "<b>controller:</b> {$controller} || <b>task:</b> {$task} || <b>type:</b> {$type}";

if ($task == "cpanel") {
	$controller = "cpanel";
}

if($controller) {
  $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
	//echo $path;
  if (file_exists($path)) {
  	require_once $path;
  } else {
		//echo "dsdsdsdsdds";
    $controller = 'cpanel';
  }
}
//echo $controller;
//echo JRequest::getVar('controller');
//echo " - " . JRequest::getVar('task');
//echo "dsds";
//print_r($controller->getView('cpanel'));
//echo $classname;
//print_r($controller);

// Create the controller
$classname	= 'jupgradeController'.$controller;
//echo $classname;
$controller = new $classname( );
//$controller->registerDefaultTask('cpanel');

// Perform the Request task
$controller->execute( $task );

// Redirect if set by the controller
$controller->redirect();

?>

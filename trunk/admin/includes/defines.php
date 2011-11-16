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

// no direct access
defined('_JEXEC') or die;

$directory = $_GET['directory'];

define('JPATH_ROOT',			dirname(dirname(dirname(dirname(JPATH_BASE)))));
define('JPATH_SITE',			JPATH_ROOT.DS.$directory);
define('JPATH_CONFIGURATION', 	JPATH_ROOT.DS.$directory);
define('JPATH_ADMINISTRATOR', 	JPATH_ROOT.DS.$directory.DS.'administrator');
define('JPATH_XMLRPC', 		JPATH_ROOT.DS.$directory.DS.'xmlrpc');
define('JPATH_LIBRARIES',	 	JPATH_ROOT.DS.$directory.DS.'libraries');
define('JPATH_PLUGINS',		JPATH_ROOT.DS.$directory.DS.'plugins'  );
define('JPATH_INSTALLATION',	JPATH_ROOT.DS.$directory.DS.'installation');
define('JPATH_THEMES'	   ,	JPATH_BASE.DS.$directory.DS.'templates');
define('JPATH_CACHE',			JPATH_BASE.DS.$directory.DS.'cache');

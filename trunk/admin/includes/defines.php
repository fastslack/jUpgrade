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

/**
* Joomla! Application define
*/

//Global definitions
//Joomla framework path definitions
$parts = explode(DS, JPATH_BASE);

//print_r($parts);
$newparts = array();
for($i=0;$i<count($parts)-4;$i++){
	//echo $parts[$i] . "\n";
	$newparts[] = $parts[$i];

}

//print_r(implode(DS, $newparts));
//Defines
define('JPATH_ROOT',			implode(DS, $newparts));

define('JPATH_SITE',			JPATH_ROOT.'/jupgrade');
define('JPATH_CONFIGURATION', 	JPATH_ROOT.'/jupgrade');
define('JPATH_ADMINISTRATOR', 	JPATH_ROOT.'/jupgrade/administrator');
define('JPATH_XMLRPC', 		JPATH_ROOT.'/jupgrade/xmlrpc');
define('JPATH_LIBRARIES',	 	JPATH_ROOT.'/jupgrade/libraries');
define('JPATH_PLUGINS',		JPATH_ROOT.'/jupgrade/plugins'  );
define('JPATH_INSTALLATION',	JPATH_ROOT.'/jupgrade/installation');
define('JPATH_THEMES'	   ,	JPATH_BASE.'/jupgrade/templates');
define('JPATH_CACHE',			JPATH_BASE.'/jupgrade/cache');

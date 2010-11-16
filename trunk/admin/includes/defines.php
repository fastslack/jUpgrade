<?php
/**
 * jUpgrade
 *
 * @version		$Id$
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @author      Matias Aguirre <maguirre@matware.com.ar>
 * @link        http://www.matware.com.ar
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Joomla! Application define
*/

//Global definitions
//Joomla framework path definitions
$parts = explode( DS, JPATH_BASE );

//print_r($parts);
$newparts = array();
for($i=0;$i<count($parts)-4;$i++){
	//echo $parts[$i] . "\n";
	$newparts[] = $parts[$i];

}

//print_r(implode( DS, $newparts ));
//Defines
define( 'JPATH_ROOT',			implode( DS, $newparts ) );

define( 'JPATH_SITE',			JPATH_ROOT.DS.'jupgrade' );
define( 'JPATH_CONFIGURATION', 	JPATH_ROOT.DS.'jupgrade' );
define( 'JPATH_ADMINISTRATOR', 	JPATH_ROOT.DS.'jupgrade'.DS.'administrator' );
define( 'JPATH_XMLRPC', 		JPATH_ROOT.DS.'jupgrade'.DS.'xmlrpc' );
define( 'JPATH_LIBRARIES',	 	JPATH_ROOT.DS.'jupgrade'.DS.'libraries' );
define( 'JPATH_PLUGINS',		JPATH_ROOT.DS.'jupgrade'.DS.'plugins'   );
define( 'JPATH_INSTALLATION',	JPATH_ROOT.DS.'jupgrade'.DS.'installation' );
define( 'JPATH_THEMES'	   ,	JPATH_BASE.DS.'jupgrade'.DS.'templates' );
define( 'JPATH_CACHE',			JPATH_BASE.DS.'jupgrade'.DS.'cache');

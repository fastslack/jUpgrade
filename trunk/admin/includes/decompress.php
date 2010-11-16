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

define( '_JEXEC', 1 );
define( 'DS', DIRECTORY_SEPARATOR );
define( 'JPATH_BASE', dirname(__FILE__) );

$parts = explode( DS, JPATH_BASE );
$newparts = array();
for($i=0;$i<count($parts)-4;$i++){
	//echo $parts[$i] . "\n";
	$newparts[] = $parts[$i];

}

define( 'JPATH_ROOT',			implode( DS, $newparts ) );
define( 'JPATH_SITE',			JPATH_ROOT );
define( 'JPATH_CONFIGURATION', 	JPATH_ROOT );
define( 'JPATH_ADMINISTRATOR', 	JPATH_ROOT.DS.'administrator' );
define( 'JPATH_XMLRPC', 		JPATH_ROOT.DS.'xmlrpc' );
define( 'JPATH_LIBRARIES',	 	JPATH_ROOT.DS.'libraries' );
define( 'JPATH_PLUGINS',		JPATH_ROOT.DS.'plugins'   );
define( 'JPATH_INSTALLATION',	JPATH_ROOT.DS.'installation' );
define( 'JPATH_THEMES'	   ,	JPATH_BASE.DS.'templates' );
define( 'JPATH_CACHE',			JPATH_BASE.DS.'cache');

require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'methods.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'factory.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'import.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'error'.DS.'error.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'base'.DS.'object.php' );

require(JPATH_ROOT.DS."configuration.php");

jimport( 'joomla.filesystem.archive' );


$filename = 'joomla16.zip';

//$_REQUEST['root'] = "./";
//$path = str_replace('&#96;', '\\', $_REQUEST['root']); 
//$dir = $path.DS."jupgrade";

$archive = new JArchive( );
$archive->extract($filename, JPATH_SITE.DS.'jupgrade');

//echo true;

?>

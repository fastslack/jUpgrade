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
define( 'JPATH_BASE', dirname(__FILE__) );
define( 'DS', DIRECTORY_SEPARATOR );
require_once ( JPATH_BASE .DS.'defines.php' );

require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'methods.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'factory.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'import.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'error'.DS.'error.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'base'.DS.'object.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'database.php' );
require_once ( JPATH_INSTALLATION.DS.'models'.DS.'database.php' );
//require_once ( JPATH_INSTALLATION.DS.'helpers'.DS.'database.php' );

require(JPATH_ROOT.DS."configuration.php");

$jconfig = new JConfig();
//print_r($jconfig);

$config = array();
$config['dbo'] = & JInstallationHelperDatabase::getDBO('mysql', $jconfig->host, $jconfig->user, $jconfig->password, $jconfig->db, 'j16_');

$installHelper = new JInstallationModelDatabase($config);

//print_r($installHelper);

$dbscheme = JPATH_INSTALLATION.DS.'sql'.DS.'mysql'.DS.'joomla.sql';

if ($installHelper->populateDatabase($config['dbo'], $dbscheme, $errors) > 0 ) {
	return 0;
}else{
	return 1;
}

?>

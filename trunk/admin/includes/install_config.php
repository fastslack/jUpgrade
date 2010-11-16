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
require_once ( JPATH_LIBRARIES .DS.'joomla'.DS.'methods.php' );
require_once ( JPATH_LIBRARIES .DS.'joomla'.DS.'factory.php' );
require_once ( JPATH_LIBRARIES .DS.'joomla'.DS.'import.php' );
require_once ( JPATH_LIBRARIES .DS.'joomla'.DS.'error'.DS.'error.php' );
require_once ( JPATH_LIBRARIES .DS.'joomla'.DS.'base'.DS.'object.php' );
//require_once ( JPATH_ADMINISTRATOR .DS.'components'.DS.'com_jupgrade'.DS.'helpers'.DS.'install.php' );
//require_once ( JPATH_ADMINISTRATOR .DS.'components'.DS.'com_jupgrade'.DS.'helpers'.DS.'configuration.php' );

require_once ( JPATH_INSTALLATION.DS.'models'.DS.'configuration.php' );

require(JPATH_ROOT.DS."configuration.php");

$jconfig = new JConfig();

//print_r($jconfig);

$jconfig->db_type   = 'mysql';
$jconfig->db_host     = $jconfig->host;
$jconfig->db_user     = $jconfig->user; 
$jconfig->db_pass = $jconfig->password;
$jconfig->db_name = $jconfig->db;  
$jconfig->db_prefix = "j16_";
$jconfig->site_name = $jconfig->sitename;

//print_r($jconfig);

if (JInstallationModelConfiguration::_createConfiguration($jconfig) > 0 ) {
	echo 1;
}else{
	echo 0;
}
?>

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
require_once ( JPATH_LIBRARIES .DS.'joomla'.DS.'database'.DS.'database.php' );
require_once ( JPATH_LIBRARIES .DS.'joomla'.DS.'html'.DS.'parameter.php' );

require(JPATH_ROOT.DS."configuration.php");

$jconfig = new JConfig();

//print_r($jconfig);

$config = array();
$config['driver']   = 'mysql';
$config['host']     = $jconfig->host;
$config['user']     = $jconfig->user; 
$config['password'] = $jconfig->password;
$config['database'] = $jconfig->db;  
$config['prefix']   = $jconfig->dbprefix;
//print_r($config);

$config_new = $config;
$config_new['prefix'] = "j16_";

$db = JDatabase::getInstance( $config );
$db_new = JDatabase::getInstance( $config_new );
//print_r($db_new);
//print_r($db);

?>

<?php
/**
 * jUpgrade
 *
 * @author      Matias Aguirre
 * @email       maguirre@matware.com.ar
 * @url         http://www.matware.com.ar
 * @license     GNU/GPL
 */

function insertObjectList( $db, $table, &$object, $keyName = NULL ) {

	$count = count($object);

	for ($i=0; $i<$count; $i++) {
		$db->insertObject($table, $object[$i]);
		$ret = $db->getErrorMsg();
	}

  return $ret;
}

define( '_JEXEC', 1 );
define( 'JPATH_BASE', dirname(__FILE__) );
define( 'DS', DIRECTORY_SEPARATOR );
require_once ( JPATH_BASE .DS.'defines.php' );

require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'methods.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'factory.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'import.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'error'.DS.'error.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'base'.DS.'object.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'application'.DS.'application.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'database.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'tablenested.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table'.DS.'menu.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table'.DS.'menutype.php' );
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

$query = "SELECT `menutype`,`name` AS title,`alias`,`link`,`type`,"
." `published`,`parent` AS parent_id, `componentid` AS component_id,"
." `sublevel` AS level,`ordering`,`checked_out`,`checked_out_time`,`browserNav`,"
." `access`,`params`,`lft`,`rgt`,`home`"
." FROM {$config['prefix']}menu"
." ORDER BY id ASC";
$db->setQuery( $query );
$menu = $db->loadObjectList();
//echo $db->errorMsg();
//print_r($content[0]);

//echo count($menu);

echo insertObjectList($db_new, '#__menu', $menu);

$query = "SELECT *"
." FROM {$config['prefix']}menu_types"
." WHERE id > 1";
$db->setQuery( $query );
$menutypes = $db->loadObjectList();

echo insertObjectList($db_new, '#__menu_types', $menutypes);

?>

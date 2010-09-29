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
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'database.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'tablenested.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table'.DS.'module.php' );
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

$query = "SELECT `title`,NULL AS `note`, `content`,`ordering`,`position`,"
." `checked_out`,`checked_out_time`,`published`,`module`,"
." `access`,`showtitle`,`params`,`client_id`,NULL AS `language`"
." FROM {$config['prefix']}modules"
." WHERE iscore = 0 AND id > 19"
." ORDER BY id ASC";

$db->setQuery( $query );
$modules = $db->loadObjectList();
//echo $db->errorMsg();

echo insertObjectList($db_new, '#__modules', $modules);

//print_r($content[0]);
/*
for($i=0;$i<count($modules);$i++) {
	//echo $sections[$i]->id . "<br>";

	$new = new JTableModule($db_new);
	//print_r($new);
	//$new->id = $modules[$i]->id;
	$new->title = $modules[$i]->title;
	$new->content = $modules[$i]->content;
	$new->ordering = $modules[$i]->ordering;
	$new->position = $modules[$i]->position;
	$new->checked_out = $modules[$i]->checked_out;
	$new->checked_out_time = $modules[$i]->checked_out_time;
	$new->published = $modules[$i]->published;
	$new->module = $modules[$i]->module;
	$new->access = $modules[$i]->access+1;
	$new->showtitle = $modules[$i]->showtitle;
	$new->params = $modules[$i]->params;
	$new->client_id = $modules[$i]->client;
	$new->store();

}
sleep(1);
*/
?>

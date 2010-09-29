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
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'html'.DS.'parameter.php' );
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


for($i=0;$i<count($modules);$i++){

	## Parameters
	$p = explode("\n", $modules[$i]->params);
	$params = array();
	for($y=0;$y<count($p);$y++){
		$ex = explode("=",$p[$y]);
		if($ex[0] != ""){
			if ($ex[1] == 0) {
				$ex[1] = "";
			}
			$params[$ex[0]] = $ex[1];
		}
	}
	$parameter = new JParameter($params);
	$parameter->loadArray($params);
	$modules[$i]->params = $parameter->toString();
	//echo $parameter->toString() . "\n";

	## Language
	$modules[$i]->language = "*";
	
	## Module
	if ($modules[$i]->module == "mod_mainmenu") {
		$modules[$i]->module = "mod_menu";
	}

	## Access
	$modules[$i]->access = $modules[$i]->access+1;

}

echo insertObjectList($db_new, '#__modules', $modules);


?>

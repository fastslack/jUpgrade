<?php
/**
 * jUpgrade
 *
 * @author      Matias Aguirre
 * @email       maguirre@matware.com.ar
 * @url         http://www.matware.com.ar
 * @license     GNU/GPL
 */

/**
 * Inserts a category
 *
 * @access  public
 * @param   object  An object whose properties match table fields
 */
function insertCategory( $db, $object, $parent) {

	/*
	 * Get data for category
	 */
	$query = "SELECT rgt FROM #__categories"
	." WHERE title = 'ROOT' AND extension = 'system'"
	." LIMIT 1";
	$db->setQuery( $query );
	$lft = $db->loadResult();	
	$rgt = $lft+1;
	$title = $object->title;
	$alias = $object->alias;
	$published = $object->published;
	$access = $object->access + 1;

	/*
	 * Get parent
	 */
	if($parent != false){
		$path = JFilterOutput::stringURLSafe($parent)."/".$alias;
		$query = "SELECT id FROM #__categories WHERE title = '{$parent}' LIMIT 1";
		$db->setQuery( $query );
		$parent = $db->loadResult();
		$level = 2;
		$old = $object->id;
	}else{
		$parent = 1;
		$level = 1;
		$path = $alias;
		$old = 0;
	}
	
	##
	## Insert Category
	##
	$query = "INSERT INTO #__categories" 
	." (`parent_id`,`lft`,`rgt`,`level`,`path`,`extension`,`title`,`alias`,`published`, `access`, `language`)"
	." VALUES( {$parent}, {$lft}, {$rgt}, {$level}, '{$path}', 'com_content', '{$title}', '{$alias}', {$published}, {$access}, '*' ) ";
	$db->setQuery( $query );
	$db->query();	echo $db->getError();
	$new = $db->insertid();
	//echo $query . "\n";

	## Update ROOT rgt
	$query = "UPDATE #__categories SET rgt=rgt+2"
	." WHERE title = 'ROOT' AND extension = 'system'";		
	$db->setQuery($query);
	$db->query();	echo $db->getError();

	##
	## Save old id and new id
	##
	$query = "INSERT INTO #__jupgrade_categories" 
	." (`old`,`new`)"
	." VALUES( {$old}, {$new} ) ";
	$db->setQuery( $query );
	$db->query();
	//echo $db->getError();
	//echo $query."\n";

 	return true;
}

/**
 * Inserts asset
 *
 * @access  public
 */
function insertAsset( $db, $parent ) {

	/*
	 * Get parent
	 */
	if($parent != false){
		$query = "SELECT id FROM #__assets WHERE title = '{$parent}' LIMIT 1";
		$db->setQuery( $query );
		$parent = $db->loadResult();	
		$level = 3;
	}else{
		$parent = 1;
		$level = 2;
	}

	/*
	 * Get data for asset
	 */
	$query = "SELECT id FROM #__categories ORDER BY id DESC LIMIT 1";
	$db->setQuery( $query );
	$cid = $db->loadResult();	

	$query = "SELECT title FROM #__categories ORDER BY id DESC LIMIT 1";
	$db->setQuery( $query );
	$title = $db->loadResult();	

	$query = "SELECT rgt+1 FROM #__assets WHERE name LIKE 'com_content.category%'"
	." ORDER BY lft DESC LIMIT 1";
	$db->setQuery( $query );
	$lft = $db->loadResult();	
	if(!isset($lft)) {
		$lft = 34;
	}

	$rgt = $lft+1;
	$name = "com_content.category.{$cid}";
	$rules = '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}';

	// Update lft & rgt > cat
	$query = "UPDATE #__assets SET lft=lft+2"
	." WHERE lft >= {$lft}";		
	$db->setQuery($query);
	$db->query();	echo $db->getError();

	$query = "UPDATE #__assets SET rgt=rgt+2"
	." WHERE rgt >= {$rgt}";		
	$db->setQuery($query);
	$db->query();	echo $db->getError();

	/*
	 * Insert Asset
	 */
	$query = "INSERT INTO #__assets" 
	." (`parent_id`,`lft`,`rgt`,`level`,`name`,`title`,`rules`)"
	." VALUES( {$parent}, {$lft}, {$rgt}, {$level}, '{$name}', '{$title}', '{$rules}') ";
	$db->setQuery( $query );
	$db->query();	echo $db->getError();
	//echo $query . "<br>";

	// Setting the asset id to category
	$query = "SELECT id FROM #__assets ORDER BY id DESC LIMIT 1";
	$db->setQuery( $query );
	$assetid = $db->loadResult();	

	$query = "UPDATE #__categories SET asset_id={$assetid}"
	." WHERE id = {$cid}";		
	$db->setQuery($query);
	$db->query();	echo $db->getError();


	return true;
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
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table'.DS.'asset.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table'.DS.'category.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'utilities'.DS.'string.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'filter'.DS.'filteroutput.php' );
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

## Getting old values 
$query = "SELECT *"
." FROM {$config['prefix']}sections"
." WHERE scope = 'content'"
." ORDER BY id ASC";
$db->setQuery( $query );
$sections = $db->loadObjectList();
//print_r($sections);

for($i=0;$i<count($sections);$i++) {
	//echo $sections[$i]->title . "<br>";

	insertCategory($db_new, $sections[$i], false);
	insertAsset($db_new, false);

	/*
	 * CHILDREN CATEGORIES
	 */

	$query = "SELECT *"
	." FROM {$config['prefix']}categories"
	." WHERE section = {$sections[$i]->id}"
	." ORDER BY id ASC";
	$db->setQuery( $query );
	$categories = $db->loadObjectList();

	for($y=0;$y<count($categories);$y++){

		//echo $categories[$y]->title."\n";

		insertCategory($db_new, $categories[$y], $sections[$i]->title);
		insertAsset($db_new, $sections[$i]->title);

	}

}

sleep(1);
?>

<?php
/**
 * jUpgrade
 *
 * @author      Matias Aguirre
 * @email       maguirre@matware.com.ar
 * @url         http://www.matware.com.ar
 * @license     GNU/GPL
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );


/**
* jUpgrade utility class for migrations
*
*/
class jUpgrade
{
	var $config = array();
	var $db_old = null;
	var $db_new = null;

	function __construct()
	{
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
		require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'html'.DS.'parameter.php' );
		require(JPATH_ROOT.DS."configuration.php");

		$jconfig = new JConfig();
		//print_r($jconfig);

		$this->config['driver']   = 'mysql';
		$this->config['host']     = $jconfig->host;
		$this->config['user']     = $jconfig->user; 
		$this->config['password'] = $jconfig->password;
		$this->config['database'] = $jconfig->db;  
		$this->config['prefix']   = $jconfig->dbprefix;
		//print_r($config);

		$config_new = $this->config;
		$config_new['prefix'] = "j16_";

		$this->db_old = JDatabase::getInstance( $this->config );
		$this->db_new = JDatabase::getInstance( $config_new );
	}

	/**
	 * Inserts a category
	 *
	 * @access  public
	 * @param   object  An object whose properties match table fields
	 */
	function insertCategory( $object, $parent) {

		/*
		 * Get data for category
		 */
		$query = "SELECT rgt FROM #__categories"
		." WHERE title = 'ROOT' AND extension = 'system'"
		." LIMIT 1";
		$this->db_new->setQuery( $query );
		$lft = $this->db_new->loadResult();

		$rgt = $lft+1;
		$title = $object->title;
		$alias = $object->alias;
		$published = $object->published;
		$access = $object->access + 1;
		$extension = $object->section;

		##
		## Correct extension
		##
		if (is_numeric($extension) || $extension == "") {
			$extension = "com_content";
		} 
		if ($extension == "com_banner") {
			$extension = "com_banners";
		}
		if ($extension == "com_contact_detail") {
			$extension = "com_contact";
		}

		##
		## Get parent
		##
		if($parent != ""){
			$path = JFilterOutput::stringURLSafe($parent)."/".$alias;

			$query = "SELECT id FROM #__categories WHERE title = '{$parent}' LIMIT 1";
			$this->db_new->setQuery( $query );
			$parent = $this->db_new->loadResult();
			echo $this->db_new->getError();

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
		." VALUES( {$parent}, {$lft}, {$rgt}, {$level}, '{$path}', '{$extension}', '{$title}', '{$alias}', {$published}, {$access}, '*' ) ";
		$this->db_new->setQuery( $query );
		$this->db_new->query();	echo $this->db_new->getError();
		$new = $this->db_new->insertid();
		//echo $query . "\n\n";

		## Update ROOT rgt
		$query = "UPDATE #__categories SET rgt=rgt+2"
		." WHERE title = 'ROOT' AND extension = 'system'";		
		$this->db_new->setQuery($query);
		$this->db_new->query();	echo $this->db_new->getError();

		##
		## Save old id and new id
		##
		$query = "INSERT INTO #__jupgrade_categories" 
		." (`old`,`new`)"
		." VALUES( {$old}, {$new} ) ";
		$this->db_new->setQuery( $query );
		$this->db_new->query();
		//echo $this->db_new->getError();
		//echo $query."\n";

	 	return true;
	}

	/**
	 * Inserts asset
	 *
	 * @access  public
	 */
	function insertAsset( $parent ) {

		/*
		 * Get parent
		 */
		if($parent != false){
			$query = "SELECT id FROM #__assets WHERE title = '{$parent}' LIMIT 1";
			$this->db_new->setQuery( $query );
			$parent = $this->db_new->loadResult();	
			$level = 3;
		}else{
			$parent = 1;
			$level = 2;
		}

		/*
		 * Get data for asset
		 */
		$query = "SELECT id FROM #__categories ORDER BY id DESC LIMIT 1";
		$this->db_new->setQuery( $query );
		$cid = $this->db_new->loadResult();	

		$query = "SELECT title FROM #__categories ORDER BY id DESC LIMIT 1";
		$this->db_new->setQuery( $query );
		$title = $this->db_new->loadResult();	

		$query = "SELECT rgt+1 FROM #__assets WHERE name LIKE 'com_content.category%'"
		." ORDER BY lft DESC LIMIT 1";
		$this->db_new->setQuery( $query );
		$lft = $this->db_new->loadResult();	
		if(!isset($lft)) {
			$lft = 34;
		}

		$rgt = $lft+1;
		$name = "com_content.category.{$cid}";
		$rules = '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}';

		// Update lft & rgt > cat
		$query = "UPDATE #__assets SET lft=lft+2"
		." WHERE lft >= {$lft}";		
		$this->db_new->setQuery($query);
		$this->db_new->query();	echo $this->db_new->getError();

		$query = "UPDATE #__assets SET rgt=rgt+2"
		." WHERE rgt >= {$rgt}";		
		$this->db_new->setQuery($query);
		$this->db_new->query();	echo $this->db_new->getError();

		/*
		 * Insert Asset
		 */
		$query = "INSERT INTO #__assets" 
		." (`parent_id`,`lft`,`rgt`,`level`,`name`,`title`,`rules`)"
		." VALUES( {$parent}, {$lft}, {$rgt}, {$level}, '{$name}', '{$title}', '{$rules}') ";
		$this->db_new->setQuery( $query );
		$this->db_new->query();	echo $this->db_new->getError();
		//echo $query . "<br>";

		// Setting the asset id to category
		$query = "SELECT id FROM #__assets ORDER BY id DESC LIMIT 1";
		$this->db_new->setQuery( $query );
		$assetid = $this->db_new->loadResult();	

		$query = "UPDATE #__categories SET asset_id={$assetid}"
		." WHERE id = {$cid}";		
		$this->db_new->setQuery($query);
		$this->db_new->query();	echo $this->db_new->getError();


		return true;
	}


	function insertObjectList( $db, $table, &$object, $keyName = NULL ) {

		$count = count($object);

		for ($i=0; $i<$count; $i++) {
			$db->insertObject($table, $object[$i]);
			$ret = $db->getErrorMsg();
		}

		return $ret;
	}

	function fixParams ($object) {

		for($i=0;$i<count($object);$i++){
			$p = explode("\n", $object[$i]->params);
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
			$object[$i]->params = $parameter->toString();
			//echo $parameter->toString() . "\n";
		}

		return $object;
	}

}

?>

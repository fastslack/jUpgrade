<?php
/**
 * jUpgrade
 *
 * @version		  $Id$
 * @package		  MatWare
 * @subpackage	com_jupgrade
 * @author      Matias Aguirre <maguirre@matware.com.ar>
 * @link        http://www.matware.com.ar
 * @copyright		Copyright 2006 - 2011 Matias Aguire. All rights reserved.
 * @license		  GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

// Make sure we can see all errors.
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * jUpgrade utility class for migrations
 *
 * @package		MatWare
 * @subpackage	com_jupgrade
 */
class jUpgrade
{
	/**
	 * @var		string	The name of the source database table.
	 * @since	0.4.
	 */
	protected $source = null;

	public $config = array();
	public $config_old = array();
	public $db_old = null;
	public $db_new = null;

	function __construct()
	{
		// Set timelimit to 0
		if(!ini_get('safe_mode')) { 
			set_time_limit(0);
		}

		// Base includes
		require_once JPATH_LIBRARIES.'/joomla/import.php';
		require_once JPATH_LIBRARIES.'/joomla/methods.php';
		require_once JPATH_LIBRARIES.'/joomla/factory.php';
		require_once JPATH_LIBRARIES.'/joomla/import.php';
		require_once JPATH_LIBRARIES.'/joomla/config.php';
		require_once JPATH_SITE.'/configuration.php';

		// Base includes
		jimport('joomla.base.object');
		jimport('joomla.base.adapter');

		// Application includes
		jimport('joomla.application.helper');
		jimport('joomla.application.application');
		jimport('joomla.application.component.modellist');

		// Error includes
		jimport('joomla.error.error');
		jimport('joomla.error.exception');

		// Database includes
		jimport('joomla.database.database');
		jimport('joomla.database.table');
		jimport('joomla.database.tablenested');
		jimport('joomla.database.table.asset');
		jimport('joomla.database.table.category');

		// Update and installer includes for 3rd party extensions
		jimport('joomla.installer.installer');
		jimport('joomla.updater.updater');
		jimport('joomla.updater.update');

		// Other stuff
		jimport('joomla.filesystem.folder');
		jimport('joomla.utilities.string');
		jimport('joomla.filter.filteroutput');
		jimport('joomla.html.parameter');

		// Echo all errors, otherwise things go really bad.
		JError::setErrorHandling(E_ALL, 'echo');

		// Manually
		//JTable::addIncludePath(JPATH_LIBRARIES.'/joomla/database/table');

		$jconfig = new JConfig();

		$this->config['driver']   = $jconfig->dbtype; 
		$this->config['host']     = $jconfig->host;
		$this->config['user']     = $jconfig->user;
		$this->config['password'] = $jconfig->password;
		$this->config['database'] = $jconfig->db;
		$this->config['prefix']   = $jconfig->dbprefix;
		//print_r($config);
		$this->config_old = $this->config;
		$this->config_old['prefix'] = $this->getPrefix();

		$this->db_new = JDatabase::getInstance($this->config);
		$this->db_old = JDatabase::getInstance($this->config_old);
	}

	/**
	 * Converts the params fields into a JSON string.
	 *
	 * @param	string	$params	The source text definition for the parameter field.
	 *
	 * @return	string	A JSON encoded string representation of the parameters.
	 * @since	0.4.
	 * @throws	Exception from the convertParamsHook.
	 */
	protected function convertParams($params)
	{
		$temp	= new JParameter($params);
		$object	= $temp->toObject();

		// Fire the hook in case this parameter field needs modification.
		$this->convertParamsHook($object);

		return json_encode($object);
	}

	/**
	 * A hook to be able to modify params prior as they are converted to JSON.
	 *
	 * @param	object	$object	A reference to the parameters as an object.
	 *
	 * @return	void
	 * @since	0.4.
	 * @throws	Exception
	 */
	protected function convertParamsHook(&$object)
	{
		// Do customisation of the params field here for specific data.
	}

	/**
	 * Get the raw data for this part of the upgrade.
	 *
	 * @param	string 	$select	A select condition to add to the query.
	 * @param	string 	$join	 A select condition to add to the query.
	 * @param	mixed 	$where	A string or array where condition to add to the query.
	 * @param	string	$order	The ordering for the source data.
	 *
	 * @return	array	Returns a reference to the source data array.
	 * @since	0.4.
	 * @throws	Exception
	 */
	protected function &getSourceData($select = '*', $join = null, $where = null, $order = null)
	{
		// Error checking.
		if (empty($this->source)) {
			throw new Exception('Source table not specified.');
		}

		// Prepare the query for the source data.
		$query = $this->db_old->getQuery(true);

		$query->select((string)$select);
		$query->from((string)$this->source);

		// Check if 'where' clause is set
		if (!empty($where))
		{
			// Multiple conditions
			if (is_array($where))
			{
				for($i=0;$i<count($where);$i++) {
					$query->where((string)$where[$i]);
				}
			}
			else if (is_string($where))
			{
				$query->where((string)$where);
			}

		}

		// Check if 'join' clause is set
		if (!empty($join))
		{
			// Multiple joins
			if (is_array($join))
			{
				for($i=0;$i<count($join);$i++) {
					$pieces = explode("JOIN", $join[$i]);
					$type = trim($pieces[0]);
					$conditions = trim($pieces[1]);

					$query->join((string)$type, (string)$conditions);
				}

			}
			else if (is_string($join))
			{
				$pieces = explode("JOIN", $join);
				$type = trim($pieces[0]);
				$conditions = trim($pieces[1]);

				$query->join((string)$type, (string)$conditions);
			}
		}

		// Check if 'order' clause is set
		if (!empty($order))
			$query->order($order);

		// Debug
		//print_r($query->__toString());

		$this->db_old->setQuery((string)$query);

		// Getting data
		$rows	= $this->db_old->loadAssocList();
		$error = $this->db_old->getErrorMsg();

		// Check for query error.
		if ($error) {
			throw new Exception($error);
		}

		return $rows;
	}

	/**
	 * Sets the data in the destination database.
	 *
	 * @return	void
	 * @since	0.4.
	 * @throws	Exception
	 */
	protected function setDestinationData()
	{
		// Get the source data.
		$rows	= $this->getSourceData();
		$table	= empty($this->destination) ? $this->source : $this->destination;

		// TODO: this is ok for proof of concept, but add some batching for more efficient inserting.
		foreach ($rows as $row)
		{
			// Convert the array into an object.
			$row = (object) $row;

			if (!$this->db_new->insertObject($table, $row)) {
				throw new Exception($this->db_new->getErrorMsg());
			}
		}
	}

	/**
	 * The public entry point for the class.
	 *
	 * @return	boolean
	 * @since	0.4.
	 */
	public function upgrade()
	{
		try
		{
			$this->setDestinationData();
		}
		catch (Exception $e)
		{
			echo JError::raiseError(500, $e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Cleanup the data in the destination database.
	 *
	 * @return	void
	 * @since	0.5.1
	 * @throws	Exception
	 */
	protected function cleanDestinationData($table = false)
	{
		// Get the table
		if ($table == false) {
			$table	= empty($this->destination) ? $this->source : $this->destination;
		}

		$query = "TRUNCATE TABLE {$table}";
		$this->db_new->setQuery($query);
		$this->db_new->query();

		// Check for query error.
		$error = $this->db_new->getErrorMsg();

		if ($error) {
			throw new Exception($error);
		}

	}

	/**
	 * Copy table to old site to new site
	 *
	 * @return	void
	 * @since	0.5.1
	 * @throws	Exception
	 */
	protected function copyTable($from, $to) {

		// Check if table exists
		$database = $this->config['database'];

		$query = "SELECT COUNT(*) AS count
      FROM information_schema.tables
      WHERE table_schema = '$database'
      AND table_name = '$from'";

		$this->db_new->setQuery($query);
		$res = $this->db_new->loadResult();

		//
	  if($res == 0) {
      $success = false;
	  } else {
      $query = "CREATE TABLE {$to} LIKE {$from}";
			$this->db_new->setQuery($query);
			//$this->db_new->query();

			// Check for query error.
			$error = $this->db_new->getErrorMsg();

			if ($error) {
				throw new Exception($error);
			}

      $query = "INSERT INTO {$to} SELECT * FROM {$from}";
			$this->db_new->setQuery($query);
			//$this->db_new->query();

			// Check for query error.
			$error = $this->db_new->getErrorMsg();

			if ($error) {
				throw new Exception($error);
			}

      $success = true;
	  }
	 
	  return $success;
	}


	/**
	 * Inserts a category
	 *
	 * @access  public
	 * @param   object  An object whose properties match table fields
	 * @since	0.4.
	 */
	public function insertCategory($object, $parent = false)
	{
		// Get old id
		$oldlist = new stdClass();
		$oldlist->section = $object->extension;
		$oldlist->old = $object->sid;
		unset($object->sid);

		// Correct extension
		if ($object->extension == "com_banner") {
			$object->extension = "com_banners";
		}

		if (is_numeric($object->extension) || $object->extension == "" || $object->extension == "category") {
			$object->extension = "com_content";
		}

		// If has parent made $path and get parent id
		if ($parent !== false) {
			$object->path = JFilterOutput::stringURLSafe($parent)."/".$object->alias;

			// Fixing title quote
			$parent = str_replace("'", "&#39;", $parent);

			$query = "SELECT id FROM #__categories WHERE title = '{$parent}' LIMIT 1";
			$this->db_new->setQuery($query);
			$object->parent_id = $this->db_new->loadResult();

			// Check for query error.
			$error = $this->db_new->getErrorMsg();

			if ($error) {
				throw new Exception($error);
			}

		}
		else {
			$object->parent_id = 1;
			$object->path = $object->alias;

			// Fixing extension name if it's section
			if ($object->extension == 'com_section') {
				$object->extension = "com_content";
			}
		}

		// Insert the row
		if (!$this->db_new->insertObject('#__categories', $object)) {
			throw new Exception($this->db_new->getErrorMsg());
		}

		// Returning sid needed by insertAsset()
		$object->sid = $oldlist->old;

		// Get new id
		$oldlist->new = $this->db_new->insertid();

		// Save old and new id
		if (!$this->db_new->insertObject('#__jupgrade_categories', $oldlist)) {
			throw new Exception($this->db_new->getErrorMsg());
		}

	 	return true;
	}

	/**
	 * Inserts asset
	 *
	 * @access  public
	 * @param   object  An object whose properties match table fields
	 * @param   string  The parent title
	 * @since	0.4.
	 */
	public function insertAsset($object, $parent = false)
	{
		// Getting the asset table
		$table = JTable::getInstance('Asset', 'JTable', array('dbo' => $this->db_new));

		// Getting the categories id's
		$categories = $this->getMapList();

		//
		// Correct extension
		//
		if ($object->extension != 'article') {
			$sid = isset($object->sid) ? $object->sid : $object->id ;
			$id = $categories[$sid]->new;
			$updatetable = '#__categories';

			if ($object->extension == "com_banners") {
				$table->name = "com_banners.category.{$id}";
				$table->parent_id = 3;
			}
			else if ($object->extension == "com_contact_details") {
				$table->name = "com_contact.category.{$id}";
				$table->parent_id = 7;
			}
			else if ($object->extension == "com_newsfeeds") {
				$table->name = "com_newsfeeds.category.{$id}";
				$table->parent_id = 19;
			}
			else if ($object->extension == "com_weblinks") {
				$table->name = "com_weblinks.category.{$id}";
				$table->parent_id = 25;
				$table->level = 2;
			}
			else if (is_numeric($object->extension) || $object->extension == 'com_content') {
				$table->name = "com_content.category.{$id}";

				// Get parent and level
				if ($parent !== false) {
					// Fixing title quote
					$parent = str_replace("'", "&#39;", $parent);

					$query = "SELECT id FROM #__assets WHERE title = '{$parent}' LIMIT 1";
					$this->db_new->setQuery($query);
					$table->parent_id = $this->db_new->loadResult();
					$table->level = 3;
				}
				else {
					$table->level = 2;
					$table->parent_id = 8;
				}
			}

		}
		else if ($object->extension == "article") {
			$updatetable = '#__content';
			$id = $object->id;
			$table->name = "com_content.article.{$id}";
			$table->parent_id = 8;
			$table->level = 2;
		}

		// Setting rules values
		$table->rules = '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}';
		$table->title = mysql_real_escape_string($object->title);

		// Insert the asset
		$table->store();

		// Returning sid needed by childen categories
		$object->sid = isset($sid) ? $sid : $object->id ;

		// updating the category asset_id;
		$query = "UPDATE {$updatetable} SET asset_id = {$table->id}"
		." WHERE id = {$id}";
		$this->db_new->setQuery($query);
		$this->db_new->query();

		// Check for query error.
		$error = $this->db_new->getErrorMsg();

		if ($error) {
			throw new Exception($error);
			return false;
		}

		return true;
	}

	/**
	 * Internal function to debug
	 *
	 * @return	a better version of print_r
	 * @since	0.4.5
	 * @throws	Exception
	 */
	public function print_a($subject)
	{
		echo str_replace("=>","&#8658;",str_replace("Array","<font color=\"red\"><b>Array</b></font>",nl2br(str_replace(" "," &nbsp; ",print_r($subject,true)))));
	}


	/**
	 * Internal function to get original database prefix
	 *
	 * @return	an original database prefix
	 * @since	0.5.3
	 * @throws	Exception
	 */
	public function getPrefix()
	{
		// configuration.php path
		$filename = JPATH_ROOT.DS.'configuration.php';

		// read the file
		$handle = fopen($filename, "r");
		$contents = fread($handle, filesize($filename));
		fclose($handle);

		// grep the dbprefix line
		$pattern = '/dbprefix\ = (.*)/';
		preg_match($pattern, $contents, $matches);
		$prefix = $matches[1];

		// Strip all trash
		$prefix = explode(";", $prefix);
		$prefix = $prefix[0];
		return $prefix = trim($prefix, "'");

	}

	/**
	 * Internal function to get original database prefix
	 *
	 * @return	an original database prefix
	 * @since	0.5.3
	 * @throws	Exception
	 */
	public function getMapList($table = 'categories', $section = false)
	{
		// Getting the categories id's
		$query = "SELECT *"
		." FROM j16_jupgrade_{$table}";

		if ($section !== false) {
			$query .= " WHERE section = '{$section}'";
		}

		$this->db_new->setQuery($query);
		$data = $this->db_new->loadObjectList('old');

		// Check for query error.
		$error = $this->db_new->getErrorMsg();

		if ($error) {
			throw new Exception($error);
			return false;
		}

		return $data;
	}

	/**
	 * Internal function to get the component settings
	 *
	 * @return	an object with global settings
	 * @since	0.5.7
	 * @throws	Exception
	 */
	public function getParams()
	{
		// Getting the categories id's
		$query = "SELECT params
							FROM #__components AS c
							WHERE c.option = 'com_jupgrade'";

		$this->db_old->setQuery($query);
		$params = $this->db_old->loadResult();

		// Check for query error.
		$error = $this->db_old->getErrorMsg();

		if ($error) {
			throw new Exception($error);
			return false;
		}

		$temp	= new JParameter($params);
		$object	= $temp->toObject();

		return $object;
	}

	/**
	 * Internal function to get the component settings
	 *
	 * @return	an object with global settings
	 * @since	0.5.7
	 * @throws	Exception
	 */
	public function getRequirements()
	{
		$requirements = array();

		$requirements['phpMust'] = '5.2';
		$requirements['phpIs'] = PHP_VERSION;

		$requirements['mysqlMust'] = '5.0';
		$requirements['mysqlIs'] = mysql_get_server_info();

		return $requirements;
	}
}


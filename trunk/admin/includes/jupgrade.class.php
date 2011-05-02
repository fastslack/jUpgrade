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
	protected $id = 0;
	protected $lastid = 0;
	protected $name = 'undefined';
	protected $state = null;
	protected $xml = null;
	protected $ready = true;
	protected $output = '';

	public $config = array();
	public $config_old = array();
	public $db_old = null;
	public $db_new = null;

	function __construct($step = null)
	{
		// Set timelimit to 0
		if(!ini_get('safe_mode')) {
			set_time_limit(0);
		}

		if ($step) {
			$this->id = $step->id;
			$this->lastid = $step->lastid;
			$this->name = $step->name;
			$this->state = json_decode($step->state);
			if (isset($this->state->xmlfile)) {
				// Read xml definition file
				$this->xml = simplexml_load_file($this->state->xmlfile);
			}
		}
		$this->checkTimeout();

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

		// File and folder management
		jimport('joomla.filesystem.folder');

		// Other stuff
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
		//$this->print_a($query->__toString());

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
	protected function setDestinationData($rows = null)
	{
		// Get the source data.
		if ($rows === null) {
			$rows = $this->getSourceData();
		}
		$table = empty($this->destination) ? $this->source : $this->destination;

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
	 * Clone table structure from old site to new site
	 *
	 * @return	boolean
	 * @since 1.1.0
	 * @throws	Exception
	 */
	protected function cloneTable($from, $to=null, $drop=true) {
		// Check if table exists
		$database = $this->config_old['database'];
		if (!$to) $to = $from;
		$from = preg_replace ('/#__/', $this->db_old->getPrefix(), $from);
		$to = preg_replace ('/#__/', $this->db_new->getPrefix(), $to);

		$query = "SELECT COUNT(*) AS count
			FROM information_schema.tables
			WHERE table_schema = '$database'
			AND table_name = '$from'";

		$this->db_old->setQuery($query);
		$res = $this->db_old->loadResult();

		if($res == 0) {
			$success = false;
		} else {
			if ($drop) {
				$query = "DROP TABLE IF EXISTS {$to}";
				$this->db_new->setQuery($query);
				$this->db_new->query();

				// Check for query error.
				$error = $this->db_new->getErrorMsg();

				if ($error) {
					throw new Exception($error);
				}
			}
			$query = "CREATE TABLE {$to} LIKE {$from}";
			$this->db_new->setQuery($query);
			$this->db_new->query();

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
	 * Copy table to old site to new site
	 *
	 * @return	boolean
	 * @since 1.1.0
	 * @throws	Exception
	 */
	protected function copyTable($from, $to=null) {

		// Check if table exists
		$database = $this->config_old['database'];
		if (!$to) $to = $from;
		$from = preg_replace ('/#__/', $this->db_old->getPrefix(), $from);
		$to = preg_replace ('/#__/', $this->db_new->getPrefix(), $to);

		$success = $this->cloneTable($from, $to);
		if ($success) {
			$query = "INSERT INTO {$to} SELECT * FROM {$from}";
			$this->db_new->setQuery($query);
			$this->db_new->query();

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
	 * Save internal state.
	 *
	 * @return	boolean
	 * @since	1.1.0
	 */
	public function saveState()
	{
		// Cannot save state if step is not defined
		if (!$this->name) return false;

		$state = json_encode($this->state);
		$query = "UPDATE j16_jupgrade_steps SET state = {$this->db_new->quote($state)} WHERE name = {$this->db_new->quote($this->name)}";
		$this->db_new->setQuery($query);
		$this->db_new->query();

		// Check for query error.
		$error = $this->db_new->getErrorMsg();

		return !$error;
	}

	/**
	 * Check if this migration has been completed.
	 *
	 * @return	boolean
	 * @since	1.1.0
	 */
	public function isReady()
	{
		return $this->ready;
	}

	/**
	 * The public entry point for the class.
	 *
	 * @return	boolean
	 * @since	1.1.0
	 */
	public function upgradeExtension()
	{
		try
		{
			// Detect
			if (!$this->detectExtension())
			{
				return false;
			}

			// Migrate
			$this->ready = $this->migrateExtensionTables();
			if ($this->ready)
			{
				$this->ready = $this->migrateExtensionFolders();
			}
			if ($this->ready)
			{
				$this->ready = $this->migrateExtensionCustom();
			}

			// Store state
			$this->saveState();
		}
		catch (Exception $e)
		{
			echo JError::raiseError(500, $e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Check if extension migration is supported.
	 *
	 * @return	boolean
	 * @since	1.1.0
	 */
	protected function detectExtension()
	{
		return true;
	}

	/**
	 * Get update site information
	 *
	 * @return	array	Update site information or null
	 * @since	1.1.0
	 */
	protected function getUpdateSite() {
		if (empty($this->xml->updateservers->server[0])) {
			return null;
		}
		$server = $this->xml->updateservers->server[0];
		if (empty($server['url'])) {
			return null;
		}
		return array(
			'type'=> ($server['type'] ? $server['type'] : 'extension'),
			'priority'=> ($server['priority'] ? $server['priority'] : 1),
			'name'=> ($server['name'] ? $server['name'] : $this->name),
			'url'=> $server['url']
		);
	}

	/**
	 * Get folders to be migrated.
	 *
	 * @return	array	List of tables without prefix
	 * @since	1.6.4
	 */
	protected function getCopyFolders() {
		$folders = !empty($this->xml->folders->folder) ? $this->xml->folders->folder : array();
		$results = array();
		foreach ($folders as $folder) {
			$results[] = (string) $folder;
		}
		return $results;
	}

	/**
	 * Get directories to be migrated.
	 *
	 * @return	array	List of directories
	 * @since	1.6.4
	 */
	protected function getCopyTables() {
		$tables = !empty($this->xml->tables->table) ? $this->xml->tables->table : array();
		$results = array();
		foreach ($tables as $table) {
			$results[] = (string) $table;
		}
		return $results;
	}

	/**
	 * Migrate the database tables.
	 *
	 * @return	boolean
	 * @since	1.1.0
	 */
	protected function migrateExtensionTables()
	{
		if (!isset($this->state->tables))
		{
			$this->state->tables = $this->getCopyTables();
		}
		while(($value = array_shift($this->state->tables)) !== null) {
			$this->output("{$this->name} #__{$value}");
			$copyTableFunc = 'copyTable_'.$value;
			if (method_exists($this, $copyTableFunc)) {
				// Use function called like copyTable_kunena_categories
				$ready = $this->$copyTableFunc($value);
			} else {
				// Use default migration function
				$table = "#__$value";
				$this->copyTable($table);
				$ready = true;
			}
			// If table hasn't been fully copied, we need to push it back to stack
			if (!$ready) {
				array_unshift($this->state->tables, $value);
			}
			if ($this->checkTimeout()) {
				break;
			}
		}
		return empty($this->state->tables);
	}

	/**
	 * Migrate the folders.
	 *
	 * @return	boolean
	 * @since	1.1.0
	 */
	protected function migrateExtensionFolders()
	{
		if (!isset($this->state->folders))
		{
			$this->state->folders = $this->getCopyFolders();
		}
		$oldpath = substr(JPATH_SITE, 0, -8);
		while(($value = array_shift($this->state->folders)) !== null) {
			$this->output("{$this->name} {$value}");
			$src = $oldpath.$value;
			$dest = JPATH_SITE.DS.$value;
			JFolder::copy($src, $dest);
			if ($this->checkTimeout()) {
				break;
			}
		}
		return empty($this->state->folders);
	}

	/**
	 * Migrate custom information.
	 *
	 * @return	boolean Ready
	 * @since	1.1.0
	 */
	protected function migrateExtensionCustom()
	{
		return true;
	}

	/**
	 * A hook to be able to modify params prior as they are converted to JSON.
	 *
	 * @param	object	$object	A reference to the parameters as an object.
	 *
	 * @return	void
	 * @since	1.1.0
	 * @throws	Exception
	 */
	protected function migrateExtensionDataHook()
	{
		// Do customisation of the params field here for specific data.
	}

	/**
	 * Function to output text back to user
	 *
	 * @return	string Previous output
	 * @since	1.1.0
	 */
	public function output($text='')
	{
		$output = empty($this->output) ? $this->name : $this->output;
		$this->output = $text;
		return $output;
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
	 * Get the mapping of the old usergroups to the new usergroup id's.
	 *
	 * @return	array	An array with keys of the old id's and values being the new id's.
	 * @since	1.1.0
	 */
	protected function getUsergroupIdMap()
	{
		$map = array(
			// Old	=> // New
			28		=> 1,	// USERS
			29		=> 1,	// Public Frontend
			18		=> 2,	// Registered
			19		=> 3,	// Author
			20		=> 4,	// Editor
			21		=> 5,	// Publisher
			30		=> 1,	// Public Backend
			23		=> 6,	// Manager
			24		=> 7,	// Administrator
			25		=> 8,	// Super Administrator
		);

		return $map;
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
		$requirements['mysqlIs'] = $this->db_old->getVersion();

		return $requirements;
	}

	/**
	 * Internal function to check if 5 seconds has been passed
	 *
	 * @return	bool	true / false
	 * @since	1.1.0
	 */
	protected function checkTimeout($stop = false) {
		static $start = null;
		if ($stop) $start = 0;
		$time = microtime (true);
		if ($start === null) {
			$start = $time;
			return false;
		}
		if ($time - $start < 5)
			return false;

		return true;
	}
}
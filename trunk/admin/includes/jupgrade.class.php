<?php
/**
 * jUpgrade
 *
 * @version		  $Id$
 * @package		  MatWare
 * @subpackage	com_jupgrade
 * @author      Matias Aguirre <maguirre@matware.com.ar>
 * @link        http://www.matware.com.ar
 * @copyright		Copyright 2006 - 2011 Matias Aguirre. All rights reserved.
 * @license		  GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * jUpgrade utility class for migrations
 *
 * @package		MatWare
 * @subpackage	com_jupgrade
 */
class jUpgrade
{
	/**
	 * Parameters
	 * @since	0.4.
	 */
	public    $canDrop = false;
	protected $source = null;
	protected $id = 0;
	protected $lastid = 0;
	protected $name = 'undefined';
	protected $state = null;
	protected $xml = null;
	protected $ready = true;
	protected $output = '';
	protected $params = null;

	protected $usergroup_map = array(
			// Old	=> // New
			0		=> 0,	// ROOT
			28		=> 1,	// USERS (=Public)
			29		=> 1,	// Public Frontend
			18		=> 2,	// Registered
			19		=> 3,	// Author
			20		=> 4,	// Editor
			21		=> 5,	// Publisher
			30		=> 6,	// Public Backend (=Manager)
			23		=> 6,	// Manager
			24		=> 7,	// Administrator
			25		=> 8,	// Super Administrator
		);

	public $config = array();
	public $config_old = array();
	public $db_old = null;
	public $db_new = null;

	function __construct($step = null)
	{
		if ($step) {
			$this->id = $step->id;
			$this->lastid = isset($step->lastid) ? $step->lastid : 0;
			$this->name = $step->name;
			$this->state = json_decode($step->state);
			if (isset($this->state->xmlfile)) {
				// Read xml definition file
				$this->xml = simplexml_load_file($this->state->xmlfile);
			}
		}
		$this->checkTimeout();

		// Base includes
		if (file_exists(JPATH_LIBRARIES.'/joomla/import.php')) {
			require_once JPATH_LIBRARIES.'/joomla/import.php';
		}else if (file_exists(JPATH_LIBRARIES.'/import.php')) {
			require_once JPATH_LIBRARIES.'/import.php';
		}
		if (file_exists(JPATH_LIBRARIES.'/joomla/config.php')) {
			require_once JPATH_LIBRARIES.'/joomla/config.php';
		}
		if (file_exists(JPATH_LIBRARIES.'/joomla/log/log.php')) {
			require_once JPATH_LIBRARIES.'/joomla/log/log.php';
		}
		if (file_exists(JPATH_LIBRARIES.'/joomla/methods.php')) {
			require_once JPATH_LIBRARIES.'/joomla/methods.php';
		}
		require_once JPATH_LIBRARIES.'/joomla/factory.php';
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
		jimport('joomla.environment.uri');

		// Echo all errors, otherwise things go really bad.
		//JError::setErrorHandling(E_ALL, 'echo');

		// Manually
		//JTable::addIncludePath(JPATH_LIBRARIES.'/joomla/database/table');

		$jconfig = new JConfig();

		$this->config['driver']   = $jconfig->dbtype;
		$this->config['host']     = $jconfig->host;
		$this->config['user']     = $jconfig->user;
		$this->config['password'] = $jconfig->password;
		$this->config['database'] = $jconfig->db;

		$this->config_old = $this->config;
		$this->config_old['prefix'] = $this->getPrefix();

		// Creating old dabatase instance
		$this->db_old = JDatabase::getInstance($this->config_old);

		// Getting the params
		$this->params = $this->getParams();

		// Setting the new prefix to the db instance
		$this->config['prefix'] = isset($this->params->prefix_new) ? $this->params->prefix_new : 'j17_';
		// Creating new dabatase instance
		$this->db_new = JDatabase::getInstance($this->config);

		// Set timelimit to 0
		if(!@ini_get('safe_mode')) {
			if (!empty($this->params->timelimit)) {
				set_time_limit(0);
			}
		}

		// Make sure we can see all errors.
		if (!empty($this->params->error_reporting)) {
			error_reporting(E_ALL);
			@ini_set('display_errors', 1);
		}

		// MySQL grants check
		$query = "SHOW GRANTS FOR CURRENT_USER";
		$this->db_new->setQuery( $query );
		$list = $this->db_new->loadRowList();
		$grant = isset($list[1][0]) ? $list[1][0] : $list[0][0];
		$grant = empty($list[1][0]) ? $list[0][0] : $list[1][0];

		if (strpos($grant, 'DROP') == true || strpos($grant, 'ALL') == true) {
			$this->canDrop = true;
		}
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
	protected function &getSourceData($select = '*', $join = null, $where = null, $order = null, $groupby  = null, $debug = null)
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

		// Add group statement if exists
		if (!empty($groupby))
			$query->group($groupby);

		// Check if 'order' clause is set
		if (!empty($order))
			$query->order($order);

		// Debug
		if (!empty($debug))
			$this->print_a($query->__toString());

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
			throw new Exception($e->getMessage());
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

		if ($this->canDrop) {
			$query = "TRUNCATE TABLE {$table}";
			$this->db_new->setQuery($query);
			$this->db_new->query();
		} else {
			$query = "DELETE FROM {$table}";
			$this->db_new->setQuery($query);
			$this->db_new->query();
		}

		// Check for query error.
		$error = $this->db_new->getErrorMsg();

		if ($error) {
			throw new Exception($error);
		}

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
			/*
			if ($drop) {
				if ($this->canDrop) {
					$query = "DROP TABLE IF EXISTS {$to}";
					$this->db_new->setQuery($query);
					$this->db_new->query();
				} else {
					$query = "DELETE FROM {$to}";
					$this->db_new->setQuery($query);
					$this->db_new->query();
				}

				// Check for query error.
				$error = $this->db_new->getErrorMsg();

				if ($error) {
					throw new Exception($error);
				}
			}
			*/
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
		$query = "UPDATE jupgrade_steps SET state = {$this->db_new->quote($state)} WHERE name = {$this->db_new->quote($this->name)}";
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
	 * Internal function to convert numeric html entities to UTF-8.
	 *
	 * @return	string
	 * @since	2.5.2
	 */
	public function entities2Utf8($input)
	{
		return html_entity_decode($input, ENT_QUOTES, 'UTF-8');
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
		$filename = JPATH_ROOT.'/configuration.php';

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
		." FROM jupgrade_{$table}";

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
		return $this->usergroup_map;
	}

	/**
	 * Map old user group from Joomla 1.5 to new installation.
	 *
	 * @return	int	New user group
	 * @since	1.2.2
	 */
	protected function mapUserGroup($id) {
		return isset($this->usergroup_map[$id]) ? $this->usergroup_map[$id] : $id;
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

		$jconfig = new JConfig();

		// Correct params for jUpgradeCli
		if (!empty($jconfig->cli) && $jconfig->cli == 1) {

			$object = new stdClass();

			$object->cli = $jconfig->cli;
			$object->dbtype = $jconfig->dbtype;
			$object->host = $jconfig->host;
			$object->user = $jconfig->user;
			$object->password = $jconfig->password;
			$object->db = $jconfig->db;
			$object->dbprefix = $jconfig->dbprefix;
			$object->prefix_new = $jconfig->prefix_new;
			$object->timelimit = $jconfig->timelimit;
			$object->error_reporting = $jconfig->error_reporting;
			$object->positions = $jconfig->positions;

		}else{
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
		}

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
		// Check if server is linux
		ob_start();
		phpinfo(1);
		$phpinfo = ob_get_contents();
		ob_end_clean();
		$phpinfo = preg_replace("'<style[^>]*>.*</style>'siU",'',$phpinfo);
		$phpinfo = strip_tags($phpinfo);
		$exp = explode(" ", $phpinfo);

		$requirements = array();

		$requirements['phpMust'] = '5.2.4';
		$requirements['phpIs'] = PHP_VERSION;

		$requirements['mysqlMust'] = '5.1';
		if ($exp[3] == 'Linux') {
			$requirements['mysqlMust'] = '5.0.4';
		}
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

	/**
	 *
	 * Gets the changeset object
	 *
	 * @return  JSchemaChangeset
	 */
	public function getChangeSet()
	{
		$folder = JPATH_ADMINISTRATOR . '/components/com_admin/sql/updates/';
		$changeSet = JSchemaChangeset::getInstance(JFactory::getDbo(), $folder);
		return $changeSet;
	}

	/**
	 * Get version from #__schemas table
	 *
	 * @return  mixed  the return value from the query, or null if the query fails
	 * @throws Exception
	 */

	public function getSchemaVersion() {
		$db = $this->db_new;
		$query = $db->getQuery(true);
		$query->select('version_id')->from($db->qn('#__schemas'))
		->where('extension_id = 700');
		$db->setQuery($query);
		$result = $db->loadResult();
		if ($db->getErrorNum()) {
			throw new Exception('Database error - getSchemaVersion');
		}
		return $result;
	}

	/**
	 * Fix schema version if wrong
	 *
	 * @param JSchemaChangeSet
	 *
	 * @return   mixed  string schema version if success, false if fail
	 */
	public function fixSchemaVersion($changeSet)
	{
		// Get correct schema version -- last file in array
		$schema = $changeSet->getSchema();
		$db = $this->db_new;
		$result = false;

		// Check value. If ok, don't do update
		$version = $this->getSchemaVersion();
		if ($version == $schema)
		{
			$result = $version;
		}
		else
		{
			// Delete old row
			$query = $db->getQuery(true);
			$query->delete($db->qn('#__schemas'));
			$query->where($db->qn('extension_id') . ' = 700');
			$db->setQuery($query);
			$db->query();

			// Add new row
			$query = $db->getQuery(true);
			$query->insert($db->qn('#__schemas'));
			$query->set($db->qn('extension_id') . '= 700');
			$query->set($db->qn('version_id') . '= ' . $db->q($schema));
			$db->setQuery($query);
			if ($db->query()) {
				$result = $schema;
			}
		}
		return $result;
	}
}

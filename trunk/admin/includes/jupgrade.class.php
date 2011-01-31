<?php
/**
 * jUpgrade
 *
 * @version		  $Id$
 * @package		  MatWare
 * @subpackage	com_jupgrade
 * @author      Matias Aguirre <maguirre@matware.com.ar>
 * @link        http://www.matware.com.ar
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
		// Base includes
		require_once JPATH_LIBRARIES.DS.'joomla'.DS.'import.php';
		require_once JPATH_LIBRARIES.DS.'joomla'.DS.'methods.php';
		require_once JPATH_LIBRARIES.DS.'joomla'.DS.'factory.php';
		require_once JPATH_LIBRARIES.DS.'joomla'.DS.'import.php';
		require_once JPATH_LIBRARIES.DS.'joomla'.DS.'config.php';
		require_once JPATH_ROOT.DS.'jupgrade'.DS.'configuration.php';

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

		$this->config['driver']   = 'mysql';
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
	protected function cleanDestinationData()
	{
		// Get the table
		$table	= empty($this->destination) ? $this->source : $this->destination;

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
	 * Inserts a category
	 *
	 * @access  public
	 * @param   object  An object whose properties match table fields
	 * @since	0.4.
	 */
	public function insertCategory($object, $parent = false)
	{
		
		$title = $object->title;
		$alias = $object->alias;
		$description = $object->description;
		$published = $object->published;
		$access = $object->access + 1;
		$checked_out = $object->checked_out;
		$checked_out_time = $object->checked_out_time;
		$params = $object->params;
		
		//$extension = $object->section;
		$extension = "com_content";

		// Correct extension
		if (is_numeric($extension) || $extension == "") {
			$extension = "com_content";
		}
		if ($extension == "com_banner") {
			$extension = "com_banners";
		}
		if ($extension == "com_contact_detail") {
			$extension = "com_contact";
		}

		// Correct alias
		if ($alias == "") {
			$alias = JFilterOutput::stringURLSafe($title);
		}

		// Get parent
		if ($parent !== false) {
			$path = JFilterOutput::stringURLSafe($parent)."/".$alias;

			$query = "SELECT id FROM #__categories WHERE title = '{$parent}' LIMIT 1";
			$this->db_new->setQuery($query);
			$parent_query = $this->db_new->loadResult();

			// Check for query error.
			$error = $this->db_new->getErrorMsg();

			if ($error) {
				throw new Exception($error);
			}

			$level = 2;
		}	else {
			$parent_query = 1;
			$level = 1;
			$path = $alias;
		}

		// Insert Category
		$query = "INSERT INTO #__categories"
		." (`parent_id`, `path`,`extension`,`title`,`alias`,`description`, `published`, `checked_out`, `checked_out_time`, `params`, `access`, `language`)"
		." VALUES({$parent_query}, '{$path}', '{$extension}', '{$title}', '{$alias}', '{$description}', {$published}, '{$checked_out}', '{$checked_out_time}', '{$params}', {$access}, '*') ";
		$this->db_new->setQuery($query);
		$this->db_new->query();

		// Check for query error.
		$error = $this->db_new->getErrorMsg();

		if ($error) {
			throw new Exception($error);
		}

		// Get new id
		$new = $this->db_new->insertid();

		// Getting old id and save it
		$old = $object->sid;

		$query = "INSERT INTO #__jupgrade_categories"
		." (`old`,`new`)"
		." VALUES({$old}, {$new}) ";
		$this->db_new->setQuery($query);
		$this->db_new->query();

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
	public function insertAsset($object, $parent = false) {

		// Content or category?
		if ($object->id) {
			$object->sid = $object->id;
			$name = "com_content.article.{$object->sid}";
		}else{
			$name = "com_content.category.{$object->sid}";
		}

		// Get parent and level
		if ($parent !== false) {
			$query = "SELECT id FROM #__assets WHERE title = '{$parent}' LIMIT 1";
			$this->db_new->setQuery($query);
			$parent = $this->db_new->loadResult();
			$level = 3;
		}	else {
			$parent = 8;
			$level = 2;
		}

		// Setting rules values
		$rules = '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}';
		$title = mysql_real_escape_string($object->title);

		// Insert Asset
		$query = "INSERT INTO #__assets"
		." (`parent_id`, `name`, `title`, `level`, `rules`)"
		." VALUES({$parent}, '{$name}', '{$title}', '{$level}', '{$rules}') ";
		$this->db_new->setQuery($query);
		$this->db_new->query();	

		// Check for query error.
		$error = $this->db_new->getErrorMsg();

		if ($error) {
			throw new Exception($error);
			return false;
		}	

		// Get new id
		$assetid = $this->db_new->insertid();

		if ($object->id) {
			// updating the categori asset_id;
			$query = "UPDATE #__categories SET asset_id={$assetid}"
			." WHERE id = {$object->sid}";
			$this->db_new->setQuery($query);
			$this->db_new->query();

			// Check for query error.
			$error = $this->db_new->getErrorMsg();

			if ($error) {
				throw new Exception($error);
				return false;
			}	
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
	public function print_a($subject){
		echo str_replace("=>","&#8658;",str_replace("Array","<font color=\"red\"><b>Array</b></font>",nl2br(str_replace(" "," &nbsp; ",print_r($subject,true)))));
	}


	/**
	 * Internal function to get original database prefix
	 *
	 * @return	an original database prefix
	 * @since	0.5.3
	 * @throws	Exception
	 */
	public function getPrefix(){

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

}

<?php
/**
 * jUpgrade
 *
 * @version		$Id$
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @copyright	Copyright 2006 - 2011 Matias Aguire. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @author		Matias Aguirre <maguirre@matware.com.ar>
 * @link		http://www.matware.com.ar
 */

/**
 * Upgrade class for 3rd party extensions
 *
 * This class search for extensions to be migrated
 *
 * @since	0.4.5
 */
class jUpgradeExtensions extends jUpgrade
{
	/**
	 * count adapters
	 * @var int
	 * @since	1.1.0
	 */
	public $count = 0;
	protected $extensions = array();

	public function getInstance($step) {
		static $instances = array();

		if (!isset($instances[$step->name])) {
			if ($step->name == 'extensions') {
				return new jUpgradeExtensions($step);
			}
			$state = json_decode($step->state);

			// Try to load the adapter object
			if (file_exists($state->phpfile)) {
				require_once $state->phpfile;
			}

			if (class_exists($state->class)) {
				$instances[$step->name] = new $state->class($step);
			} else {
				$instances[$step->name] = new jUpgradeExtensions($step);
			}
		}
		return $instances[$step->name];
	}

	public function upgrade()
	{
		if (!$this->upgradeComponents()) {
			return false;
		}
		if (!$this->upgradeModules()) {
			return false;
		}
		if (!$this->upgradePlugins()) {
			return false;
		}

		if (empty($this->params->cli)) {
			if (!$this->upgradeTemplates()) {
				return false;
			}
		}
		$this->_processExtensions();

		return true;
	}

	/**
	 * Upgrade the components
	 *
	 * @return	
	 * @since	1.1.0
	 * @throws	Exception
	 */
	protected function upgradeComponents()
	{
		$this->source = '#__components AS c';
		$this->destination = '#__extensions';

		$where = array();
		$where[] = "c.parent = 0";
		$where[] = "c.option NOT IN ('com_admin', 'com_banners', 'com_cache', 'com_categories', 'com_checkin', 'com_config', 'com_contact', 'com_content', 'com_cpanel', 'com_frontpage', 'com_installer', 'com_jupgrade', 'com_languages', 'com_login', 'com_mailto', 'com_massmail', 'com_media', 'com_menus', 'com_messages', 'com_modules', 'com_newsfeeds', 'com_plugins', 'com_poll', 'com_search', 'com_sections', 'com_templates', 'com_user', 'com_users', 'com_weblinks', 'com_wrapper' )";

		$rows = parent::getSourceData(
			'name, \'component\' AS type, `option` AS element, 1 AS client_id, params',
		 null,
		 $where,
			'id'
		);

		$this->_addExtensions ( $rows, 'com' );
		return true;
	}

	/**
	 * Upgrade the modules
	 *
	 * @return	
	 * @since	1.1.0
	 * @throws	Exception
	 */
	protected function upgradeModules()
	{
		$this->source = "#__modules";
		$this->destination = "#__extensions";

		$where = array();
		$where[] = "module   NOT   IN   ('mod_mainmenu',   'mod_login',   'mod_popular',   'mod_latest',   'mod_stats',   'mod_unread',   'mod_online',   'mod_toolbar',   'mod_quickicon',   'mod_logged',   'mod_footer',   'mod_menu',   'mod_submenu',   'mod_status',   'mod_title',   'mod_login' )";

		$rows = parent::getSourceData(
			'title as name, \'module\' AS type, `module` AS element, params',
		  null,
		  $where,
			'id',
		  'element'
		);

		$this->_addExtensions ( $rows, 'mod' );
		return true;
	}

	/**
	 * Upgrade the plugins
	 *
	 * @return
	 * @since	1.1.0
	 * @throws	Exception
	 */
	protected function upgradePlugins()
	{
		$this->source = "#__plugins";
		$this->destination = "#__extensions";

		$where = array();
		$where[] = "element   NOT   IN   ('joomla',   'ldap',   'gmail',   'openid',   'content',   'categories',   'contacts',   'sections',   'newsfeeds',   'weblinks',   'pagebreak',   'vote',   'emailcloak',   'geshi',   'loadmodule',   'pagenavigation', 'none',   'tinymce',   'xstandard',   'image',   'readmore',   'sef',   'debug',   'legacy',   'cache',   'remember', 'backlink', 'log', 'blogger', 'mtupdate' )";

		$rows = parent::getSourceData(
			'name, \'plugin\' AS type, element, folder, client_id, ordering, params',
		  null,
		  $where,
			'id',
		  'element'
		);

		$this->_addExtensions ( $rows, 'plg' );
		return true;
	}

	/**
	 * Get the raw data for this part of the upgrade.
	 *
	 * @return	array	Returns a reference to the source data array.
	 * @since	0.4.5
	 * @throws	Exception
	 */
	protected function upgradeTemplates()
	{
		$this->destination = "#__extensions";

		$folders = JFolder::folders(JPATH_ROOT.DS.'templates');
		$folders = array_diff($folders, array("system", "beez"));
		sort($folders);
		//print_r($folders);

		$rows = array();
		// Do some custom post processing on the list.
		foreach($folders as $folder) {

			$row = array();
			$row['name'] = $folder;
			$row['type'] = 'template';
			$row['element'] = $folder;
			$row['params'] = '';
			$rows[] = $row;
		}

		$this->_addExtensions ( $rows, 'tpl' );
		return true;
	}

	protected function _addExtensions( $rows, $prefix )
	{
		// Create new indexed array
		foreach ($rows as &$row)
		{
			// Convert the array into an object.
			$row = (object) $row;
			$row->id = null;
			$row->element = strtolower($row->element);
			//$row->client_id = 0;
			// Ensure that name is always using form: xxx_folder_name
			$name = preg_replace('/^'.$prefix.'_/', '', $row->element);
			if (!empty($row->folder)) {
				$element = preg_replace('/^'.$row->folder.'_/', '', $row->element);
				$row->name = ucfirst($row->folder).' - '.ucfirst($element);
				$name = $row->folder.'_'.$element;
			}
			$name = $prefix .'_'. $name;
			$this->extensions[$name] = $row;
		}
	}

	/**
	 * Get the raw data for this part of the upgrade.
	 *
	 * @return	array	Returns a reference to the source data array.
	 * @since 1.1.0
	 * @throws	Exception
	 */
	protected function _processExtensions()
	{
		$types = array(
			'/^com_(.+)$/e',									// com_componentname
			'/^mod_(.+)$/e',									// mod_modulename
			'/^plg_(.+)_(.+)$/e',								// plg_folder_pluginname
			'/^tpl_(.+)$/e');									// tpl_templatename
		$directories = array(
			"'components/com_\\1'",								// compontens/com_componentname
			"'modules/mod_\\1'",								// modules/mod_modulename
			"'plugins/\\1/\\2'",								// plugins/type/pluginname
			"'templates/\\1'");									// templates/templatename
		$classes = array(
			"'jUpgradeComponent'.ucfirst('\\1')",				// jUpgradeComponentComponentname
			"'jUpgradeModule'.ucfirst('\\1')",					// jUpgradeModuleModulename
			"'jUpgradePlugin'.ucfirst('\\1').ucfirst('\\2')",	// jUpgradePluginPluginname
			"'jUpgradeTemplate'.ucfirst('\\1')");				// jUpgradeTemplateTemplatename

		// Do some custom post processing on the list.
		foreach ($this->extensions as $name=>&$row)
		{
			$state = new StdClass();
			$state->xmlfile = null;
			$state->phpfile = null;
			$state->extensions = null;

			$path = preg_replace($types, $directories, $name);

			if (is_dir(JPATH_ROOT.DS."administrator/{$path}")) {
				// Find j16upgrade.xml from the extension's administrator folders
				$files = (array) JFolder::files(JPATH_ROOT."/administrator/{$path}", '^j25upgrade\.xml$', true, true);
				$state->xmlfile = array_shift( $files );
			}
			if (empty($state->xmlfile) && is_dir(JPATH_ROOT.'/'.$path)) {
				// Find j16upgrade.xml from the extension's folders
				$files = (array) JFolder::files(JPATH_ROOT.'/'.$path, '^j25upgrade\.xml$', true, true);
				$state->xmlfile = array_shift( $files );
			}

			// Check default path for extensions files
			if (empty($this->params->cli)) {
				$default_path = JPATH_ROOT."/administrator/components/com_jupgrade";
			} else {
				$default_path = JPATH_ROOT;
			}

			if (empty($state->xmlfile)) {
				// Find xml file from jUpgrade
				$default_xmlfile = "{$default_path}/extensions/{$name}.xml";

				if (file_exists($default_xmlfile)) {
					$state->xmlfile = $default_xmlfile;
				}
			}

			if (!empty($state->xmlfile)) {
				// Read xml definition file
				$xml = simplexml_load_file($state->xmlfile);

				if (!empty($xml->installer->file[0])) {
					$state->phpfile = JPATH_ROOT.DS.trim($xml->installer->file[0]);
				}
				if (!empty($xml->installer->class[0])) {
					$state->class = trim($xml->installer->class[0]);
				}
			}
			if (empty($state->phpfile)) {
				// Find adapter from jUpgrade
				$default_phpfile = "{$default_path}/extensions/{$name}.php";
				if (file_exists($default_phpfile)) {
					$state->phpfile = $default_phpfile;
				}
			}
			if (empty($state->class)) {
				// Set default class name
				$state->class = preg_replace($types, $classes, $row->element);
			}

			if (!empty($state->phpfile) || !empty($state->xmlfile)) {
				$query = "INSERT INTO jupgrade_steps (name, status, extension, state) VALUES('{$name}', 0, 1, {$this->db_new->quote(json_encode($state))} )";
				$this->db_new->setQuery($query);
				$this->db_new->query();

				// Read xml definition file
				$xml = simplexml_load_file($state->xmlfile);

				if (isset($xml->name) && isset($xml->collection)) {
					$query = "INSERT INTO #__update_sites (name, type, location, enabled) VALUES({$this->db_new->quote($xml->name)}, 'collection',  {$this->db_new->quote($xml->collection)}, 1 )";
					$this->db_new->setQuery($query);
					$this->db_new->query();
				}

				$row->params = $this->convertParams($row->params);
				if (!$this->db_new->insertObject($this->destination, $row)) {
					throw new Exception($this->db_new->getErrorMsg());
				}
				$this->count = $this->count+1;
				unset ($row);

				if (!empty($xml->package[0])) {
					// Add other extensions from the package
					foreach ($xml->package[0]->extension as $xml_ext) {
						if (isset($this->extensions[(string) $xml_ext->name])) {
							$extension = $this->extensions[(string) $xml_ext->name];
							$state->extensions[] = (string) $xml_ext->name;

							$extension->params = $this->convertParams($extension->params);
							if (!$this->db_new->insertObject($this->destination, $extension)) {
								throw new Exception($this->db_new->getErrorMsg());
							}
							unset ($this->extensions[(string) $xml_ext->name]);
						}
					}
				}

				$section = @$xml->categories[0]->section;

				if (!empty($section)) { 
	
					// Migrate the categories of contacts.
					$cat = new jUpgradeCategory();
					$cat->section = $section;
					$cat->upgrade();

				} //end if


			} //end if
		}
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
			$this->ready = $this->migrateExtensionFolders();
			if ($this->ready)
			{
				$this->ready = $this->migrateExtensionTables();
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
	 * Get extension version from the Joomla! 1.5 site
	 *
	 * @param	string Relative path to manifest file from Joomla! 1.5 JPATH_ROOT
	 * @return	string Version string
	 * @since	2.5.0
	 */
	protected function getExtensionVersion($manifest)
	{
		if (!file_exists(JPATH_ROOT.'/'.$manifest)) return null;

		$xml = simplexml_load_file(JPATH_ROOT.'/'.$manifest);
		return (string) $xml->version[0];
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

			} else if (strpos($value, '%') !== false) {

				$table = $this->db_old->getPrefix().$value;

				$query = "SHOW TABLES LIKE '{$table}'";
				$this->db_old->setQuery($query);
				$tables = $this->db_old->loadRowList();

				for ($i=0;$i<count($tables);$i++) {
					// Use default migration function
					$table = $tables[$i][0];
					$from = preg_replace ('/jos_/', '#__', $table);
					$this->copyTable($from);
					$ready = true;
				}

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
		while(($value = array_shift($this->state->folders)) !== null) {
			$this->output("{$this->name} {$value}");
			$src = JPATH_ROOT.DS.$value;
			$dest = JPATH_SITE.DS.$value;
			$copyFolderFunc = 'copyFolder_'.preg_replace('/[^\w\d]/', '_', $value);
			if (method_exists($this, $copyFolderFunc)) {
				// Use function called like copyFolder_media_kunena (for media/kunena)
				$ready = $this->$copyTableFunc($value);
				if (!$ready) {
					array_unshift($this->state->folders, $value);
				}
			} else {
				if (JFolder::exists($src) ) {
					JFolder::copy($src, $dest);
				}
			}
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
	 * @since	1.1.0
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
	 * @since	1.1.0
	 */
	protected function getCopyTables() {
		$tables = !empty($this->xml->tables->table) ? $this->xml->tables->table : array();
		$results = array();
		foreach ($tables as $table) {
			$results[] = (string) $table;
		}
		return $results;
	}

} // end class

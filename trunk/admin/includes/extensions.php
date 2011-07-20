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

	public function getInstance($step) {
		static $instances = array();

		if (!isset($instances[$step->name])) {
			if ($step->name == 'extensions') {
				return new jUpgradeExtensions($step);
			}
			$state = json_decode($step->state);

			// Try to load the adapter object
			if (file_exists(JPATH_ROOT.DS.$state->phpfile)) {
				require_once JPATH_ROOT.DS.$state->phpfile;
			}

			if (class_exists($state->class)) {
				$instances[$step->name] = new $state->class($step);
			} else {
				$instances[$step->name] = new jUpgrade($step);
			}
		}
		return $instances[$step->name];
	}

	public function upgradeExtension()
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
		if (!$this->upgradeTemplates()) {
			return false;
		}

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
			'id, name, \'component\' AS type, `option` AS element',
		 null,
		 $where,
			'id'
		);

		$rows = $this->_processExtension ( $rows );

		foreach ($rows as $row)
		{
			// Convert the array into an object.
			$row = (object) $row;

			if (!$this->db_new->insertObject($this->destination, $row)) {
				throw new Exception($this->db_new->getErrorMsg());
			}
		}

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
			'id, title as name, \'module\' AS type, `module` AS element',
		  null,
		  $where,
			'id',
		  'element'
		);

		$rows = $this->_processExtension ( $rows );

		foreach ($rows as $row)
		{
			// Convert the array into an object.
			$row = (object) $row;

			if (!$this->db_new->insertObject($this->destination, $row)) {
				throw new Exception($this->db_new->getErrorMsg());
			}
		}

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
			'id, name, \'plugin\' AS type, element',
		  null,
		  $where,
			'id',
		  'element'
		);

		$rows = $this->_processExtension ( $rows );

		foreach ($rows as $row)
		{
			// Convert the array into an object.
			$row = (object) $row;

			if (!$this->db_new->insertObject($this->destination, $row)) {
				throw new Exception($this->db_new->getErrorMsg());
			}
		}

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
		for($i=0;$i<count($folders);$i++) {

			$rows[$i] = array();
			$rows[$i]['name'] = $folders[$i];
			$rows[$i]['type'] = 'template';
			$rows[$i]['element'] = $folders[$i];
		}

		$rows = $this->_processExtension ( $rows );

		foreach ($rows as $row)
		{
			// Convert the array into an object.
			$row = (object) $row;

			if (!$this->db_new->insertObject($this->destination, $row)) {
				throw new Exception($this->db_new->getErrorMsg());
			}
		}

		return true;
	}

	/**
	 * Get the raw data for this part of the upgrade.
	 *
	 * @return	array	Returns a reference to the source data array.
	 * @since 1.1.0
	 * @throws	Exception
	 */
	protected function _processExtension( $rows )
	{

		$types = array(
			'/^com_(.+)$/e',									// com_componentname
			'/^mod_(.+)$/e',									// mod_modulename
			'/^plg_(.+)_(.+)$/e',								// plg_type_pluginname
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
		foreach ($rows as &$row)
		{
			$element = strtolower($row['element']);
			$state = new StdClass();
			$state->xmlfile = null;
			$state->phpfile = null;

			$path = preg_replace($types, $directories, $element);

			if (empty($state->xmlfile)) {
				// Find xml file from jUpgrade
				$default_xmlfile = JPATH_ROOT.DS."administrator/components/com_jupgrade/extensions/{$element}.xml";
				if (file_exists($default_xmlfile)) {
					$state->xmlfile = $default_xmlfile;
				}
			}

			if (!empty($state->xmlfile)) {
				// Read xml definition file
				$xml = simplexml_load_file($state->xmlfile);

				if (!empty($xml->installer->file[0])) {
					$state->phpfile = trim($xml->installer->file[0]);
				}
				if (!empty($xml->installer->class[0])) {
					$state->class = trim($xml->installer->class[0]);
				}
			}
			if (empty($state->phpfile)) {
				// Find adapter from jUpgrade
				$default_phpfile = JPATH_ROOT.DS."administrator/components/com_jupgrade/extensions/{$element}.php";
				if (file_exists($default_phpfile)) {
					$state->phpfile = $default_phpfile;
				}
			}
			if (empty($state->class)) {
				// Set default class name
				$state->class = preg_replace($types, $classes, $element);
			}

			if (!empty($state->phpfile) || !empty($state->xmlfile)) {
				$query = "INSERT INTO j16_jupgrade_steps (name, status, extension, state) VALUES('{$element}', 0, 1, {$this->db_new->quote(json_encode($state))} )";
				$this->db_new->setQuery($query);
				$this->db_new->query();

				// Read xml definition file
				$xml = simplexml_load_file($state->xmlfile);

				$query = "INSERT INTO j16_update_sites (name, type, location, enabled) VALUES('{$this->db_new->quote($xml->name)}', 'collection',  {$this->db_new->quote($xml->collection)}, 1 )";
				$this->db_new->setQuery($query);
				$this->db_new->query();

				$this->count = $this->count+1;
			}

			unset($row['id']);
		}

		return $rows;
	}


}

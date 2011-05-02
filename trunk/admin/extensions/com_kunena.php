<?php
/**
 * jUpgrade Kunena Component adapter
 *
 * @version		$Id: adminpraise.php
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @author		Matias Griese <matias@kunena.org>
 * @copyright	Copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 * @link		http://www.kunena.org
 */

defined ( '_JEXEC' ) or die ();

/**
 * Kunena 1.6 migration class from Joomla 1.5 to Joomla 1.6
 *
 * You can also put this class into your own extension, which makes jUpgrade to use your own copy instead of this adapter class.
 * In order to do that you should have j16upgrade.xml file somewhere in your extension path containing:
 * 	<jupgrade>
 * 		<!-- Adapter class location and name -->
 * 		<installer>
 * 			<file>administrator/components/com_kunena/install/j16upgrade.php</file>
 * 			<class>jUpgradeComponentKunena</class>
 * 		</installer>
 * 	</jupgrade>
 * For more information, see ./com_kunena.xml
 *
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @since		1.1.0
 */
class jUpgradeComponentKunena extends jUpgrade {
	/**
	 * Check if extension migration is supported.
	 *
	 * @return	boolean
	 * @since	1.1.0
	 */
	protected function detectExtension() {
		if (!file_exists(JPATH_ROOT.DS.'administrator/components/com_kunena/api.php')) {
			return false;
		}
		$this->db_old->setQuery("SELECT * FROM ".$this->db->nameQuote($this->db_old->getPrefix().'kunena_version')." ORDER BY `id` DESC", 0, 1);
		$version = $this->db_old->loadObject();
		if (!is_object($version) || substr($version->version, 0, 4) != '1.6.') {
			return false;
		}
		return true;
	}

	/**
	 * Get update site information
	 *
	 * @return	array	List of tables without prefix
	 * @since	1.1.0
	 */
	protected function getUpdateSite() {
		return parent::getUpdateSite();
		/* Replace above with your own logic if you're not using xml file:
		return array(
			'type'=>'extension',
			'priority'=>1,
			'name'=>"Kunena 1.6 Update Site",
			'url'=>'http://update.kunena.org/kunena16.xml'
		);
		*/
	}

	/**
	 * Get folders to be migrated.
	 *
	 * @return	array	List of folders relative to JPATH_ROOT
	 * @since	1.1.0
	 */
	protected function getCopyFolders() {
		// Replace this with your own logic if you're not using xml file:
		return parent::getCopyFolders();
	}

	/**
	 * Get tables to be migrated.
	 *
	 * @return	array	List of tables without prefix
	 * @since	1.1.0
	 */
	protected function getCopyTables() {
		// Replace this with your own logic if you're not using xml file:
		return parent::getCopyTables();
	}

	/**
	 * Migrate the folders.
	 *
	 * This function gets called after tables have been copied.
	 *
	 * If you want to split this task into even smaller chunks,
	 * please store your custom state variables into $this->state and return false.
	 * Returning false will force jUpgrade to call this function again,
	 * which allows you to continue import by reading $this->state before continuing.
	 *
	 * @return	boolean Ready (true/false)
	 * @since	1.1.0
	 */
	protected function migrateExtensionFolders()
	{
		return parent::migrateExtensionFolders();
		/*
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
		*/
	}

	/**
	 * Migrate custom information.
	 *
	 * This function gets called after all folders and tables have been copied.
	 *
	 * If you want to split this task into smaller chunks,
	 * please store your custom state variables into $this->state and return false.
	 * Returning false will force jUpgrade to call this function again,
	 * which allows you to continue import by reading $this->state before continuing.
	 *
	 * @return	boolean Ready (true/false)
	 * @since	1.1.0
	 * @throws	Exception
	 */
	protected function migrateExtensionCustom()
	{
		return true;
	}

	/**
	 * Copy kunena_categories table from old site to new site.
	 *
	 * You can create custom copy functions for all your tables.
	 *
	 * If you want to copy your table in many smaller chunks,
	 * please store your custom state variables into $this->state and return false.
	 * Returning false will force jUpgrade to call this function again,
	 * which allows you to continue import by reading $this->state before continuing.
	 *
	 * @return	boolean Ready (true/false)
	 * @since	1.1.0
	 * @throws	Exception
	 */
	protected function copyTable_kunena_categories($table) {
		$this->source = $this->destination = "#__{$table}";

		// Clone table
		$this->cloneTable($this->source, $this->destination);

		// Get data
		$rows = parent::getSourceData('*');

		// Set up the mapping table for the old groups to the new groups.
		$map = $this->getUsergroupIdMap();

		// Do some custom post processing on the list.
		foreach ($rows as &$row) {
			if (isset($row['accesstype']) || $row['accesstype'] == 'none' ) {
				if ($row['admin_access'] != 0) {
					$row['admin_access'] = $map[$row['admin_access']];
				}
				if ($row['pub_access'] == -1) {
					// All registered
					$row['pub_access'] = 2;
					$row['pub_recurse'] = 1;
				} elseif ($row['pub_access'] == 0) {
					// Everybody
					$row['pub_access'] = 1;
					$row['pub_recurse'] = 1;
				} elseif ($row['pub_access'] == 1) {
					// Nobody
					$row['pub_access'] = 8;
				} else {
					// User groups
					$row['pub_access'] = $map[$row['pub_access']];
				}
			}
		}
		$this->setDestinationData($rows);
		return true;
	}
}
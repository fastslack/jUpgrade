<?php
/**
 * jUpgrade
 *
 * @version		$Id: 
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @copyright	Copyright 2006 - 2011 Matias Aguirre. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @author		Matias Aguirre <maguirre@matware.com.ar>
 * @link		http://www.matware.com.ar
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * jUpgrade class for Virtuemart migration
 *
 * This class migrates the Adminpraise extension
 *
 * @since		1.2.0
 */
class jUpgradeComponentVirtuemart extends jUpgradeExtensions
{
	/**
	 * Check if extension migration is supported.
	 *
	 * @return	boolean
	 * @since	1.2.0
	 */
	protected function detectExtension()
	{
		return true;
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
	protected function migrateExtensionCustom() {
		$app = JFactory::getApplication('administrator');

		// Get component object
		$component = JTable::getInstance ( 'extension', 'JTable', array('dbo'=>$this->db_new) );
		$component->load(array('type'=>'component', 'element'=>$this->name));

		// Mark Virtuemart as discovered and install it
		$component->client_id = 1;
		$component->state = -1;
		$component->store();
		jimport('joomla.installer.installer');
		$installer = JInstaller::getInstance();
		$installer->discover_install($component->extension_id);

		$query = "INSERT INTO #__update_sites_extensions
			SELECT update_site_id, '{$component->extension_id}' FROM #__update_sites WHERE name='Virtuemart'
		";
		$this->db_new->setQuery($query);
		$this->db_new->query();

		// Update Virtuemart 
		//~ jimport('joomla.updater.update');
		//~ $updater = JUpdater::getInstance();
		//~ $updater->findUpdates($component->extension_id);
		//~ $update = JTable::getInstance ( 'update', 'JTable', array('dbo'=>$this->db_new) );
		//~ $update
			//~ ->load(
			//~ array(
				//~ 'element' => 'com_virtuemart', 'type' => 'component',
				//~ 'client_id' => '1',
				//~ 'folder' => ''
			//~ )
		//~ );
		//~ if ($update->uid) {
			//~ $updater->update($update->uid);
			//~ echo "Virtuemart successfully upgraded to version 1.2";
		//~ }
		return true;
	}
}

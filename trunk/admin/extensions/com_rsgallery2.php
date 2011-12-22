<?php
/**
 * jUpgrade RSGallery2 Component adapter
 *
 * @version		$Id$
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @author		RSGallery2 Team
 * @copyright	Copyright (C) 2011 RSGallery2 Team. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */
defined ( '_JEXEC' ) or die ();

/**
 * RSGallery2 migration class from Joomla 1.5 to Joomla 1.6
 *
 * @since		1.1.1
 */
class jUpgradeComponentRsgallery2 extends jUpgradeExtensions {
	/**
	 * Check if extension migration is supported.
	 *
	 * @return	boolean
	 * @since	1.1.1
	 */
	protected function detectExtension() {
		//Take one RSGallery2 file to see if the components files are present
		if (!file_exists(JPATH_ROOT.DS.'administrator/components/com_rsgallery2/init.rsgallery2.php')) {
			return false;
		}
		//Let's not try to take the version into account right now... but a check could be done here
		return true;
	}

	/**
	 * Get folders to be migrated.
	 *
	 * @return	array	List of folders relative to JPATH_ROOT
	 * @since	1.1.1
	 */
	protected function getCopyFolders() {
		//Using xml file
		return parent::getCopyFolders();
	}

	/**
	 * Get tables to be migrated.
	 *
	 * @return	array	List of tables without prefix
	 * @since	1.1.1
	 */
	protected function getCopyTables() {
		//Using xml file
		return parent::getCopyTables();
	}

	/**
	 * Migrate the folders.
	 *
	 * @return	boolean Ready (true/false)
	 * @since	1.1.1
	 */
	protected function migrateExtensionFolders()
	{
		return parent::migrateExtensionFolders();
	}
}

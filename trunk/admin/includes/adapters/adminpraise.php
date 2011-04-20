<?php
/**
 * jUpgrade
 *
 * @version		$Id: adminpraise.php 
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @copyright	Copyright 2006 - 2011 Matias Aguire. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @author		Matias Aguirre <maguirre@matware.com.ar>
 * @link		http://www.matware.com.ar
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * jUpgrade class for Adminpraise migration
 *
 * This class migrates the Adminpraise extension
 *
 * @since		1.1.0
 */
class jUpgradeExtensionsAdminpraise extends jUpgrade
{
	/**
	 * Migrate tables
	 *
	 * @return	boolean
	 * @since	1.1.0
	 */
	public function migrateTables()
	{

		// Copy tables
		$this->copyTable('#__adminpraise_menu', 'j16_adminpraise_menu');
		$this->copyTable('#__adminpraise_menu_types', 'j16_adminpraise_menu_types');

		// name -> title
		$query = "ALTER TABLE `j16_adminpraise_menu` CHANGE `name` `title` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";
		$this->db_new->setQuery($query);
		$this->db_new->query();

		// Check for query error.
		$error = $this->db_new->getErrorMsg();

		if ($error) {
			throw new Exception($error);
		}

		// parent -> parent_id
		$query = "ALTER TABLE `j16_adminpraise_menu` CHANGE `parent` `parent_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0'";
		$this->db_new->setQuery($query);
		$this->db_new->query();

		// Check for query error.
		$error = $this->db_new->getErrorMsg();

		if ($error) {
			throw new Exception($error);
		}

		return true;
	}

	/**
	 * The public entry point for the class.
	 *
	 * @return	boolean
	 * @since	1.1.0
	 */
	public function upgrade()
	{
		try
		{
			$this->migrateTables();
		}
		catch (Exception $e)
		{
			echo JError::raiseError(500, $e->getMessage());

			return false;
		}

		return true;
	}
}

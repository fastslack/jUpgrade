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
	 * Migrate tables
	 *
	 * @return	boolean
	 * @since	1.2.0
	 */
	public function migrateExtensionCustom()
	{

/*
		// name -> title
		$query = "ALTER TABLE `#__adminpraise_menu` CHANGE `name` `title` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";
		$this->db_new->setQuery($query);
		//$this->db_new->query();

		// Check for query error.
		$error = $this->db_new->getErrorMsg();

		if ($error) {
			throw new Exception($error);
		}

		// parent -> parent_id
		$query = "ALTER TABLE `#__adminpraise_menu` CHANGE `parent` `parent_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0'";
		$this->db_new->setQuery($query);
		//$this->db_new->query();

		// Check for query error.
		$error = $this->db_new->getErrorMsg();

		if ($error) {
			throw new Exception($error);
		}
*/
		return true;
	}
}

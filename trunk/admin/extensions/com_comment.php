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
class jUpgradeComponentComment extends jUpgradeExtensions
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
		return true;
	}
}

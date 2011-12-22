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

defined ( '_JEXEC' ) or die ();

/**
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @since		1.1.0
 */
class jUpgradeComponentRedshop extends jUpgradeExtensions {
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
	 * Migrate tables
	 *
	 * @return	boolean
	 * @since	1.1.0
	 */
	public function migrateExtensionCustom()
	{
		return true;
	}
}

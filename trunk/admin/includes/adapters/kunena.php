<?php
/**
 * jUpgrade
 *
 * @version		$Id: kunena.php 
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
 * jUpgrade class for Kunena migration
 *
 * This class migrates the Kunena extension
 *
 * @since		1.1.0
 */
class jUpgradeExtensionsKunena extends jUpgrade
{

	/**
	 * @var		string	Extension xml url
	 * @since	1.1.0
	 */
	protected $url = 'http://www.matware.com.ar/extensions/kunena/kunena.xml';

	/**
	 * The public entry point for the class.
	 *
	 * @return	boolean
	 * @since	1.1.0
	 */
	public function migrateExtensionDataHook()
	{

		return true;
	}
}

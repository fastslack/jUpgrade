<?php
/**
 * jUpgrade
 *
 * @version		$Id: 
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @copyright	Copyright 2006 - 2012 Matias Aguirre. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @author		Matias Aguirre <maguirre@matware.com.ar>
 * @link		http://www.matware.com.ar
 */

defined('JPATH_BASE') or die();

class jUpgradeComponentAcesef extends jUpgradeExtensions
{
	protected function detectExtension()
	{
		return true;
	}

	public function migrateExtensionCustom()
	{
		return true;
	}
}


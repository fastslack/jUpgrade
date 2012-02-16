<?php
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

class jUpgradeComponentAcymailing extends jUpgradeExtensions
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


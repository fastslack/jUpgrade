<?php
/**
 * jUpgrade
 *
 * @version		$Id: 
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @copyright	Copyright 2012 Schultschik Websolution, Sven Schultschik. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @author		Sven Schultschik <sven@schultschik.de>
 * @link		http://www.schultschik.de
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * jUpgrade class for Akeeba migration
 *
 * This class migrates the Akeeba extension
 *
 * @since		1.2.4
 */
class jUpgradeComponentArs extends jUpgradeExtensions
{
	/**
	 * Check if extension migration is supported.
	 *
	 * @return	boolean
	 * @since	1.2.4
	 */
	protected function detectExtension()
	{
		return true;
	}

	/**
	 * Migrate tables
	 *
	 * @return	boolean
	 * @since	1.2.4
	 */
	public function migrateExtensionCustom()
	{
		return true;
	}


	/**
	 * Copy ars_categories table from old site to new site.
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
	protected function copyTable_ars_categories($table) {
		$this->source = $this->destination = "#__{$table}";

		// Clone table
		$this->cloneTable($this->source, $this->destination);

		// Get data
		$rows = parent::getSourceData('*');

		// Do some custom post processing on the list.
		foreach ($rows as &$row) {
			$row['access']++;
		}
		$this->setDestinationData($rows);
		return true;
	}

	/**
	 * Copy ars_items table from old site to new site.
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
	protected function copyTable_ars_items($table) {
		$this->source = $this->destination = "#__{$table}";

		// Clone table
		$this->cloneTable($this->source, $this->destination);

		// Get data
		$rows = parent::getSourceData('*');

		// Do some custom post processing on the list.
		foreach ($rows as &$row) {
			$row['access']++;
		}
		$this->setDestinationData($rows);
		return true;
	}

	/**
		 * Copy ars_releases table from old site to new site.
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
	protected function copyTable_ars_releases($table) {
		$this->source = $this->destination = "#__{$table}";

		// Clone table
		$this->cloneTable($this->source, $this->destination);

		// Get data
		$rows = parent::getSourceData('*');

		// Do some custom post processing on the list.
		foreach ($rows as &$row) {
			$row['access']++;
		}
		$this->setDestinationData($rows);
		return true;
	}
}
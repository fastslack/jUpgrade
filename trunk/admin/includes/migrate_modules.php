<?php
/**
 * jUpgrade
 *
 * @version		  $Id$
 * @package		  MatWare
 * @subpackage	com_jupgrade
 * @author      Matias Aguirre <maguirre@matware.com.ar>
 * @link        http://www.matware.com.ar
 * @copyright		Copyright 2006 - 2011 Matias Aguire. All rights reserved.
 * @license		  GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Upgrade class for modules
 *
 * This class takes the modules from the existing site and inserts them into the new site.
 *
 * @since	0.4.5
 */
class jUpgradeModules extends jUpgrade
{
	/**
	 * @var		string	The name of the source database table.
	 * @since	0.4.5
	 */
	protected $source = '#__modules';

	/**
	 * Get the mapping of the old positions to the new positions in the template.
	 *
	 * @return	array	An array with keys of the old names and values being the new names.
	 * @since	0.5.7
	 */
	public static function getPositionsMap()
	{
		$map = array(
			// Old	=> // New
			'search'				=> 'position-0',
			'top'						=> 'position-1',
			'breadcrumbs'		=> 'position-2',
			'left'					=> 'position-6',
			'right'					=> 'position-7',
			'search'				=> 'position-8',
			'footer'				=> 'position-9',
			'header'				=> 'position-15'
		);

		return $map;
	}

	/**
	 * A hook to be able to modify params prior as they are converted to JSON.
	 *
	 * @param	object	$object	A reference to the parameters as an object.
	 *
	 * @return	void
	 * @since	1.0.3
	 * @throws	Exception
	 */
	protected function convertParamsHook(&$object)
	{
		if (isset($object->startLevel)) $object->startLevel++;
		if (!empty($object->endLevel)) $object->endLevel++;
	}

	/**
	 * Get the raw data for this part of the upgrade.
	 *
	 * @return	array	Returns a reference to the source data array.
	 * @since	0.4.5
	 * @throws	Exception
	 */
	protected function &getSourceData()
	{

		$select = "`id` AS `sid`, `title`, `content`, `ordering`, `position`,"
						." `checked_out`, `checked_out_time`, `published`, `module`,"
						." `access`, `showtitle`, `params`, `client_id`";

		$where = array();
		$where[] = "client_id != 1";
		$where[] = "module IN ('mod_breadcrumbs', 'mod_footer', 'mod_mainmenu', 'mod_related_items', 'mod_stats', 'mod_wrapper', 'mod_archive', 'mod_custom', 'mod_latestnews', 'mod_mostread', 'mod_search', 'mod_syndicate', 'mod_banners', 'mod_feed', 'mod_login', 'mod_newsflash', 'mod_random_image', 'mod_whosonline' )";

		$rows = parent::getSourceData(
			$select,
		  null,
			$where,
			'id'
		);

		// Set up the mapping table for the old positions to the new positions.
		$map = self::getPositionsMap();
		$map_keys = array_keys($map);

		// Getting the component parameter with global settings
		$params = $this->getParams();

		// Do some custom post processing on the list.
		foreach ($rows as &$row)
		{
			$row['params'] = $this->convertParams($row['params']);

			## Fix access
			$row['access'] = $row['access']+1;

			## Language
			$row['language'] = "*";

			## Module field changes
			if ($row['module'] == "mod_mainmenu") {
				$row['module'] = "mod_menu";
			}
			else if ($row['module'] == "mod_archive") {
				$row['module'] = "mod_articles_archive";
			}
			else if ($row['module'] == "mod_latestnews") {
				$row['module'] = "mod_articles_latest";
			}
			else if ($row['module'] == "mod_mostread") {
				$row['module'] = "mod_articles_popular";
			}
			else if ($row['module'] == "mod_newsflash") {
				$row['module'] = "mod_articles_news";
			}

			## Change positions
			if ($params->positions == 0) {
				if (in_array($row['position'], $map_keys)) {
						$row['position'] = $map[$row['position']];
				}
			}	

		}

		return $rows;
	}

	/**
	 * Sets the data in the destination database.
	 *
	 * @return	void
	 * @since	0.4.
	 * @throws	Exception
	 */
	protected function setDestinationData()
	{
		// Get the source data.
		$rows	= $this->getSourceData();
		$table	= empty($this->destination) ? $this->source : $this->destination;

		// 
		foreach ($rows as $row)
		{
			// Convert the array into an object.
			$row = (object) $row;

			// Get old id 
			$oldlist = new stdClass();
			$oldlist->old = $row->sid;
			unset($row->sid);

			if (!$this->db_new->insertObject($table, $row)) {
				throw new Exception($this->db_new->getErrorMsg());
			}

			// Get new id 
			$oldlist->new = $this->db_new->insertid();

			// Save old and new id
			if (!$this->db_new->insertObject('jupgrade_modules', $oldlist)) {
				throw new Exception($this->db_new->getErrorMsg());
			}

		}
	}

}

/**
 * Upgrade class for modules menu
 *
 * This class takes the modules from the existing site and inserts them into the new site.
 *
 * @since	0.4.5
 */
class jUpgradeModulesMenu extends jUpgrade
{
	/**
	 * @var		string	The name of the source database table.
	 * @since	0.4.5
	 */
	protected $source = '#__modules_menu AS m';

	/**
	 * @var		string	The name of the destination database table.
	 * @since	0.4.5
	 */
	protected $destination = '#__modules_menu';

	/**
	 * Get the raw data for this part of the upgrade.
	 *
	 * @return	array	Returns a reference to the source data array.
	 * @since	0.4.5
	 * @throws	Exception
	 */
	protected function &getSourceData()
	{
		// Getting the map id's for modules and menus
		$modules = $this->getMapList('modules');
		$menus = $this->getMapList('menus');

		// Getting the menus keys to prevent 'Notice: Undefined index'
		$menus_keys = array_keys($menus);

		$where = "m.moduleid NOT IN (1, 16, 17, 18)";

		// Getting the data
		$rows = parent::getSourceData(
			'*',
		  null,
			$where,
			'm.moduleid'
		);

		// Do some custom post processing on the list.
		foreach ($rows as &$row)
		{
			if (in_array($row['menuid'], $menus_keys)) {
				$moduleid_new = isset($modules[$row['moduleid']]->new) ? $modules[$row['moduleid']]->new : '';
				$menuid_new = $menus[$row['menuid']]->new;

				//echo "<b> MODULE ID:</b> {$moduleid_new} <==> <b> MENU ID:</b> {$menuid_new}";
				//echo "<br><br>.................<br><br>";

				$row['moduleid'] = $moduleid_new == '' ? 0 : $moduleid_new;
			  $row['menuid'] = $menuid_new == '' ? 0 : $menuid_new;
			}
		}


		return $rows;
	}
}

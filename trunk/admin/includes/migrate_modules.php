<?php
/**
 * jUpgrade
 *
 * @version		$Id$
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @copyright	Copyright 2006 - 2011 Matias Aguire. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @author		Matias Aguirre <maguirre@matware.com.ar>
 * @link		http://www.matware.com.ar
 */

define('_JEXEC', 1);
define('JPATH_BASE', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);
require_once JPATH_BASE.'/defines.php';
require_once JPATH_BASE.'/jupgrade.class.php';

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
	 * @var		string	The name of the destination database table.
	 * @since	0.4.5
	 */
	protected $destination = '#__modules';

	/**
	 * Get the raw data for this part of the upgrade.
	 *
	 * @return	array	Returns a reference to the source data array.
	 * @since	0.4.5
	 * @throws	Exception
	 */
	protected function &getSourceData()
	{

		$query = "`title`,NULL AS `note`, `ordering`,`position`,"
						." `checked_out`,`checked_out_time`,`published`,`module`,"
						." `access`,`showtitle`,`params`,`client_id`,NULL AS `language`";

		$where = "iscore = 0 AND id > 19";

		$rows = parent::getSourceData(
			$query,
		  null,
			$where,
			'id'
		);

		// Do some custom post processing on the list.
		foreach ($rows as &$row)
		{
			$row['params'] = $this->convertParams($row['params']);

			## Fix access
			$row['access'] = $row['access']+1;

			## Language
			$row['language'] = "*";

			## Module
			if ($row['module'] == "mod_mainmenu") {
				$row['module'] = "mod_menu";
			}

		}

		return $rows;
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
	protected $source = '#__modules_menu';

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

		$rows = parent::getSourceData(
			'*',
		  null,
			null,
			'moduleid'
		);

		return $rows;
	}
}



// Migrate the Modules.
$modules = new jUpgradeModules;
$modules->upgrade();

// Migrate the Modules Menus.
$modulesmenu = new jUpgradeModulesMenu;
$modulesmenu->upgrade();

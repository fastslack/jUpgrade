<?php
/**
 * jUpgrade
 *
 * @version		$Id$
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @author      Matias Aguirre <maguirre@matware.com.ar>
 * @link        http://www.matware.com.ar
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

define('_JEXEC',		1);
//define('JPATH_BASE',	dirname(dirname(dirname(dirname(dirname(__FILE__))))));
define('JPATH_BASE',	dirname(__FILE__));
define('DS',			DIRECTORY_SEPARATOR);

require_once JPATH_BASE.'/defines.php';
require_once JPATH_BASE.'/jupgrade.class.php';

/**
 * Upgrade class for Menus
 *
 * This class takes the menus from the existing site and inserts them into the new site.
 *
 * @since	0.4.5
 */
class jUpgradeMenu extends jUpgrade
{
	/**
	 * @var		string	The name of the source database table.
	 * @since	0.4.5
	 */
	protected $source = '#__menu';

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
			 '`menutype`,`name` AS title,`alias`,`link`,`type`,'
			.' `published`,`parent` AS parent_id, `componentid` AS component_id,'
			.' `sublevel` AS level,`ordering`,`checked_out`,`checked_out_time`,`browserNav`,'
			.' `access`,`params`,`lft`,`rgt`,`home`',
			null,
			'id'
		);

		// Do some custom post processing on the list.
		foreach ($rows as &$row)
		{
			$row['params'] = $this->convertParams($row['params']);

			// Remove unused fields.
			unset($row['gid']);
		}

		return $rows;
	}
}

/**
 * Upgrade class for MenusTypes
 *
 * This class takes the menus from the existing site and inserts them into the new site.
 *
 * @since	0.4.5
 */
class jUpgradeMenuTypes extends jUpgrade
{
	/**
	 * @var		string	The name of the source database table.
	 * @since	0.4.5
	 */
	protected $source = '#__menu_types';

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
			$this->db_old->nameQuote('id').' > 1',
			'id'
		);

		return $rows;
	}
}


// Migrate the menu.
$menu = new jUpgradeMenu;
$menu->upgrade();

// Migrate the menu types.
$menutypes = new jUpgradeMenuTypes;
$menutypes->upgrade();

?>

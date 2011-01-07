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

require_once JPATH_BASE.DS.'defines.php';
require_once JPATH_BASE.DS.'jupgrade.class.php';

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
	protected $source = '#__menu AS m';

	/**
	 * @var		string	The name of the destination database table.
	 * @since	0.4.8
	 */
	protected $destination = '#__menu';

	/**
	 * Get the raw data for this part of the upgrade.
	 *
	 * @return	array	Returns a reference to the source data array.
	 * @since	0.4.5
	 * @throws	Exception
	 */
	protected function &getSourceData()
	{
		$join = array();
		$join[] = 'LEFT JOIN #__components AS c ON c.id = m.componentid';
		$join[] = 'LEFT JOIN j16_extensions AS e ON e.name = c.option';

		$where = "m.name != 'Home' AND m.alias != 'home'";

		$rows = parent::getSourceData(
			 ' m.menutype, m.name AS title, m.alias, m.link, m.type,'
			.' m.published, m.parent AS parent_id, e.extension_id AS component_id,'
			.' m.sublevel AS level, m.ordering, m.checked_out, m.checked_out_time, m.browserNav,'
			.' m.access, m.params, m.lft, m.rgt, m.home',
			$join,
			$where,
			'm.id'
		);

		$query = "SELECT alias FROM j16_menu";
		$this->db_new->setQuery($query);
		$aliases = $this->db_new->loadResultArray();
		echo $this->db_new->getError();	

		// Do some custom post processing on the list.
		foreach ($rows as &$row)
		{
			// Converting params to JSON
			$row['params'] = $this->convertParams($row['params']);
			// Fixing parent id
			$row['parent_id'] = $row['parent_id'] == 0 ? $row['parent_id']+1 : $row['parent_id'];
			// Fixing access
			$row['access'] = $row['access'] == 0 ? 1 : $row['access']+1;
			// Fixing level
			$row['level'] = $row['level'] == 0 ? 1 : $row['level']+1;
			// Fixing language
			$row['language'] = '*';

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
			null,
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

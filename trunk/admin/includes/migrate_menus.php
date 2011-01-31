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

		 // Getting the categories id's
		$query = "SELECT *"
		." FROM j16_jupgrade_categories";
		$this->db_new->setQuery( $query );
		$categories = $this->db_new->loadObjectList('old');

		// Creating the query
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

		// Getting number of rows
		$count = count($rows);
	
		// Do some custom post processing on the list.
		foreach ($rows as $key => &$row)
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

			// Fixing menus URL's
			if ($row['link'] == 'index.php?option=com_content&view=frontpage') {
				$row['link'] = 'index.php?option=com_content&view=featured';
			}else if (strlen(strstr($row['link'], 'index.php?option=com_content&view=section&layout=blog')) ) {
				$ex = explode('&', $row['link']);
				$id = substr($ex[3], 3);
				$row['link'] = 'index.php?option=com_content&view=category&layout=blog&id='.$categories[$id]->new;
			}

			// Joomla 1.6 database structure not allow to have duplicated aliases
			$newrows = $rows;

			for ($i=$key;$i<$count;$i++) {
				unset($newrows[$i]);
			}

			$strip = array();
			$strip[$key] = $row;

			$newrows = array_diff_key($newrows, $strip);
			
			foreach ($newrows as $key => &$newrow) {
				if ($newrow['alias'] != $row['alias']) {
					$row['alias'] = JFilterOutput::stringURLSafe($row['title']);
				}else{
					$row['alias'] = JFilterOutput::stringURLSafe($row['title'])."-".rand();
					break;
				}
			}

			// Correct path
			//$row['path'] = JFilterOutput::stringURLSafe($parent)."/".$alias;

		}

		return $rows;
	}

	/**
	 * A hook to be able to modify params prior as they are converted to JSON.
	 *
	 * @param	object	$object	A reference to the parameters as an object.
	 *
	 * @return	void
	 * @since	0.4.
	 * @throws	Exception
	 */
	protected function convertParamsHook(&$object)
	{
		if (isset($object->menu_image)) {
			if((string)$object->menu_image == '-1'){
				$object->menu_image = '';
			}
		}
	}

	/**
	 * The public entry point for the class.
	 *
	 * @return	void
	 * @since	0.5.2
	 * @throws	Exception
	 */
	public function upgrade()
	{
		if (parent::upgrade()) {
			// Rebuild the usergroup nested set values.
			$table = JTable::getInstance('Menu', 'JTable', array('dbo' => $this->db_new));

			if (!$table->rebuild()) {
				echo JError::raiseError(500, $table->getError());
			}
		}
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

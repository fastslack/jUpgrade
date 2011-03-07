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

define('_JEXEC',		1);
define('JPATH_BASE',	dirname(__FILE__));
define('DS',			DIRECTORY_SEPARATOR);

require_once JPATH_BASE.'/defines.php';
require_once JPATH_BASE.'/jupgrade.class.php';

/**
 * Upgrade class for 3rd party extensions
 *
 * This class search for extensions to be migrated
 *
 * @since	0.4.5
 */
class jUpgradeExtensions extends jUpgrade
{
	/**
	 * @var		string	The name of the source database table.
	 * @since	0.4.5
	 */
	public $source = '#__components AS c';

	/**
	 * @var		string	The name of the destination database table.
	 * @since	0.4.5
	 */
	public $destination = '#__extensions';


	/**
	 * Get the raw data for this part of the upgrade.
	 *
	 * @return	array	Returns a reference to the source data array.
	 * @since	0.4.5
	 * @throws	Exception
	 */
	protected function &getSourceData()
	{
		$where = array();
		$where[] = "id > 33";
		$where[] = "c.option != 'com_jupgrade'";

		$rows = parent::getSourceData(
			'id, name, \'component\' AS type, `option` AS element',
		 null,
		 $where,
			'id'
		);

		// Do some custom post processing on the list.
		foreach ($rows as &$row)
		{
			unset($row['id']);
		}

		return $rows;
	}

}

// Search for 3rd party extensions
$extensions = new jUpgradeExtensions;
$extensions->upgrade();


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
	public $source = '#__components';

	/**
	 * @var		string	The name of the destination database table.
	 * @since	0.4.5
	 */
	public $destination = '#__extensions';


	/**
	 * Search for 3rd party extensions
	 *
	 * @return	bool	True if everything is ok
	 * @since	0.4.5
	 * @throws	Exception
	 */
	public function search()
	{

		$updater = JUpdater::getInstance();
//print_r($updater);

/*
		$rows = parent::getSourceData(
			'`bid` AS id,`cid`,`type`,`name`,`alias`, `imptotal` ,`impmade`, `clicks`, '
		 .'`clickurl`, `checked_out`, `checked_out_time`, `showBanner` AS state,'
		 .' `custombannercode`, `description`, `sticky`, `ordering`, `publish_up`, '
		 .' `publish_down`, `params`',
			null,
			'bid'
		);

		// Do some custom post processing on the list.
		foreach ($rows as &$row)
		{
			$row['params'] = $this->convertParams($row['params']);

			// Remove unused fields.
			unset($row['gid']);
		}
*/
		//return $rows;
	}

}

// Search for 3rd party extensions
$extensions = new jUpgradeExtensions;

//TODO: Make search method

$search = $extensions->search();

/*
if ($search) {
	echo "DO SOMETHING";
}
*/

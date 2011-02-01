<?php
/**
 * jUpgrade
 *
 * @version		  $Id$
 * @package		  MatWare
 * @subpackage	com_jupgrade
 * @author      Matias Aguirre <maguirre@matware.com.ar>
 * @link        http://www.matware.com.ar
 * @license		  GNU General Public License version 2 or later; see LICENSE.txt
 */
define('_JEXEC',		1);
//define('JPATH_BASE',	dirname(dirname(dirname(dirname(dirname(__FILE__))))));
define('JPATH_BASE',	dirname(__FILE__));
define('DS',			DIRECTORY_SEPARATOR);

require_once JPATH_BASE.DS.'defines.php';
require_once JPATH_BASE.DS.'jupgrade.class.php';

/**
 * Upgrade class for Weblinks
 *
 * This class takes the weblinks from the existing site and inserts them into the new site.
 *
 * @since	0.4.5
 */
class jUpgradeWeblinks extends jUpgrade
{
	/**
	 * @var		string	The name of the source database table.
	 * @since	0.4.5
	 */
	protected $source = '#__weblinks';

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
			'`id`, `catid`, `sid`, `title`, `alias`, `url`, `description`, `date`, `hits`, '
     .' `published` AS state, `checked_out`, `checked_out_time`, `ordering`, `archived`, `approved`,`params`',
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
 * Upgrade class for Weblinks categories
 *
 * This class takes the categories banners from the existing site and inserts them into the new site.
 *
 * @since	0.5.6
 */
class jUpgradeWeblinksCategories extends jUpgrade
{
	/**
	 * @var		string	The name of the source database table.
	 * @since	0.5.6
	 */
	protected $source = '#__categories';

	/**
	 * Get the raw data for this part of the upgrade.
	 *
	 * @return	array	Returns a reference to the source data array.
	 * @since	0.5.6
	 * @throws	Exception
	 */
	protected function &getSourceData()
	{

		$where = "section = 'com_weblinks'";

		$rows = parent::getSourceData(
			'`id` AS sid, `title`, `alias`, `section`, `description`, `published`, `checked_out`, `checked_out_time`, `access`, `params`',
		  null,
			$where,
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


	/**
	 * Sets the data in the destination database.
	 *
	 * @return	void
	 * @since	0.5.6
	 * @throws	Exception
	 */
	protected function setDestinationData()
	{
		// Get the source data.
		$rows	= $this->getSourceData();

		//
		// Insert the categories
		//
		foreach ($rows as $row)
		{
			// Convert the array into an object.
			$row = (object) $row;

			// Insert category
			if (!$this->insertCategory($row)) {
				throw new Exception('JUPGRADE_ERROR_INSERTING_CATEGORY');
			}
	
			// Insert asset
			if (!$this->insertAsset($row)) {
				throw new Exception('JUPGRADE_ERROR_INSERTING_ASSET');
			}

		}
	}

	/**
	 * The public entry point for the class.
	 *
	 * @return	void
	 * @since	0.5.6
	 * @throws	Exception
	 */
	public function upgrade()
	{
		if (parent::upgrade()) {
			// Rebuild the categories table
			$table = JTable::getInstance('Category', 'JTable', array('dbo' => $this->db_new));

			if (!$table->rebuild()) {
				echo JError::raiseError(500, $table->getError());
			}

			// Rebuild the assets table
			$assets = JTable::getInstance('Asset', 'JTable', array('dbo' => $this->db_new));

			if (!$assets->rebuild()) {
				echo JError::raiseError(500, $assets->getError());
			}

		}
	}

}


// Migrate the weblinks.
$weblinks = new jUpgradeWeblinks;
$weblinks->upgrade();

// Migrate the categories of weblinks.
$weblinksCat = new jUpgradeWeblinksCategories;
$weblinksCat->upgrade();

?>

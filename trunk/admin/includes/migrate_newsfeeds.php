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

/**
 * Upgrade class for Newsfeeds
 *
 * This class takes the newsfeeds from the existing site and inserts them into the new site.
 *
 * @since	0.4.5
 */
class jUpgradeNewsfeeds extends jUpgrade
{
	/**
	 * @var		string	The name of the source database table.
	 * @since	0.4.5
	 */
	protected $source = '#__newsfeeds';

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
			'`catid`,`id`,`name`,`alias`,`link`,`filename`,`published`,`numarticles`,`cache_time`, '
     .'`checked_out`,`checked_out_time`,`ordering`,`rtl`',
			null,
			'id'
		);

		// Getting the categories id's
		$categories = $this->getMapList('categories', 'com_newsfeeds');

		// Do some custom post processing on the list.
		foreach ($rows as &$row)
		{
			$row['access'] = empty($row['access']) ? 1 : $row['access'] + 1;
			$row['language'] = '*';

			$cid = $row['catid'];
			$row['catid'] = &$categories[$cid]->new;
		}

		return $rows;
	}
}

/**
 * Upgrade class for Newsfeeds categories
 *
 * This class takes the categories banners from the existing site and inserts them into the new site.
 *
 * @since	0.5.6
 */
class jUpgradeNewsfeedsCategories extends jUpgrade
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

		$where = "section = 'com_newsfeeds'";

		$rows = parent::getSourceData(
			'`id` AS sid, `title`, `alias`, `section` AS extension, `description`, `published`, `checked_out`, `checked_out_time`, `access`, `params`',
		  null,
			$where,
			'id'
		);

		// Do some custom post processing on the list.
		foreach ($rows as &$row)
		{
			$row['params'] = $this->convertParams($row['params']);
			$row['access'] = $row['access'] == 0 ? 1 : $row['access'] + 1;
			$row['language'] = '*';

			// Correct alias
			if ($row['alias'] == "") {
				$row['alias'] = JFilterOutput::stringURLSafe($row['title']);
			}
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
		}
	}

}


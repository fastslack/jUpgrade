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
 * Upgrade class for Banners
 *
 * This class takes the banners from the existing site and inserts them into the new site.
 *
 * @since	0.4.5
 */
class jUpgradeBanners extends jUpgrade
{
	/**
	 * @var		string	The name of the source database table.
	 * @since	0.4.5
	 */
	protected $source = '#__banner';

	/**
	 * @var		string	The name of the destination database table.
	 * @since	0.4.5
	 */
	protected $destination = '#__banners';


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
			'`bid` AS id, `cid`, `type`,`name`,`alias`, `imptotal` ,`impmade`, `clicks`, '
		 .'`catid`, `clickurl`, `checked_out`, `checked_out_time`, `showBanner` AS state, '
		 .'`custombannercode`, `description`, `sticky`, `ordering`, `publish_up`, '
		 .'`publish_down`, `params`',
			null,
			'bid'
		);

		// Getting the categories id's
		$categories = $this->getMapList('categories', 'com_banner');

		// Do some custom post processing on the list.
		foreach ($rows as &$row)
		{
			$row['name'] = str_replace("'", "&#39;", $row['name']);
			$row['params'] = $this->convertParams($row['params']);
			$row['description'] = str_replace("'", "&#39;", $row['description']);

			$cid = $row['catid'];
			$row['catid'] = &$categories[$cid]->new;

			$row['language'] = '*';
		}

		return $rows;
	}
}

/**
 * Upgrade class for Banners categories
 *
 * This class takes the categories banners from the existing site and inserts them into the new site.
 *
 * @since	0.5.6
 */
class jUpgradeBannersCategories extends jUpgrade
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

		$where = "section = 'com_banner'";

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
			$row['access'] = $row['access']+1;
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

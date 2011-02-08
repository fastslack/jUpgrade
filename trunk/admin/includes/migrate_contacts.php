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
 * Upgrade class for Contacts
 *
 * This class takes the contacts from the existing site and inserts them into the new site.
 *
 * @since	0.4.5
 */
class jUpgradeContacts extends jUpgrade
{
	/**
	 * @var		string	The name of the source database table.
	 * @since	0.4.5
	 */
	protected $source = '#__contact_details';

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
			'id'
		);

		// Do some custom post processing on the list.
		foreach ($rows as &$row)
		{
			$row['params'] = $this->convertParams($row['params']);
		}

		return $rows;
	}
}

/**
 * Upgrade class for Contacts categories
 *
 * This class takes the categories banners from the existing site and inserts them into the new site.
 *
 * @since	0.5.6
 */
class jUpgradeContactsCategories extends jUpgrade
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

		$where = "section = 'com_contact_details'";

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

// Migrate the contacts.
$contacts = new jUpgradeContacts;
$contacts->upgrade();

// Migrate the categories of contacts.
$contactsCat = new jUpgradeContactsCategories;
$contactsCat->upgrade();

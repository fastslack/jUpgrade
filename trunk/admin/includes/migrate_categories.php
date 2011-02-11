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
 * Upgrade class for categories
 *
 * This class takes the categories from the existing site and inserts them into the new site.
 *
 * @since	0.4.5
 */
class jUpgradeCategories extends jUpgrade
{
	/**
	 * @var		string	The name of the source database table.
	 * @since	0.4.5
	 */
	protected $source = '#__sections';

	/**
	 * @var		string	The name of the destination database table.
	 * @since	0.4.5
	 */
	protected $destination = '#__categories';

	/**
	 * Get the raw data for this part of the upgrade.
	 *
	 * @return	array	Returns a reference to the source data array.
	 * @since	0.4.5
	 * @throws	Exception
	 */
	protected function &getSourceData()
	{

		$where = "scope = 'content'";

		$rows = parent::getSourceData(
			'`id` AS sid, `title`, `alias`, \'category\' AS extension, `description`, `published`, `checked_out`, `checked_out_time`, `access`, `params`',
		  null,
			$where,
			'id'
		);

		// Do some custom post processing on the list.
		foreach ($rows as &$row)
		{
			$row['title'] = mysql_real_escape_string($row['title']);
			$row['description'] = mysql_real_escape_string($row['description']);
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
	 * @since	0.4.
	 * @throws	Exception
	 */
	protected function setDestinationData()
	{
		// Get the source data.
		$rows	= $this->getSourceData();

		// Truncate j16_jupgrade_categories table
		$clean	= $this->cleanDestinationData('j16_jupgrade_categories');

		// Insert uncategorized id
		$query = "INSERT INTO `j16_jupgrade_categories` (`old`, `new`) VALUES (0, 2)";
		$this->db_new->setQuery($query);
		$this->db_new->query();

		// Check for query error.
		$error = $this->db_new->getErrorMsg();

		if ($error) {
			throw new Exception($error);
		}

		//
		// Insert the categories
		//
		foreach ($rows as $row)
		{
			// Convert the array into an object.
			$row = (object) $row;

			$this->insertCategory($row);
			$this->insertAsset($row);

			 // Childen categories
			$query = "SELECT `id` AS sid, `title`, `alias`, `section` AS extension, `description`, `published`, `checked_out`, `checked_out_time`, `access`, `params`"
			." FROM {$this->config_old['prefix']}categories"
			." WHERE section = {$row->sid}"
			." ORDER BY id ASC";
			$this->db_old->setQuery($query);
			$categories = $this->db_old->loadObjectList();

			for($y=0;$y<count($categories);$y++){

				// Correct some values
				$categories[$y]->params = $this->convertParams($categories[$y]->params);
				$categories[$y]->title = mysql_real_escape_string($categories[$y]->title);
				$categories[$y]->description = mysql_real_escape_string($categories[$y]->description);
				$categories[$y]->access = $categories[$y]->access+1;
				$categories[$y]->language = '*';

				// Correct alias
				if ($categories[$y]->alias == "") {
					$categories[$y]->alias = JFilterOutput::stringURLSafe($categories[$y]->title);
				}

				// Inserting category and asset
				$this->insertCategory($categories[$y], $row->title);
				$this->insertAsset($categories[$y], $row->title);

			}

		}
	}

	/**
	 * The public entry point for the class.
	 *
	 * @return	void
	 * @since	0.5.5
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

// Migrate the Categories.
$categories = new jUpgradeCategories;
$categories->upgrade();

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
 * Upgrade class for categories
 *
 * This class takes the categories from the existing site and inserts them into the new site.
 *
 * @since	0.4.5
 */
class jUpgradeCategories extends jUpgradeCategory
{
	/**
	 * @var		string	The name of the source database table.
	 * @since	0.4.5
	 */
	protected $source = '#__categories';

	/**
	 * @var		string	The name of the destination database table.
	 * @since	0.4.5
	 */
	protected $destination = '#__categories';

	/**
	 * Sets the data in the destination database.
	 *
	 * @return	void
	 * @since	0.4.
	 * @throws	Exception
	 */
	protected function setDestinationData()
	{
		// Delete uncategorized categories
		$query = "DELETE FROM {$this->destination} WHERE id > 1";
		$this->db_new->setQuery($query);
		$this->db_new->query();

		/**
		 * Inserting the categories
		 * @since	2.5.1
		 */

		// Content categories
		$this->section = 'com_content'; 
		// Get the source data.
		$categories	= $this->getSourceData();
		// rootidmap
		$rootidmap = 0;

		// JTable:store() run an update if id exists so we create them first
		foreach ($categories as $category)
		{
			$object = new stdClass();

			if ($category['id'] == 1) {
				$query = "SELECT id"
				." FROM #__categories"
				." ORDER BY id DESC LIMIT 1";
				$this->db_new->setQuery($query);
				$lastid = $this->db_new->loadResult();	

				$object->id = $lastid + 1;
				$rootidmap = $object->id;
			}else{
				$object->id = $category['id'];
			}

			// Inserting the menu
			if (!$this->db_new->insertObject($this->destination, $object)) {
				throw new Exception($this->db_new->getErrorMsg());
			}
		}

		/**
		 * Inserting the sections
		 *
		 * @since	2.5.1
		 */
		// Content categories
		$this->source = '#__sections'; 
		// Get the source data.
		$sections	= $this->getSourceData();

		// Insert the sections
		foreach ($sections as $section)
		{
			// Inserting the category
			$this->insertCategory($section);
		}

		/**
		 * Updating the categories
		 *
		 * @since	2.5.1
		 */
		$catmap = $this->getMapList('categories', 'com_section');

		// Insert the categories
		foreach ($categories as $category)
		{
			if ($category['id'] == 1) {
				$category['id'] = $rootidmap;
			}

			$category['asset_id'] = null;
			$category['parent_id'] = isset($catmap[$category['extension']]->new) ? $catmap[$category['extension']]->new : 1;
			$category['lft'] = null;
			$category['rgt'] = null;
			$category['level'] = null;

			// Inserting the category
			$this->insertCategory($category);			
		}

		// Require the files
		require_once JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jupgrade'.DS.'includes'.DS.'helper.php';

		// The sql file with menus
		$sqlfile = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jupgrade'.DS.'sql'.DS.'categories.sql';

		// Import the sql file
	  if (JUpgradeHelper::populateDatabase($this->db_new, $sqlfile, $errors) > 0 ) {
	  	return false;
	  }
	}
}

<?php
/**
 * jUpgrade
 *
 * @version		  $Id: 
 * @package		  MatWare
 * @subpackage	com_jupgrade
 * @author      Matias Aguirre <maguirre@matware.com.ar>
 * @link        http://www.matware.com.ar
 * @copyright		Copyright 2006 - 2011 Matias Aguirre. All rights reserved.
 * @license		  GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * Upgrade class for categories
 *
 * This class takes the categories banners from the existing site and inserts them into the new site.
 *
 * @since	1.2.2
 */
class jUpgradeCategory extends jUpgrade
{
	/**
	 * @var		string	The name of the section of the categories.
	 * @since	1.2.2
	 */
	public $section = '';
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

		if ($this->section == 'com_content' && $this->source == '#__categories') {
			$select = '`id` AS sid, `title`, `alias`, `section` AS extension, `description`, `published`, `checked_out`, `checked_out_time`, `access`, `params`';
			$where = "section REGEXP '^[\\-\\+]?[[:digit:]]*\\.?[[:digit:]]*$'";
		}else if ($this->source == '#__categories') {
			$select = '`id` AS sid, `title`, `alias`, `section` AS extension, `description`, `published`, `checked_out`, `checked_out_time`, `access`, `params`';
			$where = "section = '{$this->section}'";
		}else if ($this->source == '#__sections') {
			$select = '`id` AS sid, `title`, `alias`, \'com_section\' AS extension, `description`, `published`, `checked_out`, `checked_out_time`, `access`, `params`';
			$where = "scope = 'content'";
		}

		$rows = parent::getSourceData(
			$select,
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

			if ($row['extension'] == 'com_banner') {
				$row['extension'] = "com_banners";
			}else if ($row['extension'] == 'com_contact_details') {
				$row['extension'] = "com_contact";
			}

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
			$this->insertCategory($row);
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

	/**
	 * Inserts a category
	 *
	 * @access  public
	 * @param   object  An object whose properties match table fields
	 * @since	0.4.
	 */
	public function insertCategory($object, $parent = false)
	{
		// Getting the asset table
		$category = JTable::getInstance('Category', 'JTable', array('dbo' => $this->db_new));

		// Get old id
		$oldlist = new stdClass();
		$oldlist->section = $object['extension'];
		$oldlist->old = $object['sid'];
		unset($object['sid']);

		// Correct extension
		if ($object['extension'] == "com_banner") {
			$object['extension'] = "com_banners";
		}

		if (is_numeric($object['extension']) || $object['extension'] == "" || $object['extension'] == "category") {
			$object['extension'] = "com_content";
		}

		// If has parent made $path and get parent id
		if ($parent !== false) {

			$object['path'] = JFilterOutput::stringURLSafe($parent)."/".$object['alias'];

			// Fixing title quote
			$parent = str_replace("'", "&#39;", $parent);

			$query = "SELECT id FROM #__categories WHERE title = '{$parent}' LIMIT 1";
			$this->db_new->setQuery($query);
			$object['parent_id'] = $this->db_new->loadResult();

			// Check for query error.
			$error = $this->db_new->getErrorMsg();

			if ($error) {
				throw new Exception($error);
			}

			// Setting the location of the new category
			$category->setLocation($object['parent_id'], 'last-child');
		}
		else {
			$object['path'] = $object['alias'];

			// Setting the location of the new category
			$category->setLocation(1, 'last-child');

			// Fixing extension name if it's section
			if ($object['extension'] == 'com_section') {
				$object['extension'] = "com_content";
			}
		}

		// Bind data to save category
		if (!$category->bind($object)) {
			echo JError::raiseError(500, $category->getError());
		}

		// Insert the category
		if (!$category->store()) {
			echo JError::raiseError(500, $category->getError());
		}

		// Returning sid needed by insertAsset()
		$object['sid'] = $oldlist->old;

		// Get new id
		$oldlist->new = $category->id;

		// Save old and new id
		if (!$this->db_new->insertObject('jupgrade_categories', $oldlist)) {
			throw new Exception($this->db_new->getErrorMsg());
		}

	 	return true;
	}
}

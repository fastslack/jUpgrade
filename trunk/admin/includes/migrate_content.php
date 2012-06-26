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
 * Upgrade class for content
 *
 * This class takes the content from the existing site and inserts them into the new site.
 *
 * @since	0.4.5
 */
class jUpgradeContent extends jUpgrade
{
	/**
	 * @var		string	The name of the source database table.
	 * @since	0.4.5
	 */
	protected $source = '#__content AS c';

	/**
	 * @var		string	The name of the destination database table.
	 * @since	0.4.5
	 */
	protected $destination = '#__content';

	/**
	 * Get the raw data for this part of the upgrade.
	 *
	 * @return	array	Returns a reference to the source data array.
	 * @since	0.4.5
	 * @throws	Exception
	 */
	protected function &getSourceData()
	{
		$where = "o.section REGEXP '^[\\-\\+]?[[:digit:]]*\\.?[[:digit:]]*$'";

		$rows = parent::getSourceData(
			'`id`, `title`, `alias`, `title_alias`, `introtext`, `fulltext`, `state`, '
			.'`mask`, `catid`, `created`, `created_by`, `created_by_alias`, '
			.'`modified`, `modified_by`, `checked_out`, `checked_out_time`, `publish_up`, `publish_down`, '
			.'`images`, `urls`, `attribs`, `version`, `parentid`, `ordering`, `metakey`, `metadesc`, '
			.'`access`, `hits` ',
			null,
			null,
			'id'
		);

		// Get category mapping
		$query = "SELECT * FROM jupgrade_categories WHERE section REGEXP '^[\\-\\+]?[[:digit:]]*\\.?[[:digit:]]*$' AND old>0";
		$this->db_new->setQuery($query);
		$catidmap = $this->db_new->loadObjectList('old');
		
		// Find uncategorised category id
		$query = "SELECT id FROM #__categories WHERE extension='com_content' AND path='uncategorised' LIMIT 1";
		$this->db_new->setQuery($query);
		$defaultId = $this->db_new->loadResult();
		
		// Do some custom post processing on the list.
		foreach ($rows as &$row)
		{
			// Convert HTML entities to UTF-8 on escaped entries
			$row['title'] = $this->entities2Utf8($row['title']);
			$row['created_by_alias'] = $this->entities2Utf8($row['created_by_alias']);
			$row['metakey'] = $this->entities2Utf8($row['metakey']);
			$row['metadesc'] = $this->entities2Utf8($row['metadesc']);
			
			$row['attribs'] = $this->convertParams($row['attribs']);
			$row['access'] = $row['access'] == 0 ? 1 : $row['access'] + 1;
			$row['language'] = '*';

			// Correct state
			if ($row['state'] == -1) {
				$row['state'] = 2;
			}
			
			// Map catid
			$row['catid'] = isset($catidmap[$row['catid']]) ? $catidmap[$row['catid']]->new : $defaultId;

			/*
			 * Prevent JGLOBAL_ARTICLE_MUST_HAVE_TEXT error
			 */
			if (trim($row['introtext']) == '' && trim($row['fulltext']) == '')
			{
				$row['introtext'] = '&nbsp;';
			}
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
		$object->show_parent_category = isset($object->show_parent_category) ? $object->show_parent_category : "";
		$object->link_parent_category = isset($object->link_parent_category) ? $object->link_parent_category : "";
		$object->link_author = isset($object->link_author) ? $object->link_author : "";
		$object->show_publish_date = isset($object->show_publish_date) ? $object->show_publish_date : "";
		$object->show_item_navigation = isset($object->show_item_navigation) ? $object->show_item_navigation : "";
		$object->show_icons = isset($object->show_icons) ? $object->show_icons : "";
		$object->show_vote = isset($object->show_vote) ? $object->show_vote : "";
		$object->show_hits = isset($object->show_hits) ? $object->show_hits : "";
		$object->show_noauth = isset($object->show_noauth) ? $object->show_noauth : "";
		$object->alternative_readmore = isset($object->alternative_readmore) ? $object->alternative_readmore : "";
		$object->article_layout = isset($object->article_layout) ? $object->article_layout : "";
		$object->show_publishing_options = isset($object->show_publishing_options) ? $object->show_publishing_options : "";
		$object->show_article_options = isset($object->show_article_options) ? $object->show_article_options : "";
		$object->show_urls_images_backend = isset($object->show_urls_images_backend) ? $object->show_urls_images_backend : "";
		$object->show_urls_images_frontend = isset($object->show_urls_images_frontend) ? $object->show_urls_images_frontend : "";
	}

	/**
	* Sets the data in the destination database.
	*
	* @return	void
	* @since	0.5.3
	* @throws	Exception
	*/
	protected function setDestinationData()
	{
		// Truncate the table for better debug
		$clean	= $this->cleanDestinationData();

		// Get the source data.
		$rows	= $this->getSourceData();
		$table	= empty($this->destination) ? $this->source : $this->destination;

		// Initialize values
		$aliases = array();
		$unique_alias_suffix = 1;

		//
		// JTable:store() run an update if id exists so we create them first
		//
		foreach ($rows as $row)
		{
			$object = new stdClass();
			$object->id = $row['id'];

			// Inserting the menu
			if (!$this->db_new->insertObject($table, $object)) {
				throw new Exception($this->db_new->getErrorMsg());
			}
		}

		//
		// Insert content data
		//
		foreach ($rows as $row)
		{
			// The Joomla 2.5 database structure does not allow duplicate aliases
			if (in_array($row['alias'], $aliases, true)) {
				$row['alias'] .= $unique_alias_suffix;
				$unique_alias_suffix++;
			}
			$aliases[] = $row['alias'];

			// Getting the asset table
			$content = JTable::getInstance('Content', 'JTable', array('dbo' => $this->db_new));

			// Bind data to save content
			if (!$content->bind($row)) {
				echo JError::raiseError(500, $content->getError());
			}

			// Check the content
			if (!$content->check()) {
				echo JError::raiseError(500, $content->getError());
			}

			// Insert the content
			if (!$content->store()) {
				echo JError::raiseError(500, $content->getError());
			}

		}

		$params = $this->getParams();

		/*
		 * Update the featured column with records from content_frontpage
		 */
		$query = "UPDATE `{$params->prefix_new}content`, `{$this->config_old['prefix']}content_frontpage`"
		." SET `{$params->prefix_new}content`.featured = 1 WHERE `{$params->prefix_new}content`.id = `{$this->config_old['prefix']}content_frontpage`.content_id";
		$this->db_new->setQuery($query);
		$this->db_new->query();

		// Check for query error.
		$error = $this->db_new->getErrorMsg();

		if ($error) {
			throw new Exception($error);
		}

		/*
		 * Upgrading the content configuration
		 */
		$query = "SELECT params FROM #__components WHERE `option` = 'com_content'";
		$this->db_old->setQuery($query);
		$articles_config = $this->db_old->loadResult();

		// Check for query error.
		$error = $this->db_new->getErrorMsg();

		if ($error) {
			throw new Exception($error);
		}

		// Convert params to JSON
		$articles_config = $this->convertParams($articles_config);

		// Update the
		$query = "UPDATE #__extensions SET `params` = '{$articles_config}' WHERE `element` = 'com_content'";
		$this->db_new->setQuery($query);
		$this->db_new->query();

		// Check for query error.
		$error = $this->db_new->getErrorMsg();

		if ($error) {
			throw new Exception($error);
		}
	}
}

/**
 * Upgrade class for FrontEnd content
 *
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @since		0.4.4
 */
class jUpgradeContentFrontpage extends jUpgrade
{
	/**
	 * @var		string	The name of the source database table.
	 * @since	0.4.4
	 */
	protected $source = '#__content_frontpage';

}


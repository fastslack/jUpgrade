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
			'`id`, `title`, NULL AS `alias`, `title_alias`, `introtext`, `fulltext`, `state`, '
		 .'`mask`, o.new AS catid, `created`, `created_by`, `created_by_alias`, '
		 .'`modified`, `modified_by`, `checked_out`, `checked_out_time`, `publish_up`, `publish_down`, '
		 .'`images`, `urls`, `attribs`, `version`, `parentid`, `ordering`, `metakey`, `metadesc`, '
     .'`access`, `hits` ',
		 'LEFT JOIN jupgrade_categories AS o ON o.old = c.catid',
		 $where,
			'id'
		);

		// Do some custom post processing on the list.
		foreach ($rows as &$row)
		{
			$row['attribs'] = $this->convertParams($row['attribs']);
			$row['access'] = $row['access'] == 0 ? 1 : $row['access'] + 1;
			$row['language'] = '*';

			// Correct alias
			if ($row['alias'] == "") {
				$row['alias'] = JFilterOutput::stringURLSafe($row['title']);
			}

			// Correct state
			if ($row['state'] == -1) {
				$row['state'] = 2;
			}

		}

		return $rows;
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

		// Insert content data
		foreach ($rows as $row)
		{
			// Convert the array into an object.
			$row = (object) $row;

			//Cleanup
			unset($row->extension);

			if (!$this->db_new->insertObject($table, $row)) {
				throw new Exception($this->db_new->getErrorMsg());
			}

			// set section value to identify asset
			$row->extension = 'article';

			if (!$this->insertAsset($row)) {
				throw new Exception('JUPGRADE_ERROR_INSERTING_ASSET');
			}

		}

		$params = $this->getParams();

		// Update the featured column with records from content_frontpage
		$query = "UPDATE `{$params->prefix_new}content`, `{$this->config_old['prefix']}content_frontpage`"
		." SET `{$params->prefix_new}content`.featured = 1 WHERE `{$params->prefix_new}content`.id = `{$this->config_old['prefix']}content_frontpage`.content_id";
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
 * Upgrade class for the Usergroup Map
 *
 * This translates the group mapping table from 1.5 to 1.6.
 * Group id's up to 30 need to be mapped to the new group id's.
 * Group id's over 30 can be used as is.
 * User id's are maintained in this upgrade process.
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


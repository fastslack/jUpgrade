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
	public $source = '#__components AS c';

	/**
	 * @var		string	The name of the destination database table.
	 * @since	0.4.5
	 */
	public $destination = '#__extensions';

	/**
	 * count adapters
	 * @var int
	 * @since	1.1.0
	 */
	public $count = 0;

	/**
	 * Get the raw data for this part of the upgrade.
	 *
	 * @return	array	Returns a reference to the source data array.
	 * @since	0.4.5
	 * @throws	Exception
	 */
	protected function &getSourceData()
	{
		$where = array();
		$where[] = "c.parent = 0";
		$where[] = "c.option NOT IN ('com_admin', 'com_banners', 'com_cache', 'com_categories', 'com_checkin', 'com_config', 'com_contact', 'com_content', 'com_cpanel', 'com_frontpage', 'com_installer', 'com_jupgrade', 'com_languages', 'com_login', 'com_mailto', 'com_massmail', 'com_media', 'com_menus', 'com_messages', 'com_modules', 'com_newsfeeds', 'com_plugins', 'com_poll', 'com_search', 'com_sections', 'com_templates', 'com_user', 'com_users', 'com_weblinks', 'com_wrapper' )";

		$rows = parent::getSourceData(
			'id, name, \'component\' AS type, `option` AS element',
		 null,
		 $where,
			'id'
		);

		// Do some custom post processing on the list.
		foreach ($rows as &$row)
		{
			//echo $row['element']."\n";

			//$element = substr($row['element'], 4);
			$element = $row['element'];
			//echo $element."\n";

			//
			$filename = dirname(__FILE__).DS.'adapters'.DS.strtolower($element).'.php';

			if (file_exists($filename)) {
				$query = "INSERT INTO j16_jupgrade_steps (name, status, extension, state) VALUES('{$element}', 0, 1, '' )";
				$this->db_new->setQuery($query);
				$this->db_new->query();

				$this->count = $this->count+1;
			}

			unset($row['id']);
		}

		return $rows;
	}
}

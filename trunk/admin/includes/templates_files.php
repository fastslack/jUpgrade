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
define('JPATH_BASE',	dirname(__FILE__));
define('DS',			DIRECTORY_SEPARATOR);

require_once JPATH_BASE.DS.'defines.php';
require_once JPATH_BASE.DS.'jupgrade.class.php';

/**
 * Upgrade class for 3rd party templates
 *
 * This class search for templates to be migrated
 *
 * @since	0.4.8
 */
class jUpgradeTemplatesFiles extends jUpgrade
{
	/**
	 * @var		string	The name of the destination database table.
	 * @since	0.4.8
	 */
	public $destination = '#__template_styles';


	/**
	 * Get the raw data for this part of the upgrade.
	 *
	 * @return	array	Returns a reference to the source data array.
	 * @since	0.4.8
	 * @throws	Exception
	 */
	protected function &getSourceData()
	{

		$folders = JFolder::folders(JPATH_ROOT.DS.'templates');
		$folders = array_diff($folders, array("system", "beez"));
		sort($folders);
		//print_r($folders);

		$rows = array();
		// Do some custom post processing on the list.
		for($i=0;$i<count($folders);$i++) {

			$rows[$i] = array();
			$rows[$i]['template'] = $folders[$i];
			$rows[$i]['client_id'] = 0;
			$rows[$i]['home'] = 0;
			$rows[$i]['title'] = $folders[$i];

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
		//$table	= empty($this->destination) ? $this->source : $this->destination;

		for($i=0;$i<count($rows);$i++) {
			$src = JPATH_ROOT.DS.'templates'.DS.$rows[$i]['template'];
			$dest = JPATH_SITE.DS.'templates'.DS.$rows[$i]['template'];
			JFolder::copy($src, $dest);
		}
	}


}

// Migration 3rd party templates
$templates = new jUpgradeTemplatesFiles;
$templates->upgrade(); 

?>

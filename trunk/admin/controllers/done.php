<?php
/**
 * jUpgrade
 *
 * @version		$Id: 
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @copyright	Copyright 2006 - 2011 Matias Aguire. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @author		Matias Aguirre <maguirre@matware.com.ar>
 * @link		http://www.matware.com.ar
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * checks Controller
 *
 * @package		MatWare
 * @subpackage	com_jupgrade
 */
class jupgradeControllerDone extends JController
{	
	function done()
	{
		jimport('joomla.filesystem.folder');

		$olddir = JPATH_ROOT.'/jupgrade/installation';
		$dir = JPATH_ROOT.'/jupgrade/installation-old';

		if (is_dir($dir)) {
			JFolder::delete($dir);
		}

		if (JFolder::move($olddir, $dir)) {
			echo 1;
			exit;
		}	else {
			echo 0;
			exit;
		}

	}
}

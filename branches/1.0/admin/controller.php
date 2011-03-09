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

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * @package		MatWare
 * @subpackage	com_jupgrade
 */
class jupgradeController extends JController
{
	/**
	 * Display controller.
	 *
	 * @param	string	Sub-template to display.
	 *
	 * @return	void
	 */
	function display($tpl = '')
	{
		parent::display($tpl, null);
	}
}

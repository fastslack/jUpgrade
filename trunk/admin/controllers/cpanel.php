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

/**
 * cPanel Controller
 *
 * @package		MatWare
 * @subpackage	com_jupgrade
 */
class jupgradeControllerCpanel extends jupgradeController
{
	/**
	 * Display the view.
	 *
	 * @return	void
	 */
	function display()
	{
		JRequest::setVar( 'view', 'cpanel' );
		parent::display();
	}

	/**
	 * Redirect to the help site.
	 *
	 * return	void
	 */
	function help()
	{
		$msg = "";
		$link = "http://www.matware.com.ar/forum/listcat/categories.html";
		$this->setRedirect($link, $msg);
	}
}

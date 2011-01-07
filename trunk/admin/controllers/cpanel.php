<?php
/**
 * jUpgrade
 *
 * @version			$Id$
 * @package			MatWare
 * @subpackage	com_jupgrade
 * @author      Matias Aguirre <maguirre@matware.com.ar>
 * @link        http://www.matware.com.ar
 * @license			GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * cPanel Controller
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
class jupgradeControllerCpanel extends jupgradeController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
	}

  function display() {
    JRequest::setVar( 'view', 'cpanel' );
    parent::display();
  }

  function help() {
		$msg = "";
		$link = "http://www.matware.com.ar/foros.html";
		$this->setRedirect($link, $msg);   
  }

}
?>

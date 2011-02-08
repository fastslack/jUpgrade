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

jimport( 'joomla.application.component.view' );

/**
 * @package		MatWare
 * @subpackage	com_jupgrade
 */
class jupgradeViewCpanel extends JView
{
	/**
	 * Display the view.
	 *
	 * @param	string	$tpl	The subtemplate to display.
	 *
	 * @return	void
	 */
	function display($tpl = null)
	{
		JToolBarHelper::title(JText::_( 'jUpgrade' ), 'jupgrade');
		JToolBarHelper::custom('cpanel', 'back.png', 'back_f2.png', 'Back', false, false);
		JToolBarHelper::preferences('com_jupgrade', '500');
		JToolBarHelper::spacer();
		JToolBarHelper::custom('help', 'help.png', 'help_f2.png', 'Help', false, false);
		JToolBarHelper::spacer();

		$xmlfile = JPATH_COMPONENT.'/jupgrade.xml';
 		$xml = new JSimpleXML;
 		$xml->loadFile($xmlfile);
		$attrib = $xml->document->version[0];
		//print_r($attrib->data());

		$this->assignRef('version',	$attrib->data());

		parent::display($tpl);
	}
}

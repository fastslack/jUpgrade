<?php
/**
 * jUpgrade
 *
 * @author      Matias Aguirre
 * @email       maguirre@matware.com.ar
 * @url         http://www.matware.com.ar
 * @license     GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class jupgradeViewCpanel extends JView
{

	function display($tpl = null)
	{
		JToolBarHelper::title(   JText::_( 'jUpgrade' ), 'jupgrade' );
		JToolBarHelper::custom('back', 'back.png', 'back_f2.png', 'Back', false, false);
		JToolBarHelper::spacer();

		$xmlfile = JPATH_COMPONENT.DS."jupgrade.xml";
 		$xml = new JSimpleXML;
 		$xml->loadFile($xmlfile);
		$attrib = $xml->document->version[0];
		//print_r($attrib->data());

		$this->assignRef('version',	$attrib->data());

		parent::display($tpl);
	}
}

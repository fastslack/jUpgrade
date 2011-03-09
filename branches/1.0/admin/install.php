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

// Load the component language file.
$language = JFactory::getLanguage();
$language->load('com_jupgrade');

// PHP 5 check.
if (version_compare(PHP_VERSION, '5.2.4', '<')) {
	$this->parent->abort(JText::_('J_USE_PHP5'));

	return false;
}

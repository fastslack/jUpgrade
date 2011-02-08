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

define('_JEXEC', 1);
define('JPATH_BASE', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);
require_once JPATH_BASE.'/defines.php';

require_once JPATH_LIBRARIES.'/joomla/methods.php';
require_once JPATH_LIBRARIES.'/joomla/factory.php';
require_once JPATH_LIBRARIES.'/joomla/import.php';
require_once JPATH_LIBRARIES.'/joomla/error/error.php';
require_once JPATH_LIBRARIES.'/joomla/base/object.php';
require_once JPATH_LIBRARIES.'/joomla/database/database.php';
require_once JPATH_INSTALLATION.'/models/database.php';
//require_once JPATH_INSTALLATION.'/helpers/database.php';

require(JPATH_ROOT.DS."configuration.php");

$jconfig = new JConfig();
//print_r($jconfig);

$config = array();
$config['dbo'] = & JInstallationHelperDatabase::getDBO('mysql', $jconfig->host, $jconfig->user, $jconfig->password, $jconfig->db, 'j16_');

$installHelper = new JInstallationModelDatabase($config);

//print_r($installHelper);

$dbscheme = JPATH_INSTALLATION.'/sql/mysql/joomla.sql';

if ($installHelper->populateDatabase($config['dbo'], $dbscheme, $errors) > 0) {
	return 0;
}
else {
	return 1;
}

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
if (file_exists(JPATH_LIBRARIES.'/joomla/import.php')) {
	require_once JPATH_LIBRARIES.'/joomla/import.php';
}else if (file_exists(JPATH_LIBRARIES.'/import.php')) {
	require_once JPATH_LIBRARIES.'/import.php';
}
require_once JPATH_LIBRARIES.'/joomla/methods.php';
require_once JPATH_LIBRARIES.'/joomla/factory.php';
require_once JPATH_LIBRARIES.'/joomla/error/error.php';
require_once JPATH_LIBRARIES.'/joomla/base/object.php';
if (file_exists(JPATH_LIBRARIES.'/joomla/utilities/arrayhelper.php')) {
	require_once JPATH_LIBRARIES.'/joomla/utilities/arrayhelper.php';
}
if (file_exists(JPATH_LIBRARIES.'/joomla/log/log.php')) {
	require_once JPATH_LIBRARIES.'/joomla/log/log.php';
}

require_once JPATH_INSTALLATION.'/models/configuration.php';

require JPATH_ROOT.'/configuration.php';

$jconfig = new JConfig();
$jconfig->db_type   = 'mysql';
$jconfig->db_host	= $jconfig->host;
$jconfig->db_user	= $jconfig->user;
$jconfig->db_pass	= $jconfig->password;
$jconfig->db_name	= $jconfig->db;
$jconfig->db_prefix	= "j16_";
$jconfig->site_name	= $jconfig->sitename;

$jconfig->admin_email	= $jconfig->mailfrom;
$jconfig->site_metadesc	= $jconfig->MetaDesc;
$jconfig->site_metakeys	= $jconfig->MetaKeys;

$jconfig->ftp_enable	= 0;
$jconfig->ftp_save	= 0;

// Run the configuration creation
if (JInstallationModelConfiguration::_createConfiguration($jconfig) > 0) {
	echo 1;
}else {
	echo 0;
}

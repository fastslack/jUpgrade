<?php
/**
 * jUpgrade
 *
 * @version		$Id: install.php
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
 * install_config Controller
 *
 * @package		MatWare
 * @subpackage	com_jupgrade
 */
class jupgradeControllerInstall extends JController
{	
	function install_config()
	{	
		require_once JPATH_COMPONENT_ADMINISTRATOR.'/includes/configuration.php';
		require_once JPATH_ROOT.'/configuration.php';

		$jconfig = new JConfig();
				
		$jconfig->db_type   = 'mysql';
		$jconfig->db_host	= $jconfig->host;
		$jconfig->db_user	= $jconfig->user;
		$jconfig->db_pass	= $jconfig->password;
		$jconfig->db_name	= $jconfig->db;
		$jconfig->db_prefix	= "j16_";
		$jconfig->site_name	= $jconfig->sitename;
		
		//print_r($jconfig);
		
		if (JInstallationModelConfiguration::_createConfiguration($jconfig) > 0) {
			echo 1;
			exit;
		}
		else {
			echo 0;
			exit;
		}
	}
	
	function install_db()
	{
		require_once JPATH_COMPONENT_ADMINISTRATOR.'/includes/database.php';

		// jUpgrade class
		require_once JPATH_COMPONENT_ADMINISTRATOR.'/includes/jupgrade.class.php';
				
		// jUpgrade class
		$jupgrade = new jUpgrade;
		
		// Getting the component parameter with global settings
		$params = $jupgrade->getParams();
		
		// getting config
		$jconfig = new JConfig();
		
		$config = array();
		$config['dbo'] = & JInstallationHelperDatabase::getDBO('mysql', $jconfig->host, $jconfig->user, $jconfig->password, $jconfig->db, 'j16_');
		
		// getting helper
		$installHelper = new JInstallationModelDatabase($config);
		
		// installing global database
		$schema = JPATH_ROOT.'/jupgrade/installation/sql/mysql/joomla.sql';
		
		if (!$installHelper->populateDatabase($config['dbo'], $schema) > 0) {
			echo 1;
			exit;
		}
		
		// installing Molajo database
		if ($params->mode == 1) {
		
			$schema = JPATH_ROOT.'/jupgrade/installation/sql/mysql/joomla2.sql';
		
			if (!$installHelper->populateDatabase($config['dbo'], $schema) > 0) {
				echo 1;
				exit;
			}
		}
	}
}

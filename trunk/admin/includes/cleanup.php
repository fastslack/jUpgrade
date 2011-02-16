<?php
/**
 * jUpgrade
 *
 * @version		  $Id: cleanup.php 20716 2011-02-15 11:12:27Z maguirre $
 * @package		  MatWare
 * @subpackage	com_jupgrade
 * @author      Matias Aguirre <maguirre@matware.com.ar>
 * @link        http://www.matware.com.ar
 * @copyright		Copyright 2006 - 2011 Matias Aguire. All rights reserved.
 * @license		  GNU General Public License version 2 or later; see LICENSE.txt
 */

define('_JEXEC', 1);
define('JPATH_BASE', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);
require_once JPATH_BASE.'/defines.php';
require_once JPATH_BASE.'/jupgrade.class.php';

// jUpgrade class
$jupgrade = new jUpgrade;

// Truncate all j16_tables
$query = "DROP TABLE `j16_assets`, `j16_banners`, `j16_banner_clients`, `j16_banner_tracks`, `j16_categories`, `j16_contact_details`, `j16_content`, `j16_content_frontpage`, `j16_content_rating`, `j16_core_log_searches`, `j16_extensions`,  `j16_languages`, `j16_menu`, `j16_menu_types`, `j16_messages`, `j16_messages_cfg`, `j16_modules`, `j16_modules_menu`, `j16_newsfeeds`, `j16_redirect_links`, `j16_schemas`, `j16_session`, `j16_template_styles`, `j16_updates`, `j16_update_categories`, `j16_update_sites`, `j16_update_sites_extensions`, `j16_usergroups`, `j16_users`, `j16_user_profiles`, `j16_user_usergroup_map`, `j16_viewlevels`, `j16_weblinks`";
$jupgrade->db_new->setQuery($query);
$jupgrade->db_new->query();

// Set all status to 0
$query = "UPDATE j16_jupgrade_steps SET status = 0";
$jupgrade->db_new->setQuery($query);
$jupgrade->db_new->query();

// Check for query error.
$error = $jupgrade->db_new->getErrorMsg();

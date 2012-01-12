<?php
/**
 * @version       $Id: 
 * @package       jUpgrade
 * @subpackage    jUpgradeCli
 * @copyright     CopyRight 2006-2012 Matware All rights reserved.
 * @author        Matias Aguirre
 * @email         maguirre@matware.com.ar
 * @link          http://www.matware.com.ar/
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-2.0-standalone.html
 */

/*
 * Change the following path to suit where you have cloned or installed the Joomla Platform repository.
 * The default setting assumes you have the joomla platform and examples folder at the same level.
 */

// Setup the base path related constant.
define('JPATH_BASE', dirname(__FILE__));
define('JPATH_SITE', dirname(__FILE__));
define('JPATH_ROOT', dirname(__FILE__));
define('JPATH_LIBRARIES', dirname(dirname(dirname(__FILE__))).'/joomla-platform/libraries'   );

// Import the Joomla! Platform
require JPATH_LIBRARIES.'/import.php';
// Import the JCli class from the platform.
jimport('joomla.application.cli');
// Require the files
jimport('joomla.filesystem.file');
jimport('joomla.database.database');
// Require the files
require_once JPATH_BASE.'/includes/jupgrade.class.php';
require_once JPATH_BASE.'/includes/jupgrade.category.class.php';
require_once JPATH_BASE.'/includes/jupgrade.extensions.class.php';
// Require the files
require_once JPATH_BASE.'/includes/migrate_users.php';
require_once JPATH_BASE.'/includes/migrate_modules.php';
require_once JPATH_BASE.'/includes/migrate_categories.php';
require_once JPATH_BASE.'/includes/migrate_content.php';
require_once JPATH_BASE.'/includes/migrate_menus.php';
require_once JPATH_BASE.'/includes/migrate_banners.php';
require_once JPATH_BASE.'/includes/migrate_contacts.php';
require_once JPATH_BASE.'/includes/migrate_newsfeeds.php';
require_once JPATH_BASE.'/includes/migrate_weblinks.php';
// Require the files
require_once JPATH_BASE.'/includes/helper.php';

<?php
/**
 * jUpgrade
 *
 * @version		  $Id$
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

// Select the steps
$query = "SELECT * FROM j16_jupgrade_steps AS s WHERE s.status != 1 ORDER BY s.id ASC LIMIT 1";
$jupgrade->db_new->setQuery($query);
$step = $jupgrade->db_new->loadObject();

// Check for query error.
$error = $jupgrade->db_new->getErrorMsg();

// Check if steps is an object
if (!is_object($step)) {
	echo $error;
	exit;
}

// Require the file
require_once JPATH_BASE.'/migrate_'.$step->name.'.php';

switch ($step->name) {
	case 'users':
		// Migrate the users.
		$u1 = new jUpgradeUsers($step);
		$u1->upgrade();

		// Migrate the usergroups.
		$u2 = new jUpgradeUsergroups($step);
		$u2->upgrade();

		// Migrate the user-to-usergroup mapping.
		$u2 = new jUpgradeUsergroupMap($step);
		$u2->upgrade();

		break;
	case 'modules':
		// Migrate the Modules.
		$modules = new jUpgradeModules($step);
		$modules->upgrade();

		// Migrate the Modules Menus.
		$modulesmenu = new jUpgradeModulesMenu($step);
		$modulesmenu->upgrade();

		break;
	case 'categories':
		// Migrate the Categories.
		$categories = new jUpgradeCategories($step);
		$categories->upgrade();

		break;
	case 'content':
		// Migrate the Content.
		$content = new jUpgradeContent($step);
		$content->upgrade();

		// Migrate the Frontpage Content.
		$frontpage = new jUpgradeContentFrontpage($step);
		$frontpage->upgrade();

		break;
	case 'menus':
		// Migrate the menu.
		$menu = new jUpgradeMenu;
		$menu->upgrade();

		// Migrate the menu types.
		$menutypes = new jUpgradeMenuTypes($step);
		$menutypes->upgrade();

		break;
	case 'banners':
		// Migrate the categories of banners.
		$bannersCat = new jUpgradeBannersCategories($step);
		$bannersCat->upgrade();

		// Migrate the banners.
		$banners = new jUpgradeBanners($step);
		$banners->upgrade();

		break;
	case 'contacts':
		// Migrate the contacts.
		$contacts = new jUpgradeContacts($step);
		$contacts->upgrade();

		// Migrate the categories of contacts.
		$contactsCat = new jUpgradeContactsCategories($step);
		$contactsCat->upgrade();

		break;
	case 'newsfeeds':
		// Migrate the categories of newsfeeds.
		$newsfeedsCat = new jUpgradeNewsfeedsCategories($step);
		$newsfeedsCat->upgrade();

		// Migrate the newsfeeds.
		$newsfeeds = new jUpgradeNewsfeeds;
		$newsfeeds->upgrade();

		break;
	case 'weblinks':
		// Migrate the categories of weblinks.
		$weblinksCat = new jUpgradeWeblinksCategories($step);
		$weblinksCat->upgrade();

		// Migrate the weblinks.
		$weblinks = new jUpgradeWeblinks($step);
		$weblinks->upgrade();

		break;
}


// updating the status flag
$query = "UPDATE j16_jupgrade_steps SET status = 1"
." WHERE name = '{$step->name}'";
$jupgrade->db_new->setQuery($query);
$jupgrade->db_new->query();

// Check for query error.
$error = $jupgrade->db_new->getErrorMsg();

echo ";|;".$step->id.";|;".$step->name;

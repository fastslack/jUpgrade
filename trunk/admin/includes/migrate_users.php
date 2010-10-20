<?php
/**
 * jUpgrade
 *
 * @author      Matias Aguirre
 * @email       maguirre@matware.com.ar
 * @url         http://www.matware.com.ar
 * @license     GNU/GPL
 */

define( '_JEXEC', 1 );
define( 'JPATH_BASE', dirname(__FILE__) );
define( 'DS', DIRECTORY_SEPARATOR );

require_once ( JPATH_BASE .DS.'defines.php' );
require_once ( JPATH_BASE .DS.'jupgrade.class.php' );

$jUpgrade = new jUpgrade();

$db = &$jUpgrade->db_old;
$db_new = &$jUpgrade->db_new;
$config = &$jUpgrade->config;

##

// Migrating Users
$query = "SELECT `id`, `name`, `username`, `email`, `password`, `usertype`, `block`,"
		." `sendEmail`, `registerDate`, `lastvisitDate`, `activation`, `params`"
		." FROM " . $config['prefix'] . "users";

//echo $query;

$db->setQuery( $query );
$users = $db->loadObjectList();
//print_r($users);
//echo $db->getErrorMsg();

$users = $jUpgrade->fixParams($users);

$jUpgrade->insertObjectList($db_new, '#__users', $users);

// Migrating Groups
$query = "SELECT id, title FROM #__usergroups";
$db_new->setQuery( $query );
$gids = $db_new->loadAssocList();		
$newgids = array();
for($i=0;$i<count($gids);$i++) {
	$newgids[$gids[$i]['title']] = $gids[$i]['id'];
}

//print_r($newgids);

$query = "SELECT u.id AS user_id, u.usertype AS group_id"
." FROM {$config['prefix']}users AS u";
$db->setQuery( $query );
//echo $query;

$user_usergroup_map = $db->loadObjectList();
for($i=0;$i<count($user_usergroup_map);$i++) {
	if ($user_usergroup_map[$i]->group_id == "Super Administrator") {
		$user_usergroup_map[$i]->group_id = "Super Users";
	}
	$user_usergroup_map[$i]->group_id = $newgids[$user_usergroup_map[$i]->group_id];
}
//print_r($user_usergroup_map);
$ret = $jUpgrade->insertObjectList($db_new, '#__user_usergroup_map', $user_usergroup_map);	

//sleep(1);
?>

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

$query = "SELECT `title`,NULL AS `note`, `content`,`ordering`,`position`,"
." `checked_out`,`checked_out_time`,`published`,`module`,"
." `access`,`showtitle`,`params`,`client_id`,NULL AS `language`"
." FROM {$config['prefix']}modules"
." WHERE iscore = 0 AND id > 19"
." ORDER BY id ASC";

$db->setQuery( $query );
$modules = $db->loadObjectList();
//echo $db->errorMsg();


for($i=0;$i<count($modules);$i++){
	## Language
	$modules[$i]->language = "*";
	
	## Module
	if ($modules[$i]->module == "mod_mainmenu") {
		$modules[$i]->module = "mod_menu";
	}

	## Access
	$modules[$i]->access = $modules[$i]->access+1;

}

$modules = $jUpgrade->fixParams($modules);

echo $jUpgrade->insertObjectList($db_new, '#__modules', $modules);


?>

<?php
/**
 * jUpgrade
 *
 * @version		$Id$
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @author      Matias Aguirre <maguirre@matware.com.ar>
 * @link        http://www.matware.com.ar
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

define( '_JEXEC', 1 );
define( 'JPATH_BASE', dirname(__FILE__) );
define( 'DS', DIRECTORY_SEPARATOR );
require_once ( JPATH_BASE .DS.'defines.php' );
require_once ( JPATH_BASE .DS.'jupgrade.class.php' );

$jUpgrade = new jUpgrade();

## Truncate #__jupgrade_categories
$query = "TRUNCATE TABLE `#__jupgrade_categories`";
$jUpgrade->db_new->setQuery( $query );
$jUpgrade->db_new->query();

## Getting old values 
$query = "SELECT *"
." FROM {$jUpgrade->config['prefix']}sections"
." WHERE scope = 'content'"
." ORDER BY id ASC";
$jUpgrade->db_old->setQuery( $query );
$sections = $jUpgrade->db_old->loadObjectList();
//print_r($sections);

for($i=0;$i<count($sections);$i++) {
	//echo $sections[$i]->title . "<br>";

	$jUpgrade->insertCategory($sections[$i], false);
	$jUpgrade->insertAsset(false);

	/*
	 * CHILDREN CATEGORIES
	 */

	$query = "SELECT *"
	." FROM {$jUpgrade->config['prefix']}categories"
	." WHERE section = {$sections[$i]->id}"
	." ORDER BY id ASC";
	$jUpgrade->db_old->setQuery( $query );
	$categories = $jUpgrade->db_old->loadObjectList();

	for($y=0;$y<count($categories);$y++){

		//echo $categories[$y]->title."\n";

		$jUpgrade->insertCategory($categories[$y], $sections[$i]->title);
		$jUpgrade->insertAsset($sections[$i]->title);

	}

}

##
## Other categories
##
$query = "SELECT *"
." FROM {$jUpgrade->config['prefix']}categories"
." WHERE SUBSTRING(section, 1, 4) = 'com_'"
." ORDER BY id ASC";
$jUpgrade->db_old->setQuery( $query );
$categories = $jUpgrade->db_old->loadObjectList();

for($y=0;$y<count($categories);$y++){

	//echo $categories[$y]->title."\n";

	$jUpgrade->insertCategory($categories[$y], $sections[$i]->title);
	$jUpgrade->insertAsset($sections[$i]->title);

}



//sleep(1);
?>

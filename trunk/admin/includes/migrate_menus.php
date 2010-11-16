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

$db = &$jUpgrade->db_old;
$db_new = &$jUpgrade->db_new;
$config = &$jUpgrade->config;

##

$query = "SELECT `menutype`,`name` AS title,`alias`,`link`,`type`,"
." `published`,`parent` AS parent_id, `componentid` AS component_id,"
." `sublevel` AS level,`ordering`,`checked_out`,`checked_out_time`,`browserNav`,"
." `access`,`params`,`lft`,`rgt`,`home`"
." FROM {$config['prefix']}menu"
." ORDER BY id ASC";
$db->setQuery( $query );
$menu = $db->loadObjectList();
//echo $db->errorMsg();
//print_r($content[0]);

//echo count($menu);

echo $jUpgrade->insertObjectList($db_new, '#__menu', $menu);

$query = "SELECT *"
." FROM {$config['prefix']}menu_types"
." WHERE id > 1";
$db->setQuery( $query );
$menutypes = $db->loadObjectList();

echo $jUpgrade->insertObjectList($db_new, '#__menu_types', $menutypes);

?>

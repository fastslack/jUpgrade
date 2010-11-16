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

$query = "SELECT `bid` AS id,`cid`,`type`,`name`,`alias`, `imptotal` ,`impmade`, `clicks`, "
." `clickurl`, `checked_out`, `checked_out_time`, `showBanner` AS state,"
." `custombannercode`,`description`,`sticky`,
`ordering`,`publish_up`,`publish_down`, `params`"
." FROM {$config['prefix']}banner"
." ORDER BY bid ASC";

$db->setQuery( $query );
$banners = $db->loadObjectList();
//echo $db->errorMsg();

print_r($banners);

// `id`,`cid`,`type`,`name`,`alias`,`imptotal`,`impmade`,`clicks`,`clickurl`,`state`,`catid`,`description`,
//`custombannercode`,`sticky`,`ordering`,`metakey`,`params`,`own_prefix`,`metakey_prefix`,`purchase_type`,
//`track_clicks`,`track_impressions`,`checked_out`,`checked_out_time`,`publish_up`,`publish_down`,`reset`,`created`,`language`

for($i=0;$i<count($banners);$i++){
	$banner = &$banners[$i];
	//$banner->access = $banner->access+1;
	$banner->language = "*";

}

echo $db->getErrorMsg();
//echo $query;

echo $jUpgrade->insertObjectList($db_new, '#__banners', $banners);

?>

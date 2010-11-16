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

$query = "SELECT `id`, `title`, NULL AS `alias`, `title_alias`, `introtext`, `fulltext`, `state`, `sectionid`, `mask`, o.new AS catid, `created`, `created_by`, `created_by_alias`, `modified`, `modified_by`, `checked_out`, `checked_out_time`, `publish_up`, `publish_down`, `images`, `urls`, `attribs`, `version`, `parentid`, `ordering`, `metakey`, `metadesc`, `access`, `hits`, NULL"
." FROM " . $config['prefix'] . "content AS c"
." LEFT JOIN j16_jupgrade_categories AS o ON o.old = c.catid"
." ORDER BY id ASC";

$db->setQuery( $query );
$contents = $db->loadObjectList();
//print_r($content);

for($i=0;$i<count($contents);$i++){
	$content = &$contents[$i];
	$content->access = $content->access+1;
	$content->language = "*";

}

echo $db->getErrorMsg();
//echo $query;

echo $jUpgrade->insertObjectList($db_new, '#__content', $contents);

?>

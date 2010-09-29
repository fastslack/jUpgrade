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
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'methods.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'factory.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'import.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'error'.DS.'error.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'base'.DS.'object.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'database.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table.php' );
require_once ( JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'tablenested.php' );
require(JPATH_ROOT.DS."configuration.php");

/**
 * Banner table
 *
 * @package		Joomla.Framework
 * @subpackage	Table
 * @since		1.0
 */
class JTableBanner extends JTable
{
	/**
	* @param database A database connector object
	*/
	function __construct(&$db)
	{
		parent::__construct('#__banners', 'id', $db);
	}
}

$jconfig = new JConfig();
//print_r($jconfig);

$config = array();
$config['driver']   = 'mysql';
$config['host']     = $jconfig->host;
$config['user']     = $jconfig->user; 
$config['password'] = $jconfig->password;
$config['database'] = $jconfig->db;  
$config['prefix']   = $jconfig->dbprefix;
//print_r($config);

$config_new = $config;
$config_new['prefix'] = "j16_";

$db = JDatabase::getInstance( $config );
$db_new = JDatabase::getInstance( $config_new );
//print_r($db_new);
/*
$query = "SELECT `bid`,`cid`,`type`,`name`,`alias`,`imptotal`,`impmade`,`clicks`,`imageurl`,`clickurl`,`date`,
`showBanner`,`checked_out`,`checked_out_time`,`editor`,`custombannercode`,`catid`,`description`,`sticky`,
`ordering`,`publish_up`,`publish_down`,`tags`,`params`"
." FROM {$config['prefix']}banner"
." ORDER BY bid ASC";

$db->setQuery( $query );
$banners = $db->loadObjectList();
//echo $db->errorMsg();

//print_r($content[0]);

for($i=0;$i<count($banners);$i++) {
	//echo $sections[$i]->id . "<br>";

	$new = new JTableBanner($db_new);
	//print_r($new);
	//$new->id = $banners[$i]->bid;
	$new->cid = $banners[$i]->cid;
	$new->type = $banners[$i]->type;
	$new->name = $banners[$i]->name;
	$new->imptotal = $banners[$i]->imptotal;
	$new->impmade = $banners[$i]->impmade;
	$new->clicks = $banners[$i]->clicks;
	$new->clickurl = $banners[$i]->clickurl;
	$new->state = $banners[$i]->showBanner;
	$new->catid = $banners[$i]->catid;
	$new->description = $banners[$i]->description;
	$new->sticky = $banners[$i]->sticky;
	$new->ordering = $banners[$i]->ordering;
	$new->checked_out = $banners[$i]->checked_out;
	$new->checked_out_time = $banners[$i]->checked_out_time;
	$new->publish_up = $banners[$i]->publish_up;
	$new->publish_down = $banners[$i]->publish_down;
	//$new->setRules('{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}');
	//$new->store();
	//print_r($new);
}

*/
?>

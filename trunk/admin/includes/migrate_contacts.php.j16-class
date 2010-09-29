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
 * Contact table
 *
 * @package		Joomla.Framework
 * @subpackage	Table
 * @since		1.0
 */
class JTableContact extends JTable
{
	/**
	* @param database A database connector object
	*/
	function __construct(&$db)
	{
		parent::__construct('#__contact_details', 'id', $db);
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

$query = "SELECT *"
." FROM {$config['prefix']}contact_details"
." ORDER BY id ASC";

$db->setQuery( $query );
$contacts = $db->loadObjectList();
//echo $db->errorMsg();

//print_r($content[0]);
/*
for($i=0;$i<count($contacts);$i++) {
	//echo $sections[$i]->id . "<br>";
	$new = new JTableContact($db_new);
	print_r($new);
	$new->name = $contacts[$i]->name;
	$new->alias = $contacts[$i]->alias;
	$new->con_position = $contacts[$i]->con_position;
	$new->address = $contacts[$i]->address;
	$new->suburb = $contacts[$i]->suburb;
	$new->state = $contacts[$i]->state;
	$new->country = $contacts[$i]->country;
	$new->postcode = $contacts[$i]->postcode;
	$new->telephone = $contacts[$i]->telephone;
	$new->fax = $contacts[$i]->fax;
	$new->misc = $contacts[$i]->misc;
	$new->image = $contacts[$i]->image;
	$new->imagepos = $contacts[$i]->imagepos;
	$new->email_to = $contacts[$i]->email_to;
	$new->default_con = $contacts[$i]->default_con;
	$new->published = $contacts[$i]->published;
	$new->checked_out = $contacts[$i]->checked_out;
	$new->checked_out_time = $contacts[$i]->checked_out_time;
	$new->ordering = $contacts[$i]->ordering;
	$new->params = $contacts[$i]->params;
	$new->user_id = $contacts[$i]->user_id;
	$new->catid = $contacts[$i]->catid;
	$new->access = $contacts[$i]->access;
	$new->mobile = $contacts[$i]->mobile;
	$new->webpage = $contacts[$i]->webpage;
	//$new->setRules('{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}');
	$new->store();

}

sleep(1);
*/
?>

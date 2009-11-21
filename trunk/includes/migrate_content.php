<?php
define( '_JEXEC', 1 );
define( 'JPATH_BASE', dirname(__FILE__) );
define( 'DS', DIRECTORY_SEPARATOR );

require_once ( JPATH_BASE .DS.'defines.php' );
require_once ( JPATH_LIBRARIES .DS.'joomla'.DS.'methods.php' );
require_once ( JPATH_LIBRARIES .DS.'joomla'.DS.'factory.php' );
require_once ( JPATH_LIBRARIES .DS.'joomla'.DS.'import.php' );
require_once ( JPATH_LIBRARIES .DS.'joomla'.DS.'error'.DS.'error.php' );
require_once ( JPATH_LIBRARIES .DS.'joomla'.DS.'base'.DS.'object.php' );
require_once ( JPATH_LIBRARIES .DS.'joomla'.DS.'database'.DS.'database.php' );
require(JPATH_ROOT.DS."configuration.php");

$jconfig = new JConfig();

$config = array();
$config['driver']   = 'mysql';
$config['host']     = $jconfig->host;
$config['user']     = $jconfig->user; 
$config['password'] = $jconfig->password;
$config['database'] = $jconfig->db;  
$config['prefix']   = $jconfig->dbprefix;
//print_r($config);

$db = JDatabase::getInstance( $config );
$config['prefix']   = "j16_";
$db2 = JDatabase::getInstance( $config );
//print_r($db2);

// Migrating Users
$query = "SELECT `id`, `name`, `username`, `email`, `password`, `usertype`, `block`,"
		." `sendEmail`, `registerDate`, `lastvisitDate`, `activation`, `params`"
		." FROM " . $config['prefix'] . "users"
		." WHERE id != 62";

//echo $query;

$db->setQuery( $query );
$users = $db->loadObjectList();
//print_r($users);


//require_once ( JPATH_BASE .DS.'JoomlaTest-1.5/includes'.DS.'defines.php' );
//require_once ( JPATH_BASE .DS.'defines.php' );
//define('JPATH_BASE', JPATH_ROOT );
//require_once ( JPATH_ROOT .DS.'includes'.DS.'framework.php' );

/*
class mtwJFactory extends JFactory 
{

	function &_createConfig2($file, $type = 'PHP')
	{
		jimport('joomla.registry.registry');

		require_once $file;

		// Create the registry with a default namespace of config
		$registry = new JRegistry('config');

		// Create the JConfig object
		$config = new mtwJConfig();

		// Load the configuration values into the registry
		$registry->loadObject($config);
//print_r($registry);
		return $registry;
	}

}

$test = new mtwJFactory();
//print_r($test);
*/

//$file = JPATH_BASE.DS.'configuration.php';
//$instance = mtwJFactory::_createConfig2($file, $type);
//print_r($instance);
//$menu = &JApplication::getInstance();
//$mainframe =& JFactory::getApplication('site');
//print_r($menu);

//$file = JPATH_BASE.DS.'configuration.php';
//$instance = JFactory::_createConfig($file, "ext");

//print_r($instance);
//$db =& JFactory::getDBO();
//print_r($db);
//$me			= & JFactory::getUser();
//print_r($me);
//echo dirname(__FILE__);



//echo $_SERVER['url'] . "/configuration.php";

//print_r($_SERVER);

//$path = "/home/fastslack/www/JoomlaTest-1.5";

//require("{$path}/libraries/joomla/base/object.php");
//require("{$path}/libraries/joomla/database/database.php");
//require("{$path}/libraries/joomla/database/database/mysql.php");
//include("{$path}/libraries/joomla/factory.php");

//$db = new JFactory();

//print_r($dbs->get());









//$path = explode("administrator", $_SERVER['SCRIPT_FILENAME']); 
//print_r($path);
//require($path[0] . "configuration.php");

//$config = new JConfig();

//print_r($config);

//echo $config->host;
/*
print_r($_REQUEST);
//echo $_REQUEST['url'];

$con = mysql_connect($config->host, $config->user, $config->password);
if (!$con)  {
  die('Could not connect: ' . mysql_error());
}

mysql_select_db($config->db, $con);

$query = "UPDATE jos_joomclip_comments SET status = " .$_REQUEST['val']. " WHERE id = ". $_REQUEST['id'];

//echo $query;
mysql_query($query);

//mysql_query("INSERT INTO Persons (FirstName, LastName, Age) VALUES ('Glenn', 'Quagmire', '33')");

mysql_close($con);
*/
?>

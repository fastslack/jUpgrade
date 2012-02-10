<?php
/**
 * @version		    $Id: 
 * @package		    jUpgrade
 * @subpackage	  jUpgradeCli
 * @copyright			CopyRight 2011 Matware All rights reserved.
 * @author				Matias Aguirre
 * @email   			maguirre@matware.com.ar
 * @link					http://www.matware.com.ar/
 * @license				GNU/GPL http://www.gnu.org/licenses/gpl-3.0-standalone.html
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * @package		jUpgrade
 * @subpackage	jUpgradeCli
 */
class JUpgradeHelper
{
	/**
	 * populateDatabase
	 */
	function populateDatabase(& $db, $sqlfile, & $errors, $nexttask='mainconfig')
	{
		if( !($buffer = file_get_contents($sqlfile)) )
		{
			return -1;
		}

		$queries = JUpgradeHelper::splitSql($buffer);

		foreach ($queries as $query)
		{
			$query = trim($query);
			if ($query != '' && $query {0} != '#')
			{
				$db->setQuery($query);
				//echo $query .'<br />';
				echo ".";
				$db->query() or die($db->getErrorMsg());

				JUpgradeHelper::getDBErrors($errors, $db );
			}
		}

		return count($errors);
	}

	/**
	 * @param string
	 * @return array
	 */
	function splitSql($sql)
	{
		$sql = trim($sql);
		$sql = preg_replace('/\n\#[^\n]*/', '', "\n".$sql);
		$buffer = array ();
		$ret = array ();
		$in_string = false;

		for ($i = 0; $i < strlen($sql) - 1; $i ++) {
			if ($sql[$i] == ";" && !$in_string)
			{
				$ret[] = substr($sql, 0, $i);
				$sql = substr($sql, $i +1);
				$i = 0;
			}

			if ($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\")
			{
				$in_string = false;
			}
			elseif (!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset ($buffer[0]) || $buffer[0] != "\\"))
			{
				$in_string = $sql[$i];
			}
			if (isset ($buffer[1]))
			{
				$buffer[0] = $buffer[1];
			}
			$buffer[1] = $sql[$i];
		}

		if (!empty ($sql))
		{
			$ret[] = $sql;
		}
		return ($ret);
	}


	function & getDBO($driver, $host, $user, $password, $database, $prefix, $select = true)
	{
		static $db = null;

		if ( ! $db )
		{
			jimport('joomla.database.database');
			$options	= array ( 'driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => $database, 'prefix' => $prefix, 'select' => $select	);
			$db = & JDatabase::getInstance( $options );
		}

		return $db;
	}

	function getDBErrors( & $errors, $db )
	{
		if ($db->getErrorNum() > 0)
		{
			$errors[] = array('msg' => $db->getErrorMsg(), 'sql' => $db->_sql);
		}
	}
}

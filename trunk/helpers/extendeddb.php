<?php
/**
 * jUpgrade
 *
 * @author      Matias Aguirre
 * @email       maguirre@matware.com.ar
 * @url         http://www.matware.com.ar
 * @license     GNU/GPL
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * MySQL database driver extended
 *
 * @package		Joomla.Framework
 * @subpackage	Database
 * @since		1.0
 */
class JDatabaseExtended extends JDatabase
{
    /**
     * Inserts a list of rows into a table based on an objects properties
     *
     * @access  public
     * @param   string  The name of the table
     * @param   object  An object whose properties match table fields
     * @param   string  The name of the primary key. If provided the object property is updated.
     */
    function insertObjectList( $table, &$object, $keyName = NULL ) {

			$db =& JFactory::getDBO();
			$count = count($object);

			for ($i=0; $i<$count; $i++) {
				$db->insertObject($table, $object[$i]);
			}

			$ret = $db->getErrorNum();

      return $ret;
    }
}
?>

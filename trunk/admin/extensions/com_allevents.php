<?php
/**
 * @version		$Id: 
 * @package		AllEvents
 * @subpackage	com_allevents
 * @copyright	Copyright 2012 - 2012 Christophe Avonture. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 * @author		Christophe Avonture
 * @link		http://avonture.be/allevents/christophe-avonture
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * jUpgrade class for AllEvents migration
 *
 * This class migrates the AllEvents extension
 *
 * @since		1.1.0
 */
class jUpgradeComponentAllEvents extends jUpgradeExtensions {

   /**
    * Check if extension migration is supported.
    *
    * @return	boolean
    * @since	1.1.0
    */
   protected function detectExtension() { 
      // Detect if AllEvents is installed
      return file_exists(JPATH_ROOT.DS.'administrator/components/com_allevents/admin.allevents.php');
   }
   /**
    * Migrate tables
    *
    * @return	boolean
    * @since	1.1.0
    */
   public function migrateExtensionCustom() {
       return true;
   }
}


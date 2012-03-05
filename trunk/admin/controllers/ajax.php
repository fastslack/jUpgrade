<?php
/**
 * jUpgrade
 *
 * @version		$Id: ajax.php
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @copyright	Copyright 2006 - 2011 Matias Aguire. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @author		Matias Aguirre <maguirre@matware.com.ar>
 * @link		http://www.matware.com.ar
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Ajax Controller
 *
 * @package		MatWare
 * @subpackage	com_jupgrade
 */
class jupgradeControllerAjax extends JController
{
	/**
	 * Initial checks in jUpgrade
	 *
	 * @return	none
	 * @since	1.2.0
	 */
	function getParams()
	{
		require_once JPATH_COMPONENT_ADMINISTRATOR.'/includes/jupgrade.class.php';

		// Initialize jupgrade class
		$jupgrade = new jUpgrade;
		$object = $jupgrade->getParams();
		
		echo json_encode($object);
	}

	/**
	 * Deletes the previous migration folder
	 *
	 * @return	none
	 * @since	1.X
	 */
	function deletePreviousMigration()
	{
		$jupgrade = new jUpgrade;
		$params = $jupgrade->getParams();
		if (isset($params->directory) && strlen($params->directory) > 0) {
			$dir = JPATH_ROOT.DS.$params->directory;

			if (JFolder::exists($dir)) {
				JFolder::delete($dir);
			}
		}
	}

	/**
	 * Initial checks in jUpgrade
	 *
	 * @return	none
	 * @since	1.2.0
	 */
	function checks()
	{
		require_once JPATH_COMPONENT_ADMINISTRATOR.'/includes/jupgrade.class.php';

		// Initialize jupgrade class
		$jupgrade = new jUpgrade;
		
		// Requirements
		$requirements = $jupgrade->getRequirements();

		// Getting the component parameter with global settings
		$params = $jupgrade->getParams();

		// Checking tables
		$query = "SHOW TABLES";
		$jupgrade->db_new->setQuery($query);
		$tables = $jupgrade->db_new->loadResultArray();
		
		if (!in_array('jupgrade_categories', $tables)) {
			echo "401: jupgrade_categories table not exist";
			exit;
		}
		
		if (!in_array('jupgrade_menus', $tables)) {
			echo "402: jupgrade_menus table not exist";
			exit;
		}
		
		if (!in_array('jupgrade_modules', $tables)) {
			echo "403: jupgrade_modules table not exist";
			exit;
		}
		
		if (!in_array('jupgrade_steps', $tables)) {
			echo "404: jupgrade_steps table not exist";
			exit;
		}		

		/**
		 * Check if jupgrade_steps is fine
		 */
		$query = "SELECT COUNT(id) FROM `jupgrade_steps`";
		$jupgrade->db_new->setQuery($query);
		$nine = $jupgrade->db_new->loadResult();
		
		if ($nine < 10) {
			echo "405: jupgrade_steps is not valid";
			exit;
		}
		
		/**
		 * Check Curl
		 */
		$skip_download = isset($params->skip_download) ? $params->skip_download : 0;	
	
		if ($skip_download != 1) {
			$ext = get_loaded_extensions();
	
			if (!in_array("curl", $ext)) {
				echo "406: cURL not loaded";
				exit;
			}
		}
		
		/**
		 * Check dirs
		 */
		if (!is_writable(JPATH_ROOT)) {
			echo "407: ".JPATH_ROOT." is unwritable";
			exit;
		}
		
		$tmp = JPATH_ROOT.'/tmp';
		
		if (!is_writable($tmp)) {
			echo "408: {$tmp} is unwritable";
			exit;
		}
		
		/**
		 * Compare the PHP version
		 */
		if (!version_compare($requirements['phpMust'], $requirements['phpIs'], '<')) {
			echo "409: PHP 5.2+ or greater is required";
			exit;
		}
		
		/**
		 * Compare the MYSQL version
		 */
		if (!version_compare($requirements['mysqlMust'], $requirements['mysqlIs'])) {
			echo "410: MySQL 5.0+ or greater is required";
			exit;
		}

		/**
		 * Check safe_mode_gid
		 */
		if (@ini_get('safe_mode_gid')) {
			echo "411: You must to disable 'safe_mode_gid' on your php configuration";
			exit;
		}

		// Get original prefix for check
		$original_prefix = $jupgrade->getPrefix();
		// Get the prefix
		$prefix = $jupgrade->db_new->getPrefix();

		if ($original_prefix == $prefix) {
			echo "412: Your destination prefix is the same of the original site";
			exit;
		}

		echo 1;
		exit;
	}

	/**
	 * Cleanup
	 *
	 * @return	none
	 * @since	1.2.0
	 */
	function cleanup()
	{
		require_once JPATH_COMPONENT_ADMINISTRATOR.'/includes/jupgrade.class.php';

		/**
		 * Initialize jupgrade class
		 */
		$jupgrade = new jUpgrade;

		// Getting the component parameter with global settings
		$params = $jupgrade->getParams();

		// Get the prefix
		$prefix = $jupgrade->db_new->getPrefix();

		// Set all status to 0 and clear state
		$query = "UPDATE jupgrade_steps SET status = 0, state = ''";
		$jupgrade->db_new->setQuery($query);
		$jupgrade->db_new->query();

		// Convert the params to array
		$core_skips = (array) $params;

		// Skiping the steps setted by user
		foreach ($core_skips as $k => $v) {
			$core = substr($k, 0, 9);
			$name = substr($k, 10, 15);

			if ($core == "skip_core") {
				if ($v == 1) {
					// Set all status to 0 and clear state
					$query = "UPDATE jupgrade_steps SET status = 1 WHERE name = '{$name}'";
					$jupgrade->db_new->setQuery($query);
					$jupgrade->db_new->query();				
				}
			}
		}

		// Cleanup 3rd extensions
		$query = "DELETE FROM jupgrade_steps WHERE id > 10";
		$jupgrade->db_new->setQuery($query);
		$jupgrade->db_new->query();

		if ($jupgrade->canDrop) {
			// Get the tables
			$query = "SHOW TABLES LIKE '{$prefix}%'";
			$jupgrade->db_new->setQuery($query);
			$tables = $jupgrade->db_new->loadRowList();

			for($i=0;$i<count($tables);$i++) {
				$table = $tables[$i][0];
				$query = "DROP TABLE {$table}";
				$jupgrade->db_new->setQuery($query);
				$jupgrade->db_new->query();

				// Check for query error.
				$error = $jupgrade->db_new->getErrorMsg();

				if ($error) {
					throw new Exception($error);
				}
			}

			$tables = array();
			$tables[] = 'jupgrade_categories';
			$tables[] = 'jupgrade_menus';
			$tables[] = 'jupgrade_modules';

			for ($i=0;$i<count($tables);$i++) {
				// Truncate mapping tables
				$query = "TRUNCATE TABLE `{$tables[$i]}`";
				$jupgrade->db_new->setQuery($query);
				$jupgrade->db_new->query();
			}

			// Check for query error.
			$error = $jupgrade->db_new->getErrorMsg();

			if ($error) {
				throw new Exception($error);
			}

		} else {

			$query = "SHOW TABLES LIKE '{$prefix}%'";
			$jupgrade->db_new->setQuery($query);
			$tables = $jupgrade->db_new->loadRowList();

			$tables[][0] = 'jupgrade_categories';
			$tables[][0] = 'jupgrade_menus';
			$tables[][0] = 'jupgrade_modules';

			for ($i=0;$i<count($tables);$i++) {
				// Truncate mapping tables
				$query = "DELETE FROM `{$tables[$i][0]}`";
				$jupgrade->db_new->setQuery($query);
				$jupgrade->db_new->query();

				// Check for query error.
				$error = $jupgrade->db_new->getErrorMsg();

				if ($error) {
					throw new Exception($error);
				}
			}
		}

		// Insert needed value
		$query = "INSERT INTO `jupgrade_menus` ( `old`, `new`) VALUES ( 0, 0)";
		$jupgrade->db_new->setQuery($query);
		$jupgrade->db_new->query();

		// Check for query error.
		$error = $jupgrade->db_new->getErrorMsg();

		if ($error) {
			throw new Exception($error);
		}

		// Insert uncategorized id
		$query = "INSERT INTO `jupgrade_categories` (`old`, `new`) VALUES (0, 2)";
		$jupgrade->db_new->setQuery($query);
		$jupgrade->db_new->query();

		// Check for query error.
		$error = $jupgrade->db_new->getErrorMsg();

		if ($error) {
			throw new Exception($error);
		}

		/**
		 * Check if the previous migration should be deleteted
		 */
		//$params = $jupgrade->getParams();
		$delete_previous_migration = isset($params->delete_previous_migration) ? $params->delete_previous_migration : 0;
		if ($delete_previous_migration == 1) {
			$this->deletePreviousMigration();
		}

		echo 1;
		exit;
	}

	/**
	 * Get the file size
	 *
	 * @return	the filesize
	 * @since	1.2.0
	 */
	function getfilesize()
	{
		// Includes
		require_once JPATH_COMPONENT_ADMINISTRATOR.'/includes/jupgrade.class.php';
				
		// jUpgrade class
		$jupgrade = new jUpgrade;
		
		// Getting the component parameter with global settings
		$params = $jupgrade->getParams();
		
		// Define filenames
		$sizefile = JPATH_ROOT.'/tmp/size.tmp';
		
		// downloading Molajo instead Joomla zip
		if (isset($params->mode)) {
			if ($params->mode == 1) {
				$zipfile = JPATH_ROOT.'/tmp/joomla25.zip';
			}
			if ($params->mode == 2) {
				$zipfile = JPATH_ROOT.'/tmp/joomla17.zip';
			}
		}
		
		if (file_exists($zipfile)) {
		   $size = filesize($zipfile);
		}
		else {
			echo 212;
			exit;
		}
		
		if (file_exists($sizefile)) {
			$handle = fopen($sizefile, 'r');
			$total = trim(fread($handle, 18));
		}
		else {
			echo 121;
			exit;
		}
		
		$percent = $size / $total * 100;
		$percent = round($percent);
		
		echo "{$percent},{$size},{$total}";
		exit;
	}

	/**
	 * Decompress
	 *
	 * @return	none
	 * @since	1.2.0
	 */
	function decompress()
	{	
		// Includes
		require_once JPATH_COMPONENT_ADMINISTRATOR.'/includes/jupgrade.class.php';
		require_once JPATH_COMPONENT_ADMINISTRATOR.'/libraries/pclzip.lib.php';

		$directory = JRequest::getVar('directory');

		// jUpgrade class
		$jupgrade = new jUpgrade;
		
		// Getting the component parameter with global settings
		$params = $jupgrade->getParams();
		
		// downloading Molajo instead Joomla zip
		if (isset($params->mode)) {
			if ($params->mode == 1) {
				$zipfile = JPATH_ROOT.'/tmp/joomla25.zip';
			}
			if ($params->mode == 2) {
				$zipfile = JPATH_ROOT.'/tmp/joomla17.zip';
			}
		}		

		$dir = JPATH_ROOT.DS.$directory;
		
		if (file_exists($zipfile)) {
			$archive = new PclZip($zipfile);
		
			if ($archive->extract(PCLZIP_OPT_PATH, $dir) == 0) {
				die("Error : ".$archive->errorInfo(true));
			}
			echo 1;
			exit;
		}
	}

	/**
	 * Download Joomla package from server
	 *
	 * @return	none
	 * @since	1.2.0
	 */
	function download()
	{
		// Includes
		require_once JPATH_COMPONENT_ADMINISTRATOR.'/includes/jupgrade.class.php';
		
		// jUpgrade class
		$jupgrade = new jUpgrade;
		
		// Getting the component parameter with global settings
		$params = $jupgrade->getParams();
		
		// Define filenames
		$sizefile = JPATH_ROOT.'/tmp/size.tmp';
		$joomla17file = JPATH_ROOT.'/tmp/joomla17.zip';
		$joomla25file = JPATH_ROOT.'/tmp/joomla25.zip';
		
		// Cleanup
		if (file_exists($sizefile)) {
			unlink($sizefile);
		}
		if (file_exists($joomla17file)) {
			unlink($joomla17file);
		}
		if (file_exists($joomla25file)) {
			unlink($joomla25file);
		}
		
		// downloading Molajo instead Joomla zip
		if ($params->mode == 1) {
			$url = "http://anonymous:@joomlacode.org/svn/joomla/development/branches/jupgrade/pack/joomla25.zip";
			$zipfile = JPATH_ROOT.'/tmp/joomla25.zip';
		}else if ($params->mode == 2) {
			$url = "http://anonymous:@joomlacode.org/svn/joomla/development/branches/jupgrade/pack/joomla17.zip";
			$zipfile = JPATH_ROOT.'/tmp/joomla17.zip';
		}
		
		/*
			Getting the size of the zip
		 */
		$chGetSize = curl_init();
		
		// Set a valid user agent
		curl_setopt($chGetSize, CURLOPT_URL, $url);
		curl_setopt($chGetSize, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($chGetSize, CURLOPT_HEADER, false);
		// Don’t download the body content
		curl_setopt($chGetSize, CURLOPT_NOBODY, true);
		
		// Run the curl functions to process the request
		$chGetSizeStore = curl_exec($chGetSize);
		$chGetSizeError = curl_error($chGetSize);
		$chGetSizeInfo = curl_getinfo($chGetSize);
		// Close the connection
		curl_close($chGetSize);// Print the file size in bytes
		// Debug
		// print_r($chGetSizeInfo);
		
		$length = $chGetSizeInfo['download_content_length'];
		
		// Open file to write
		$size = fopen($sizefile, 'wb');
		if ($size == FALSE){
			print "File not opened<br>";
			exit;
		}
		
		// Write and close the file
		fwrite($size, $length);
		fclose($size);
		
		/*
			Getting the zip
		 */
		$out = fopen($zipfile, 'wb');
		if ($out == FALSE){
			print "File not opened<br>";
			exit;
		}
		
		// Create a curl connection
		$chGetFile = curl_init();
		curl_setopt($chGetFile, CURLOPT_URL, $url);
		curl_setopt($chGetFile, CURLOPT_TIMEOUT, 250);
		curl_setopt($chGetFile, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($chGetFile, CURLOPT_HEADER, false);
		curl_setopt($chGetFile, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($chGetFile, CURLOPT_FILE, $out);
		
		// Run the curl functions to process the request
		$chGetFileStore = curl_exec($chGetFile);
		$chGetFileError = curl_error($chGetFile);
		$chGetFileInfo = curl_getinfo($chGetFile);
		// Write and close the file
		curl_close($chGetFile);
		fclose($out);
		
		if (file_exists($zipfile)) {
			echo 1;
			exit;
		}
		else {
			echo 0;
			exit;
		}
	}

	/**
	 * Done
	 *
	 * @return	none
	 * @since	1.2.0
	 */
	function done()
	{
		jimport('joomla.filesystem.folder');

		$directory = JRequest::getVar('directory');

		$olddir = JPATH_ROOT.DS.$directory.DS.'installation';
		$dir = JPATH_ROOT.DS.$directory.DS.'installation-old';

		if (is_dir($dir)) {
			JFolder::delete($dir);
		}

		if (JFolder::move($olddir, $dir)) {
			echo 1;
			exit;
		}	else {
			echo 0;
			exit;
		}
	}


}

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
	 * Initial checks in jUpgrade
	 *
	 * @return	none
	 * @since	1.2.0
	 */
	function checks()
	{
		require_once JPATH_COMPONENT_ADMINISTRATOR.'/includes/jupgrade.class.php';
		
		/**
		 * Initialize jupgrade class
		 */
		$jupgrade = new jUpgrade;
		
		/**
		 * Requirements
		 */
		$requirements = $jupgrade->getRequirements();
		
		/**
		 * Checking tables
		 */
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
		$ext = get_loaded_extensions();
		
		if (!in_array("curl", $ext)) {
			echo "406: cURL not loaded";
			exit;
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
		echo "OK";
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
		
		// Drop all j16_tables
		$query = "DROP TABLE `j16_assets`, `j16_banners`, `j16_banner_clients`, `j16_banner_tracks`, `j16_categories`, `j16_contact_details`, `j16_content`, `j16_content_frontpage`, `j16_content_rating`, `j16_core_log_searches`, `j16_extensions`,  `j16_languages`, `j16_menu`, `j16_menu_types`, `j16_messages`, `j16_messages_cfg`, `j16_modules`, `j16_modules_menu`, `j16_newsfeeds`, `j16_redirect_links`, `j16_schemas`, `j16_session`, `j16_template_styles`, `j16_updates`, `j16_update_categories`, `j16_update_sites`, `j16_update_sites_extensions`, `j16_usergroups`, `j16_users`, `j16_user_profiles`, `j16_user_usergroup_map`, `j16_viewlevels`, `j16_weblinks`";
		$jupgrade->db_new->setQuery($query);
		$jupgrade->db_new->query();

		// Truncate mapping tables
		$query = "TRUNCATE TABLE `jupgrade_categories`, `jupgrade_menus`, `jupgrade_modules`";
		$jupgrade->db_new->setQuery($query);
		$jupgrade->db_new->query();

		// Set all status to 0 and clear state
		$query = "UPDATE jupgrade_steps SET status = 0, state = ''";
		$jupgrade->db_new->setQuery($query);
		$jupgrade->db_new->query();

		// Cleanup 3rd extensions
		$query = "DELETE FROM jupgrade_steps WHERE id > 10";
		$jupgrade->db_new->setQuery($query);
		$jupgrade->db_new->query();

		// Check for query error.
		$error = $jupgrade->db_new->getErrorMsg();
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
		$zipfile = JPATH_ROOT.'/tmp/joomla16.zip';
		
		// downloading Molajo instead Joomla zip
		if (isset($params->mode)) {
			if ($params->mode == 1) {
				$zipfile = JPATH_ROOT.'/tmp/joomla17.zip';
			}
			if ($params->mode == 2) {
				$zipfile = JPATH_ROOT.'/tmp/molajo16.zip';
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
		
		$zipfile = JPATH_ROOT.'/tmp/joomla16.zip';
		
		// downloading Molajo instead Joomla zip
		if (isset($params->mode)) {
			if ($params->mode == 1) {
				$zipfile = JPATH_ROOT.'/tmp/joomla17.zip';
			}
			if ($params->mode == 2) {
				$zipfile = JPATH_ROOT.'/tmp/molajo16.zip';
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
		$molajofile = JPATH_ROOT.'/tmp/molajo16.zip';
		$joomla16file = JPATH_ROOT.'/tmp/joomla16.zip';
		$joomla17file = JPATH_ROOT.'/tmp/joomla17.zip';
		
		// Cleanup
		if (file_exists($sizefile)) {
			unlink($sizefile);
		}
		if (file_exists($molajofile)) {
			unlink($molajofile);
		}
		if (file_exists($joomla16file)) {
			unlink($joomla16file);
		}
		if (file_exists($joomla17file)) {
			unlink($joomla17file);
		}
		
		// Setting names
		$url = "http://anonymous:@joomlacode.org/svn/joomla/development/branches/jupgrade/pack/joomla16.zip";
		$zipfile = JPATH_ROOT.'/tmp/joomla16.zip';
		
		// downloading Molajo instead Joomla zip
		if ($params->mode == 1) {
			$url = "http://anonymous:@joomlacode.org/svn/joomla/development/branches/jupgrade/pack/joomla17.zip";
			$zipfile = JPATH_ROOT.'/tmp/joomla17.zip';
		}else if ($params->mode == 2) {
			$url = "http://anonymous:@joomlacode.org/svn/joomla/development/branches/jupgrade/pack/molajo16.zip";
			$zipfile = JPATH_ROOT.'/tmp/molajo16.zip';
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

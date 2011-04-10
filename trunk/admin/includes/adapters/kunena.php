<?php
/**
 * jUpgrade
 *
 * @version		$Id: kunena.php 
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @copyright	Copyright 2006 - 2011 Matias Aguire. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @author		Matias Aguirre <maguirre@matware.com.ar>
 * @link		http://www.matware.com.ar
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * jUpgrade class for Kunena migration
 *
 * This class migrates the Kunena extension
 *
 * @since		1.1
 */
class jUpgradeExtensionsKunena extends jUpgrade
{
	/**
	 * Migrate tables
	 *
	 * @return	boolean
	 * @since	0.4.
	 */
	public function migrateTables()
	{

		$this->copyTable('#__kunena_announcement', 'j16_kunena_announcement');
		$this->copyTable('#__kunena_attachments', 'j16_kunena_attachments');
		$this->copyTable('#__kunena_attachments_bak', 'j16_kunena_attachments_bak');
		$this->copyTable('#__kunena_categories', 'j16_kunena_categories');
		$this->copyTable('#__kunena_config', 'j16_kunena_config');
		$this->copyTable('#__kunena_config_backup 	Browse', 'j16_kunena_config_backup 	Browse');
		$this->copyTable('#__kunena_favorites', 'j16_kunena_favorites');
		$this->copyTable('#__kunena_groups', 'j16_kunena_groups');
		$this->copyTable('#__kunena_messages', 'j16_kunena_messages');
		$this->copyTable('#__kunena_messages_text', 'j16_kunena_messages_text');
		$this->copyTable('#__kunena_moderation', 'j16_kunena_moderation');
		$this->copyTable('#__kunena_polls', 'j16_kunena_polls');
		$this->copyTable('#__kunena_polls_options', 'j16_kunena_polls_options');
		$this->copyTable('#__kunena_polls_users', 'j16_kunena_polls_users');
		$this->copyTable('#__kunena_ranks', 'j16_kunena_ranks');
		$this->copyTable('#__kunena_sessions', 'j16_kunena_sessions');
		$this->copyTable('#__kunena_smileys', 'j16_kunena_smileys');
		$this->copyTable('#__kunena_subscriptions', 'j16_kunena_subscriptions');
		$this->copyTable('#__kunena_subscriptions_categories', 'j16_kunena_subscriptions_categories');
		$this->copyTable('#__kunena_thankyou', 'j16_kunena_thankyou');
		$this->copyTable('#__kunena_users', 'j16_kunena_users');
		$this->copyTable('#__kunena_users_banned', 'j16_kunena_users_banned');
		$this->copyTable('#__kunena_version', 'j16_kunena_version');
		$this->copyTable('#__kunena_whoisonline', 'j16_kunena_whoisonline');

		return true;
	}

	/**
	 * The public entry point for the class.
	 *
	 * @return	boolean
	 * @since	0.4.
	 */
	public function upgrade()
	{
		try
		{
			$this->migrateTables();
		}
		catch (Exception $e)
		{
			echo JError::raiseError(500, $e->getMessage());

			return false;
		}

		return true;
	}
}

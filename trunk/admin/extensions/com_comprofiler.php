<?php
/**
 * jUpgrade
 *
 * @version		$Id:
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @copyright	(C) 2008-2012 Joomlapolis
 * @license		GNU/GPL (http://www.gnu.org/licenses/gpl.html)
 * @author		Kyle (aka Krileon) <krileon@joomlapolis.com>
 * @link		http://www.joomlapolis.com
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * jUpgrade class for Comprofiler migration
 *
 * This class migrates the Comprofiler extension
 *
 * @since		1.1.0
 */
class jUpgradeComponentComprofiler extends jUpgradeExtensions {

	/**
	 * Check if extension migration is supported
	 *
	 * @return	boolean
	 * @since	1.1.0
	 */
	protected function detectExtension() {
		if ( ! file_exists( $this->getJRoot() . '/administrator/components/com_comprofiler/plugin.foundation.php' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Return old Joomla root
	 *
	 * @return string
	 */
	protected function getJRoot() {
		return str_replace( DS . 'administrator' . DS . 'components' . DS . 'com_jupgrade' . DS . 'extensions', '', dirname(__FILE__) );
	}

	/**
	 * Get folders to be migrated.
	 *
	 * @return	array	List of tables without prefix
	 * @since	1.1.0
	 */
	protected function getCopyFolders() {
		$folders		=	array(	'administrator/components/com_comprofiler',
									'components/com_comprofiler',
									'images/comprofiler'
								);

		if ( file_exists( $this->getJRoot() . '/modules/mod_cblogin' ) ) {
			$folders[]	=	'modules/mod_cblogin';
		}

		if ( file_exists( $this->getJRoot() . '/modules/mod_comprofilermoderator' ) ) {
			$folders[]	=	'modules/mod_comprofilermoderator';
		}

		if ( file_exists( $this->getJRoot() . '/modules/mod_comprofileronline' ) ) {
			$folders[]	=	'modules/mod_comprofileronline';
		}

		return $folders;
	}

	/**
	 * Get tables to be migrated
	 *
	 * @return	array	List of tables without prefix
	 * @since	1.1.0
	 */
	protected function getCopyTables() {
		$db											=	$this->db_old;

		$query										=	'SELECT *'
													.	"\n FROM " . $db->NameQuote( '#__comprofiler_plugin' );
		$db->setQuery( $query );
		$plugins									=	$db->loadObjectList();

		$tables										=	array(	'comprofiler',
																'comprofiler_fields',
																'comprofiler_field_values',
																'comprofiler_lists',
																'comprofiler_members',
																'comprofiler_plugin',
																'comprofiler_sessions',
																'comprofiler_tabs',
																'comprofiler_userreports',
																'comprofiler_views'
															);

		if ( $plugins ) foreach ( $plugins as $plugin ) {
			$xmlPath								=	$this->getJRoot() . '/components/com_comprofiler/plugin/' . $plugin->type . '/' . $plugin->folder . '/' . $plugin->element . '.xml';

			if ( file_exists( $xmlPath ) ) {
				$pluginXml							=	simplexml_load_file( $xmlPath );
				$plugin_databases					=	$pluginXml->children();

				if ( count( $plugin_databases ) > 0 ) foreach ( $plugin_databases as $plugin_database ) {
					if ( $plugin_database->getName() == 'database' ) {
						$plugin_tables				=	$plugin_database->children();

						if ( count( $plugin_tables ) > 0 ) foreach ( $plugin_tables as $plugin_table ) {
							$table_attributes		=	$plugin_table->attributes();

							if ( count( $table_attributes ) > 0 ) foreach ( $table_attributes as $table_attribute ) {
								if ( $table_attribute->getName() == 'name' ) {
									$table_name		=	(string) str_replace( '#__', '', $table_attribute );

									if ( $table_name && ( ! in_array( $table_name, $tables ) ) ) {
										$tables[]	=	$table_name;
									}
								}
							}
						}
					}
				}
			}
		}

		return $tables;
	}

	/**
	 * Fix usergroup mapping for all of CBs tables
	 *
	 * You can create custom copy functions for all your tables.
	 *
	 * If you want to copy your table in many smaller chunks,
	 * please store your custom state variables into $this->state and return false.
	 * Returning false will force jUpgrade to call this function again,
	 * which allows you to continue import by reading $this->state before continuing.
	 *
	 * @return	boolean Ready (true/false)
	 * @since	1.1.0
	 * @throws	Exception
	 */
	protected function copyTable( $table ) {
		$this->destination						=	$table;
		$this->source							=	$this->destination;

		$this->cloneTable( $this->source, $this->destination );

		$rows									=	$this->getSourceData( '*' );

		if ( $rows ) foreach ( $rows as &$row ) {
			// Generic:
			if ( isset( $row['access'] ) ) {
				$access_old						=	explode( '|*|', $row['access'] );
				$access_new						=	array();

				if ( $access_old ) foreach ( $access_old as $accessid_old ) {
					if ( $accessid_old > 0 ) {
						$accessid_new			=	$this->mapUserGroup( (int) $accessid_old );

						if ( ! in_array( $accessid_new, $access_new ) ) {
							$access_new[]		=	$accessid_new;
						}
					} else {
						if ( ! in_array( $accessid_old, $access_new ) ) {
							$access_new[]		=	$accessid_old;
						}
					}
				}

				$row['access']					=	implode( '|*|', $access_new );
			}

			// Userlists and Tabs:
			if ( isset( $row['useraccessgroupid'] ) && ( $row['useraccessgroupid'] > 0 ) ) {
				$row['useraccessgroupid']		=	$this->mapUserGroup( (int) $row['useraccessgroupid'] );
			}

			// Userlists:
			if ( isset( $row['usergroupids'] ) ) {
				$usergroupids_old				=	explode( ',', $row['usergroupids'] );
				$usergroupids_new				=	array();

				if ( $usergroupids_old ) foreach ( $usergroupids_old as $usergroupid_old ) {
					if ( $usergroupid_old > 0 ) {
						$usergroupid_new		=	$this->mapUserGroup( (int) trim( $usergroupid_old ) );

						if ( ! in_array( $usergroupid_new, $usergroupids_new ) ) {
							$usergroupids_new[]	=	$usergroupid_new;
						}
					} else {
						if ( ! in_array( $usergroupid_old, $usergroupids_new ) ) {
							$usergroupids_new[]	=	$usergroupid_old;
						}
					}
				}

				$row['usergroupids']			=	implode( ', ', $usergroupids_new );
			}

			// CBSubs:
			if ( isset( $row['usergroup'] ) && ( $row['usergroup'] > 0 ) ) {
				$row['usergroup']				=	$this->mapUserGroup( (int) $row['usergroup'] );
			}

			// CBSubs:
			if ( isset( $row['usergroups'] ) ) {
				$usergroups_old					=	explode( '|*|', $row['usergroups'] );
				$usergroups_new					=	array();

				if ( $usergroups_old ) foreach ( $usergroups_old as $usergroup_old ) {
					if ( $usergroup_old > 0 ) {
						$usergroup_new			=	$this->mapUserGroup( (int) $usergroup_old );

						if ( ! in_array( $usergroup_new, $usergroups_new ) ) {
							$usergroups_new[]	=	$usergroup_new;
						}
					} else {
						if ( ! in_array( $usergroup_old, $usergroups_new ) ) {
							$usergroups_new[]	=	$usergroup_old;
						}
					}
				}

				$row['usergroups']				=	implode( '|*|', $usergroups_new );
			}
		}

		$this->setDestinationData( $rows );

		return true;
	}

	/**
	 * Migrate tables
	 *
	 * @return	boolean
	 * @since	1.1.0
	 */
	public function migrateExtensionCustom() {
		$db						=	$this->db_new;
		$option					=	$this->name;

		JFactory::getApplication( 'administrator' );

		$component				=	JTable::getInstance( 'extension', 'JTable', array( 'dbo' => $db ) );

		$component->load( array( 'type' => 'component', 'element' => $option ) );

		$query					=	'UPDATE ' . $db->NameQuote( '#__menu' )
								.	"\n SET " . $db->NameQuote( 'component_id' ) . " = " . (int) $component->extension_id
								.	"\n WHERE " . $db->NameQuote( 'type' ) . " = " . $db->Quote( 'component' )
								.	"\n AND " . $db->NameQuote( 'link' ) . " LIKE " . $db->Quote( '%' . $db->getEscaped( $option, true ) . '%', false );
		$db->setQuery( $query );
		$db->query();

		if ( file_exists( JPATH_ROOT . '/components/com_comprofiler/comprofiler.xml' ) ) {
			@unlink( JPATH_ROOT . '/components/com_comprofiler/comprofiler.xml' );
		}

		$component->client_id	=	1;
		$component->state		=	-1;

		$component->store();

		jimport( 'joomla.installer.installer' );

		$installer				=	JInstaller::getInstance();

		$installer->discover_install( (int) $component->extension_id );

		return true;
	}
}

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
	 * Get tables to be migrated
	 *
	 * @return	array	List of tables without prefix
	 * @since	1.1.0
	 */
	protected function getCopyTables() {
		$db											=	$this->db_old;

		// Get CB Plugins:
		$query										=	'SELECT *'
													.	"\n FROM " . $db->NameQuote( '#__comprofiler_plugin' );
		$db->setQuery( $query );
		$plugins									=	$db->loadObjectList();

		// CB Core Tables:
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

		// CB Plugin Tables:
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
		$db			=	$this->db_new;
		$option		=	$this->name;

		JFactory::getApplication( 'administrator' );
		jimport( 'joomla.installer.installer' );

		// Remove J1.5 XML in root:
		if ( file_exists( JPATH_SITE . '/components/com_comprofiler/comprofiler.xml' ) ) {
			@unlink( JPATH_SITE . '/components/com_comprofiler/comprofiler.xml' );
		}

		// Get CB component object:
		$component	=	$this->discoverExtension( 'component', $option, 1 );

		// Fix CB menu links:
		$query		=	'UPDATE ' . $db->NameQuote( '#__menu' )
					.	"\n SET " . $db->NameQuote( 'component_id' ) . " = " . (int) $component->extension_id
					.	"\n WHERE " . $db->NameQuote( 'type' ) . " = " . $db->Quote( 'component' )
					.	"\n AND " . $db->NameQuote( 'link' ) . " LIKE " . $db->Quote( '%' . $db->getEscaped( $option, true ) . '%', false );
		$db->setQuery( $query );
		$db->query();

		// CB Core Modules:
		if ( file_exists( JPATH_SITE . '/modules/mod_cblogin' ) ) {
			$this->migrateXML( JPATH_SITE . '/modules/mod_cblogin/mod_cblogin.xml' );
			$this->discoverExtension( 'module', 'mod_cblogin' );
		}

		if ( file_exists( JPATH_SITE . '/modules/mod_comprofilermoderator' ) ) {
			$this->migrateXML( JPATH_SITE . '/modules/mod_comprofilermoderator/mod_comprofilermoderator.xml' );
			$this->discoverExtension( 'module', 'mod_comprofilermoderator' );
		}

		if ( file_exists( JPATH_SITE . '/modules/mod_comprofileronline' ) ) {
			$this->migrateXML( JPATH_SITE . '/modules/mod_comprofileronline/mod_comprofileronline.xml' );
			$this->discoverExtension( 'module', 'mod_comprofileronline' );
		}

		// CB Content:
		if ( file_exists( JPATH_SITE . '/modules/mod_cbcontent' ) ) {
			$this->migrateXML( JPATH_SITE . '/modules/mod_cbcontent/mod_cbcontent.xml' );
			$this->discoverExtension( 'module', 'mod_cbcontent' );
		}

		// CB GroupJive:
		if ( file_exists( JPATH_SITE . '/modules/mod_cbgroupjive' ) ) {
			$this->migrateXML( JPATH_SITE . '/modules/mod_cbgroupjive/mod_cbgroupjive.xml' );
			$this->discoverExtension( 'module', 'mod_cbgroupjive' );
		}

		// CB ProfileBook:
		if ( file_exists( JPATH_SITE . '/modules/mod_cblatestposts' ) ) {
			$this->migrateXML( JPATH_SITE . '/modules/mod_cblatestposts/mod_cblatestposts.xml' );
			$this->discoverExtension( 'module', 'mod_cblatestposts' );
		}

		// CB ProfileGallery:
		if ( file_exists( JPATH_SITE . '/modules/mod_cbgallery' ) ) {
			$this->migrateXML( JPATH_SITE . '/modules/mod_cbgallery/mod_cbgallery.xml' );
			$this->discoverExtension( 'module', 'mod_cbgallery' );
		}

		// CB Admin Nav:
		if ( file_exists( JPATH_SITE . '/administrator/modules/mod_cb_adminnav' ) ) {
			$this->migrateXML( JPATH_SITE . '/administrator/modules/mod_cb_adminnav/mod_cb_adminnav.xml' );
			$this->discoverExtension( 'module', 'mod_cb_adminnav', 1 );
		}

		// CBSubs:
		if ( file_exists( JPATH_SITE . '/modules/mod_cbsubscriptions' ) ) {
			$this->migrateXML( JPATH_SITE . '/modules/mod_cbsubscriptions/mod_cbsubscriptions.xml' );
			$this->discoverExtension( 'module', 'mod_cbsubscriptions' );
		}

		// CBSubs Content Bot:
		if ( file_exists( JPATH_ROOT . '/plugins/system/cbpaidsubsbot.xml' ) ) {
			$this->migratePlugin( 'system', 'cbpaidsubsbot' );
		}

		// CB Content Bot:
		if ( file_exists( JPATH_ROOT . '/plugins/content/cbcontentbot.xml' ) ) {
			$this->migratePlugin( 'content', 'cbcontentbot' );
		}

		return true;
	}

	private function migratePlugin( $type, $plugin ) {
		if ( $type && $plugin && file_exists( JPATH_ROOT . "/plugins/$type/$plugin.xml" ) ) {
			$oldmask	=	@umask( 0 );

			if ( @mkdir( JPATH_SITE . "/plugins/$type/$plugin", 0755, true ) ) {
				@umask( $oldmask );

				@copy( JPATH_ROOT . "/plugins/$type/index.html", JPATH_SITE . "/plugins/$type/$plugin/index.html" );
				@chmod( JPATH_SITE . "/plugins/$type/$plugin/index.html", 0644 );

				@copy( JPATH_ROOT . "/plugins/$type/$plugin.php", JPATH_SITE . "/plugins/$type/$plugin/$plugin.php" );
				@chmod( JPATH_SITE . "/plugins/$type/$plugin/$plugin.php", 0644 );

				@copy( JPATH_ROOT . "/plugins/$type/$plugin.xml", JPATH_SITE . "/plugins/$type/$plugin/$plugin.xml" );
				@chmod( JPATH_SITE . "/plugins/$type/$plugin/$plugin.xml", 0644 );

				if ( file_exists( JPATH_SITE . "/plugins/$type/$plugin" ) ) {
					$this->migrateXML( JPATH_SITE . "/plugins/$type/$plugin/$plugin.xml" );
					$this->discoverExtension( 'plugin', $plugin );
				}
			} else {
				@umask( $oldmask );
			}
		}
	}

	private function migrateXML( $path ) {
		if ( $path && file_exists( $path ) ) {
			$xml				=	file_get_contents( $path );

			if ( $xml ) {
				if ( preg_match( '%(</?.*)(?:mosinstall)(.*>)%', $xml ) ) {
					$xml		=	preg_replace( '%(</?.*)(?:mosinstall)(.*>)%', '\1install\2', $xml );
				}

				if ( preg_match( '%(</?)(?:params)(>)%', $xml ) && ( ! preg_match( '%(</?)(?:config)(>)%', $xml ) ) ) {
					$xml		=	preg_replace( '%(<)(?:params)(>)%', '\1config\2 <fields name="params"> <fieldset name="basic">', $xml );
					$xml		=	preg_replace( '%(</)(?:params)(>)%', '</fieldset> </fields> \1config\2', $xml );
					$xml		=	preg_replace( '%(</?)(?:param)(.*>)%', '\1field\2', $xml );
					$xml		=	preg_replace( '%<field.*type="spacer".*default="([^"]+)".*/>%', '<field name="" type="spacer" default="" label="\1" description="" />', $xml );
					$xml		=	preg_replace( '/type="textarea"/', 'type="textarea" filter="raw"', $xml );
				}

				if ( preg_match( '/<install .*client="[\w.]+"/', $xml ) ) {
					$xml		=	preg_replace( '/(<install.*version=)"[\w.]+"/', '\1"1.7"', $xml );
				} else {
					if ( preg_match( '/administrator/', $path ) ) {
						$client	=	'administrator';
					} else {
						$client	=	'site';
					}

					$xml		=	preg_replace( '/(<install.*version=)"[\w.]+"/', '\1"1.7" client="' . $client . '"', $xml );
				}

				file_put_contents( $path, $xml );
			}
		}
	}

	private function discoverExtension( $type, $element, $client = 0 ) {
		if ( $type && $element ) {
			$extension					=	JTable::getInstance( 'extension', 'JTable', array( 'dbo' => $this->db_new ) );

			$extension->load( array( 'type' => $type, 'element' => $element ) );

			if ( $extension->extension_id ) {
				$extension->client_id	=	(int) $client;
				$extension->state		=	-1;

				$extension->store();

				$installer				=	JInstaller::getInstance();

				$installer->discover_install( (int) $extension->extension_id );
			}

			return $extension;
		}
	}
}

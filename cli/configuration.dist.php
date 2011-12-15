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

// Prevent direct access to this file outside of a calling application.
defined('_JEXEC') or die;

/**
* jUpgradeCli configuration class.
*
* @package jUpgrade
* @since 2.5
*/
final class JConfig
{
	/**
	* Cli flag
	*
	* @var string
	* @since 2.5
	*/
	public $cli = 1;
	/**
	* The database driver.
	*
	* @var string
	* @since 2.5
	*/
	public $dbtype = 'mysql';

	/**
	* Database host.
	*
	* @var string
	* @since 2.5
	*/
	public $host = 'localhost';

	/**
	* The database connection user.
	*
	* @var string
	* @since 11.3
	*/
	public $user = '';

	/**
	* The database connection password.
	*
	* @var string
	* @since 2.5
	*/
	public $password = '';

	/**
	* The database name.
	*
	* @var string
	* @since 2.5
	*/
	public $db = '';

	/**
	* The database table prefix, if necessary.
	*
	* @var string
	* @since 11.3
	*/
	public $dbprefix = 'jos_';

	/**
	* The database table prefix, if necessary.
	*
	* @var string
	* @since 11.3
	*/
	public $prefix_new = 'j17_';

	/**
	* The timelimit
	*
	* @var string
	* @since 2.5
	*/
	public $timelimit = 0;

	/**
	* The error reporting
	*
	* @var string
	* @since 2.5
	*/
	public $error_reporting = 0;

	/**
	* The positions
	*
	* @var string
	* @since 2.5
	*/
	public $positions = 0;

}

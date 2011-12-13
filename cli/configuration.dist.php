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
* CLI configuration class.
*
* @package Joomla.Examples
* @since 11.3
*/
final class JConfig
{
/**
* The database driver.
*
* @var string
* @since 11.3
*/
public $dbDriver = 'mysqli';

/**
* Database host.
*
* @var string
* @since 11.3
*/
public $dbHost = 'localhost';

/**
* The database connection user.
*
* @var string
* @since 11.3
*/
public $dbUser = 'user';

/**
* The database connection password.
*
* @var string
* @since 11.3
*/
public $dbPass = 'user';

/**
* The database name.
*
* @var string
* @since 11.3
*/
public $dbName = 'mysql';

/**
* The database table prefix, if necessary.
*
* @var string
* @since 11.3
*/
public $dbPrefix = '';
}

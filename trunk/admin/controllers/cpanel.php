<?php
/**
 * jUpgrade
 *
 * @author      Matias Aguirre
 * @email       maguirre@matware.com.ar
 * @url         http://www.matware.com.ar
 * @license     GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Hello Hello Controller
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
class jupgradeControllerCpanel extends jupgradeController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		//$this->registerTask( 'add'  , 	'edit' );
	}

    function display() {
    
        JRequest::setVar( 'view', 'cpanel' );

        parent::display();
    }


}
?>

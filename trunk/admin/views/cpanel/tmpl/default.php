<?php
/**
 * jUpgrade
 *
 * @version			$Id$
 * @package			MatWare
 * @subpackage	com_jupgrade
 * @author      Matias Aguirre <maguirre@matware.com.ar>
 * @link        http://www.matware.com.ar
 * @license			GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

$version = "v{$this->version}";

JHTML::_( 'behavior.mootools' );

$params = &JComponentHelper::getParams( 'com_jupgrade' );

$document = &JFactory::getDocument();
$document->addScript('components/com_jupgrade/js/functions.js' );
$document->addScript('components/com_jupgrade/js/dwProgressBar.js' );

// Checking for IE
jimport('joomla.environment.browser');

$browser = new JBrowser();
$version = $browser->getBrowser();

if ($version == 'msie') {
	$document->addScript('components/com_jupgrade/js/migrate.ie.js' );
}else{
	$document->addScript('components/com_jupgrade/js/migrate.js' );
}

?>
<link rel="stylesheet" type="text/css" href="components/com_jupgrade/css/jupgrade.css" />
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Orbitron">
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Puritan">
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Tinos">

<script type="text/javascript">

window.addEvent('domready', function() {

	$('checks').setStyle('display', 'none');
	$('download').setStyle('display', 'none');
	$('decompress').setStyle('display', 'none');
	$('install').setStyle('display', 'none');
	$('migration').setStyle('display', 'none');
	$('templates').setStyle('display', 'none');
	$('extensions').setStyle('display', 'none');
	$('done').setStyle('display', 'none');

	$('update').skip_download = <?php echo $params->get("skip_download") ? $params->get("skip_download") : 0; ?>;
	$('update').skip_decompress =  <?php echo $params->get("skip_decompress") ? $params->get("skip_decompress") : 0; ?>;

  $('update').addEvent('click', checks);

});

</script>

<table width="100%">
<tr>
	<td width="100%" valign="top" align="center">
		<div id="info">
			<div id="info_title"><?php echo JText::_( 'jUpgrade' ); ?></div>
			<div id="info_version"><?php echo JText::_( 'Version' ).' <b>'.$this->version.'</b>'; ?></div>
			<div id="info_thanks">
				<p>
					<?php echo JText::_( 'Developed by' ); ?> <i><a href="http://www.matware.com.ar/">Matware &#169;</a></i>  Copyleft 2006-2011<br>
					Licensed as <a href="http://www.gnu.org/licenses/old-licenses/gpl-2.0.html"><i>GNU General Public License v2</i></a><br>
				</p>
				<p>
					<a href="http://www.matware.com.ar/joomla/jupgrade.html">Project Site</a><br>
					<a href="http://www.matware.com.ar/foros/jupgrade.html">Project Support</a><br>
					<a href="http://www.joomstew.com/matias-and-beyond-2011">Want to donate?</a><br>
				<p>

			</div>
		</div>
		<div id="update">
			<img src="components/com_jupgrade/images/update.png" align="middle" border="0"/><br />
			<h2><?php echo JText::_( 'START UPGRADE' ); ?></h2>
		</div>
		<div>
			<div id="debug"></div>
		</div>
		<div id="checks">
			<p class="text"><?php echo JText::_( 'Checking...' ); ?></p>
			<div id="pb0"></div>
			<div><i><small><span id="checkstatus"><?php echo JText::_( 'Preparing for check...' ); ?></span></i></small></div>
		</div>
		<div id="download">
			<p class="text"><?php echo JText::_( 'Downloading Joomla 1.6...' ); ?></p>
			<div id="pb1"></div>
			<div id="downloadtext">
        <i><small><b><span id="currBytes">0</span></b> bytes / <b>
        <span id="totalBytes">0</span></b> bytes</small></i>
      </div>
			<span id="downloadstatus"></span>
    </div>
		<div id="decompress">
			<p class="text"><?php echo JText::_( 'Decompressing package...' ); ?></p>
			<div id="pb2"></div>
			<span id="decompressstatus"></span>
		</div>
		<div id="install">
			<p class="text"><?php echo JText::_( 'Installing Joomla 1.6...' ); ?></p>
			<div id="pb3"></div>
		</div>
		<div id="migration">
			<p class="text"><?php echo JText::_( 'Upgrading progress...' ); ?></p>
			<div id="pb4"></div>
			<div><i><small><span id="status"><?php echo JText::_( 'Preparing for migration...' ); ?></span></i></small></div>
		</div>
		<div id="templates">
			<p class="text"><?php echo JText::_( 'Upgrading templates...' ); ?></p>
			<div id="pb5"></div>
		</div>
		<div id="extensions">
			<p class="text"><?php echo JText::_( 'Upgrading 3rd extensions...' ); ?></p>
			<div id="pb6"></div>
			<div><span id="status_ext"><?php echo JText::_( 'COMING SOON' ); ?></span></div>
		</div>
		<div id="done">
			<h2><?php echo JText::_( 'Joomla 1.6 Upgrade Finished!' ); ?></h2>
			<p class="text">
				<?php echo JText::_( 'You can check your new site here' ); ?>:&nbsp;
				<a href="<?php echo JURI::root(); ?>jupgrade/" target="_blank"><?php echo JText::_( 'Site' ); ?></a> and
				<a href="<?php echo JURI::root(); ?>jupgrade/administrator/" target="_blank"><?php echo JText::_( 'Administrator' ); ?></a>
			</p>
		</div>
   </tr>
</table>
<form action="index.php?option=com_jupgrade" method="post" name="adminForm">
<input type="hidden" name="option" value="com_jupgrade" />
<input type="hidden" name="task" value="" />
<input type="hidden" id="count" value="" />
</form>


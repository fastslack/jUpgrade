<?php
/**
 * jUpgrade
 *
 * @version		$Id$
 * @package		MatWare
 * @subpackage	com_jupgrade
 * @copyright	Copyright 2006 - 2011 Matias Aguire. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @author		Matias Aguirre <maguirre@matware.com.ar>
 * @link		http://www.matware.com.ar
 */

// No direct access.
defined('_JEXEC') or die;

$version = "v{$this->version}";

JHTML::_('behavior.mootools');

// Check if "System - Mootools Upgrade" is enabled
$mtupgrade = JPluginHelper::isEnabled( 'system', 'mtupgrade' );

// get params
$params		= $this->params;

// Determine which package is being downloaded
$mode	= $params->get("mode");

// Set the correct package name
if ($mode == 0) {
	$package = 'Joomla 1.6';
} else if ($mode == 1) {
	$package = 'Joomla 1.7';
} else if ($mode == 2) {
	$package = 'Molajo';
}

// get document to add scripts
$document	= JFactory::getDocument();
$document->addScript('components/com_jupgrade/js/dwProgressBar.js');

// Checking for IE
jimport('joomla.environment.browser');

$browser = new JBrowser();
$version = $browser->getBrowser();

if ($version == 'msie') {
	$document->addScript('components/com_jupgrade/js/migrate.ie.js');
}
else{
	$document->addScript('components/com_jupgrade/js/migrate.js');
}

$document->addStyleSheet("components/com_jupgrade/css/jupgrade.css");
$document->addStyleSheet("http://fonts.googleapis.com/css?family=Orbitron");
$document->addStyleSheet("http://fonts.googleapis.com/css?family=Puritan");
$document->addStyleSheet("http://fonts.googleapis.com/css?family=Tinos");
$document->addStyleSheet("http://fonts.googleapis.com/css?family=Paytone+One");
?>
<script type="text/javascript">

window.addEvent('domready', function() {

	var jupgrade = new jUpgrade({
    mode: <?php echo $params->get("mode") ?>,
    directory: '<?php echo $params->get("directory") ?>',
    prefix_old: '<?php echo $params->get("prefix_old") ?>',
    prefix_new: '<?php echo $params->get("prefix_new") ?>',
    skip_checks: <?php echo $params->get("skip_checks") ?>,
    skip_download: <?php echo $params->get("skip_download") ?>,
    skip_decompress: <?php echo $params->get("skip_decompress") ?>,
    positions: <?php echo $params->get("positions") ?>,
    debug: <?php echo $params->get("debug") ?>
	});

});

</script>

<table width="100%">
	<tbody>
		<tr>
			<td width="100%" valign="top" align="center">
				<div id="info">
					<div id="info_title"><?php echo JText::_('jUpgrade'); ?></div>
					<div id="info_version"><?php echo JText::_('Version').' <b>'.$this->version.'</b>'; ?></div>
					<div id="info_thanks">
						<p>
							<?php echo JText::_('Developed by'); ?> <i><a href="http://www.matware.com.ar/">Matware &#169;</a></i>  Copyleft 2006-2011<br />
							Licensed as <a href="http://www.gnu.org/licenses/old-licenses/gpl-2.0.html"><i>GNU General Public License v2</i></a><br />
						</p>
						<p>
							<a href="http://matware.com.ar/joomla-projects/jupgrade.html">Project Site</a><br />
							<a href="http://matware.com.ar/forum/listcat/categories.html">Project Community</a><br />
							<a href="http://matware.com.ar/forum/projects/jupgrade/jupgrade-f-a-q.html">FAQ</a><br />
							<a href="http://www.matware.com.ar/people-who-support-this-project.html">People who support this project</a><br />
							<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
								<input type="hidden" name="cmd" value="_s-xclick" />
								<input type="hidden" name="hosted_button_id" value="CZUMWRZ5E8DKS" />
								<input id="donate" type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" />
								<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" style="border: none;" />
							</form>
						</p>
					</div>
				</div>
<?php if ($mtupgrade == false) { ?>
				<div id="error">
					<a href="index.php?option=com_plugins"><?php echo JText::_('Mootools 1.2 not loaded. Please enable "System - Mootools Upgrade" plugin.'); ?></a>
				</div>
<?php } ?>

				<div id="update">
					<br /><img src="components/com_jupgrade/images/update.png" align="middle" border="0"/><br />
					<h2><?php echo JText::_('START UPGRADE'); ?></h2>
				</div>

				<div id="checks">
					<p class="text"><?php echo JText::_('Checking...'); ?></p>
					<div id="pb0"></div>
					<div><small><i><span id="checkstatus"><?php echo JText::_('Preparing for check...'); ?></span></i></small></div>
				</div>

				<div id="download">
					<p class="text"><?php echo JText::_('Downloading '.$package.'...'); ?></p>
					<div id="pb1"></div>
					<div id="downloadtext">
						<i><small><b><span id="currBytes">0</span></b> bytes /
						<b><span id="totalBytes">0</span></b> bytes</small></i>
					</div>
					<span id="downloadstatus"></span>
				</div>

				<div id="decompress">
					<p class="text"><?php echo JText::_('Decompressing package...'); ?></p>
					<div id="pb2"></div>
					<span id="decompressstatus"></span>
				</div>

				<div id="install">
					<p class="text"><?php echo JText::_('Installing '.$package.'...'); ?></p>
					<div id="pb3"></div>
				</div>

				<div id="migration">
					<p class="text"><?php echo JText::_('Upgrading progress...'); ?></p>
					<div id="pb4"></div>
					<div><small><i><span id="status"><?php echo JText::_('Preparing for migration'); ?></span></i></small></div>
				</div>

				<div id="templates">
					<p class="text"><?php echo JText::_('Upgrading templates...'); ?></p>
					<div id="pb5"></div>
				</div>

				<div id="extensions">
					<p class="text"><?php echo JText::_('Upgrading 3rd extensions...'); ?></p>
					<div id="pb6"></div>
					<div><small><i><span id="status_ext"><?php echo JText::_('Preparing for 3rd extensions migration'); ?></span></i></small></div>
				</div>

				<div id="done">
					<h2><?php echo JText::_($package.' Upgrade Finished!'); ?></h2>
					<p class="text">
						<?php echo JText::_('You can check your new site here'); ?>:&nbsp;
						<a href="<?php echo JURI::root(); ?>jupgrade/" target="_blank"><?php echo JText::_('Site'); ?></a> and
						<a href="<?php echo JURI::root(); ?>jupgrade/administrator/" target="_blank"><?php echo JText::_('Administrator'); ?></a>
					</p>
				</div>

				<div>
					<div id="debug"></div>
				</div>

			</td>
		</tr>
	</tbody>
</table>

<form action="index.php?option=com_jupgrade" method="post" name="adminForm">
	<input type="hidden" name="option" value="com_jupgrade" />
	<input type="hidden" name="task" value="" />
</form>


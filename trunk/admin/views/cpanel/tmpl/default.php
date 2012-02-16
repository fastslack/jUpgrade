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
if ($mode == 1) {
	$package = 'Joomla 2.5';
} else if ($mode == 2) {
	$package = 'Molajo';
}

// get document to add scripts
$document	= JFactory::getDocument();
$document->addScript('components/com_jupgrade/js/dwProgressBar.js');

$document->addScript('components/com_jupgrade/js/migrate.js');
$document->addStyleSheet("components/com_jupgrade/css/jupgrade.css");
?>
<script type="text/javascript">

window.addEvent('domready', function() {

	/* Init jUpgrade */
	var jupgrade = new jUpgrade({
    mode: <?php echo $params->get("mode") ? $params->get("mode") : 1; ?>,
    directory: '<?php echo $params->get("directory") ?>',
    prefix_old: '<?php echo $params->get("prefix_old") ?>',
    prefix_new: '<?php echo $params->get("prefix_new") ?>',
    skip_checks: <?php echo $params->get("skip_checks") ? $params->get("skip_checks") : 0; ?>,
    skip_download: <?php echo $params->get("skip_download") ? $params->get("skip_download") : 0; ?>,
    skip_decompress: <?php echo $params->get("skip_decompress") ? $params->get("skip_decompress") : 0; ?>,
    skip_templates: <?php echo $params->get("skip_templates") ? $params->get("skip_templates") : 0; ?>,
    skip_extensions: <?php echo $params->get("skip_extensions") ? $params->get("skip_extensions") : 0; ?>,
    positions: <?php echo $params->get("positions") ? $params->get("positions") : 0; ?>,
    debug_php: <?php echo $params->get("debug_php") ? $params->get("debug_php") : 0; ?>,
    debug_js: <?php echo $params->get("debug_js") ? $params->get("debug_js") : 0; ?>
	});

	/* Debug */
	var debug_js = <?php echo $params->get("debug_js") ? $params->get("debug_js") : 0; ?>;
	var version = MooTools.version;

	if (debug_js > 0) {
		alert('Mootools version: '+version);
	}

});

</script>

<table width="100%">
	<tbody>
		<tr>
			<td width="100%" valign="top" align="center">
<?php if ($mtupgrade == false) { ?>
				<div id="error">
					<a href="index.php?option=com_plugins"><?php echo JText::_('Mootools 1.2 not loaded. Please enable "System - Mootools Upgrade" plugin.'); ?></a>
				</div>
<?php }else { ?>

				<div id="update">
					<br /><img src="components/com_jupgrade/images/update.png" align="middle" border="0"/><br />
					<h2><?php echo JText::_('START UPGRADE'); ?></h2><br />
				</div>

				<div id="checks">
					<p class="text"><?php echo JText::_('Checking and cleaning...'); ?></p>
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
					<p class="text"><?php echo JText::_('Copying templates...'); ?></p>
					<div id="pb5"></div>
				</div>

				<div id="files">
					<p class="text"><?php echo JText::_('Copying images/media files...'); ?></p>
					<div id="pb6"></div>
				</div>

				<div id="extensions">
					<p class="text"><?php echo JText::_('Upgrading 3rd extensions...'); ?></p>
					<div id="pb7"></div>
					<div><small><i><span id="status_ext"><?php echo JText::_('Preparing for 3rd extensions migration'); ?></span></i></small></div>
				</div>

				<div id="done">
					<h2><?php echo JText::_($package.' Upgrade Finished!'); ?></h2>
					<p class="text">
						<?php echo JText::_('You can check your new site here'); ?>:<br />
						<a href="<?php echo JURI::root().$params->get('directory'); ?>/" target="_blank"><?php echo JText::_('Site'); ?></a> and
						<a href="<?php echo JURI::root().$params->get('directory'); ?>/administrator/" target="_blank"><?php echo JText::_('Administrator'); ?></a>
					</p>
				</div>
				<div id="info">
					<div id="info_version"><?php echo JText::_('jUpgrade'); ?> <?php echo JText::_('Version').' <b>'.$this->version.'</b>'; ?></div>
					<div id="info_thanks">
						<p>
							<?php echo JText::_('Developed by'); ?> <i><a href="http://www.matware.com.ar/">Matware &#169;</a></i>  Copyleft 2006-2012<br />
							Licensed as <a href="http://www.gnu.org/licenses/old-licenses/gpl-2.0.html"><i>GNU General Public License v2</i></a><br />
						</p>
						<p>
							<a href="http://redcomponent.com/jupgrade">Project Site</a> /
							<a href="http://redcomponent.com/forum/92-jupgrade">Project Community</a> /
							<a href="http://redcomponent.com/forum/92-jupgrade/102880-jupgrade-faq">FAQ</a><br />
						</p>
					</div>
				</div>
<?php } ?>

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


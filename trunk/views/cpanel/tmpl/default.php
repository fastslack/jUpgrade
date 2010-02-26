<?php
/**
 * jUpgrade
 *
 * @author      Matias Aguirre
 * @email       maguirre@matware.com.ar
 * @url         http://www.matware.com.ar
 * @license     GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

$version = "v{$this->version}";

$document = &JFactory::getDocument();
$document->addScript('components/com_jupgrade/js/jquery-1.4.2.min.js' );
$document->addScript('components/com_jupgrade/js/jquery.progressbar.js' );
$document->addScript('components/com_jupgrade/js/jquery.timers-1.2.js' );
//$document->addCustomTag( '<script type="text/javascript">jQuery.noConflict();</script>' );
?>
<link rel="stylesheet" type="text/css" href="components/com_jupgrade/css/jupgrade.css" />
<script type="text/javascript" src="components/com_jupgrade/js/functions.js"></script>
<!--
			MOOTOOLS


<script type="text/javascript" src="../media/system/js/mootools.js"></script>
<script type="text/javascript">

window.addEvent('domready', function() {
    //alert("The DOM is ready.");

//alert($('decompress'));

	$('update').addEvent('click', function(){
			//alert('dsds');
		//event.stop();
		//make the ajax call
		var req = new Request({
			method: 'get',
			url: 'administrator/components/com_jupgrade/includes/download.php',
			data: { 'do' : '1' },
			onRequest: function() { alert('Request made. Please wait...'); },
			onComplete: function(response) { alert('Response: ' + response); }
		}).send();

	});

	$('progress').setStyle('display', 'none');
	$('decompress').setStyle('display', 'none');
	$('migration').setStyle('display', 'none');
	$('install').setStyle('display', 'none');
	$('done').setStyle('display', 'none');


});


function download(event){
	alert('dssdsfaqa');
	//new ajax('components/com_jupgrade/includes/getfilesize.php',{postBody:'answerme=ok', onComplete: showResponse, update:'container'}).request();

	//prevent the page from changing
	//event.stop();
	//make the ajax call
	var req = new Request({
		method: 'get',
		url: 'components/com_jupgrade/includes/download.php',
		data: { 'do' : '1' },
		onRequest: function() { alert('Request made. Please wait...'); },
		onComplete: function(response) { alert('Response: ' + response); }
	}).send();


	return false;
};

function showResponse(request){
    alert(document);
};
</script>
-->

<!--
<script type="text/javascript" src="components/com_jupgrade/js/jquery.js"></script>
<script type="text/javascript" src="components/com_jupgrade/js/jquery.progressbar.js"></script>
<script type="text/javascript" src="components/com_jupgrade/js/jquery.timers-1.2.js"></script>
-->

<script type="text/javascript">
	$(document).ready(function(){
		//jQuery.noConflict();

		$("#download").hide("fast");
		$("#decompress").hide("fast");
		$("#migration").hide("fast");
		$("#install").hide("fast");
		$("#done").hide("fast");

		$("#update").click(download);
	});

	function progress(event) {

		$("#test").everyTime('1s',function(i) {
			$.ajax({
				type: "GET",
				url: "components/com_jupgrade/includes/getfilesize.php",
				success: function(msg){
					var ex = explode(',', msg);
					$('#currBytes').html(ex[1]);
					$('#totalBytes').html(ex[2]);
					//alert(msg);
					if(ex[1] < ex[2]){
						//alert(msg);
						$('#pb1').progressBar(ex[0]);
					}else if(ex[1] == ex[2]){
						$('#pb1').progressBar(ex[0]);
						$('#test').stopTime("hide");
						return false;
					}
				}
			});

		});

		//event.preventDefault();
	}

	function download(event) {

		$("#pb1").progressBar();

		$("#update").slideToggle("slow");
		$("#download").slideToggle("slow");

		$.ajax({
			type: "GET",
			url: "components/com_jupgrade/includes/download.php",
			beforeSend: function (XMLHttpRequest) {
				$("#pb1").progressBar();
				progress();
			},
			success: function(msg){
				//alert(msg);
				decompress();
			}
		});

		//event.preventDefault();
	}
	
	function decompress(event) {

		$("#pb2").progressBar();
		$("#decompress").slideToggle("slow");
		
		$.get("components/com_jupgrade/includes/decompress.php", { root: "<?php echo JPATH_SITE; ?>"},
			function(data){
				//alert(data);
				for(i=0;i<=100;i++){
					$('#pb2').progressBar(i);
				}
				install();
		});
		
		//event.preventDefault();
	}	

	function install(event) {

		$("#pb3").progressBar();
		$("#install").slideToggle("slow");
		//$('#status').html('Installing database');

		$.get("components/com_jupgrade/includes/install_config.php", { root: "<?php echo JPATH_SITE; ?>"},
			function(data){
				//alert(data);
				$('#pb3').progressBar(50);
				//$('#status').html('Creating configuration');
				$.get("components/com_jupgrade/includes/install_db.php", { root: "<?php echo JPATH_SITE; ?>"},
					function(data){
						//alert(data);
						$('#pb3').progressBar(100);
						migration();
				});

		});

		//event.preventDefault();
	}	

	function migration(event) {

		$("#pb4").progressBar();
		$("#migration").slideToggle("slow");
		
		$.get("components/com_jupgrade/includes/migrate_users.php", { root: "<?php echo JPATH_SITE; ?>"},
			function(data){
			//alert(data);
			$('#status').html('Migrating Modules...');
			$('#pb4').progressBar(10);
			$.get("components/com_jupgrade/includes/migrate_modules.php", { root: "<?php echo JPATH_SITE; ?>"},
				function(data){
				//alert(data);
				$('#status').html('Migrating Categories...');
				$('#pb4').progressBar(20);
				$.get("components/com_jupgrade/includes/migrate_categories.php", { root: "<?php echo JPATH_SITE; ?>"},
					function(data){
					alert(data);
					$('#status').html('Migrating Content...');
					$('#pb4').progressBar(30);
					$.get("components/com_jupgrade/includes/migrate_content.php", { root: "<?php echo JPATH_SITE; ?>"},
						function(data){
						//alert(data);
						$('#status').html('Migrating Menus...');
						$('#pb4').progressBar(40);
						$.get("components/com_jupgrade/includes/migrate_menus.php", { root: "<?php echo JPATH_SITE; ?>"},
							function(data){
							//alert(data);
							$('#status').html('Migrating Banners...');
							$('#pb4').progressBar(50);
							$.get("components/com_jupgrade/includes/migrate_banners.php", { root: "<?php echo JPATH_SITE; ?>"},
								function(data){
								//alert(data);
								$('#status').html('Migrating Contacts...');
								$('#pb4').progressBar(60);
								$.get("components/com_jupgrade/includes/migrate_contacts.php", { root: "<?php echo JPATH_SITE; ?>"},
									function(data){
									//alert(data);
									$('#status').html('Migrating News Feeds...');
									$('#pb4').progressBar(70);
									$.get("components/com_jupgrade/includes/migrate_newsfeeds.php", { root: "<?php echo JPATH_SITE; ?>"},
										function(data){
										//alert(data);
										$('#status').html('Migrating Polls...');
										$('#pb4').progressBar(80);
										$.get("components/com_jupgrade/includes/migrate_polls.php", { root: "<?php echo JPATH_SITE; ?>"},
											function(data){
											//alert(data);
											$('#status').html('Migrating WebLinks...');
											$('#pb4').progressBar(90);
											$.get("components/com_jupgrade/includes/migrate_weblinks.php", { root: "<?php echo JPATH_SITE; ?>"},
												function(data){
												//alert(data);
												$('#status').html('Done');
												$('#pb4').progressBar(100);
												done();
											});
										});
									});
								});
							});
						});
					});
				});
			});
		});
		
		//event.preventDefault();
	}	

	function done(event) {
		$("#done").slideToggle("slow");
	}	
</script>

<table width="100%">
<tr>
	<td width="100%" valign="top" align="center">
		<div id="update">
			<img src="components/com_jupgrade/images/update.png" align="middle" border="0"/><br />
			<h2><?php echo JText::_( 'START UPGRADE' ); ?></h2>
		</div>
		<div id="download">
			<p class="text"><?php echo JText::_( 'Downloading Joomla 1.6...' ); ?></p>
			<span id="pb1" class="progressBar"></span>
			<div><i><small><b><span id="currBytes">0</span></b> bytes / <b><span id="totalBytes">0</span></b> bytes</small></i></div>
		</div>
		<div id="decompress">
			<p class="text"><?php echo JText::_( 'Decompressing package...' ); ?></p>
			<span id="pb2" class="progressBar"></span>
		</div>
		<div id="install">
			<p class="text"><?php echo JText::_( 'Installing Joomla 1.6...' ); ?></p>
			<span id="pb3" class="progressBar"></span>
		</div>
		<div id="migration">
			<p class="text"><?php echo JText::_( 'Upgrade progress...' ); ?></p>
			<span id="pb4" class="progressBar"></span>
			<div><i><small><span id="status"><?php echo JText::_( 'Migrating Users...' ); ?></span></i></small></div>
		</div>
		<div id="done">
			<h2><?php echo JText::_( 'Joomla 1.6 Upgrade Finished!' ); ?></h2>
			<p class="text">
				<?php echo JText::_( 'You can check your new site here: ' ); ?>
				<a href="<?php echo JURI::root(); ?>jupgrade/" target="_blank"><?php echo JText::_( 'Site' ); ?></a> and 
				<a href="<?php echo JURI::root(); ?>jupgrade/administrator/" target="_blank"><?php echo JText::_( 'Administrator' ); ?></a>
			</p>
		</div>
		<div id="test"></div>
   </tr>
</table>

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


<script type="text/javascript" src="components/com_jupgrade/js/jquery.js"></script>
<script type="text/javascript" src="components/com_jupgrade/js/jquery.progressbar.js"></script>
<script type="text/javascript" src="components/com_jupgrade/js/jquery.timers-1.2.js"></script>

<script type="text/javascript">
	$(document).ready(function(){
		//jQuery.noConflict();

		$("#progress").hide("fast");
		$("#decompress").hide("fast");
		$("#migration").hide("fast");
		$("#install").hide("fast");
		$("#done").hide("fast");

		$("#update").click(download);
	});

	function progress(event) {

		$("#test").everyTime(10,function(i) {
			$.ajax({
				type: "GET",
				url: "components/com_jupgrade/includes/getfilesize.php",
				success: function(msg){
					var ex = explode(',', msg);
					//alert(ex[0]);
					if(ex[1] < ex[2]){
						//alert(msg);
						$('#pb1').progressBar(ex[0]);
					}else if(ex[1] == ex[2]){
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
		$("#progress").slideToggle("slow");

		$.ajax({
			type: "GET",
			url: "components/com_jupgrade/includes/download.php",
			beforeSend: function (XMLHttpRequest) {
				$("#pb1").progressBar();
				progress();
			},
			success: function(msg){
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

		$.get("components/com_jupgrade/includes/install_db.php", { root: "<?php echo JPATH_SITE; ?>"},
			function(data){
				//alert(data);
				$('#pb3').progressBar(50);
				//$('#status').html('Creating configuration');
				$.get("components/com_jupgrade/includes/install_config.php", { root: "<?php echo JPATH_SITE; ?>"},
					function(data){
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
				$('#pb4').progressBar(11);
				$.get("components/com_jupgrade/includes/migrate_categories.php", { root: "<?php echo JPATH_SITE; ?>"},
					function(data){
						//alert(data);
						$('#pb4').progressBar(22);
						done();
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
		<div id="progress">
			<p><?php echo JText::_( 'Downloading Joomla 1.6...' ); ?></p>
			<span id="pb1" class="progressBar"></span>
		</div>
		<div id="decompress">
			<p><?php echo JText::_( 'Decompressing package...' ); ?></p>
			<span id="pb2" class="progressBar"></span>
		</div>
		<div id="install">
			<p><?php echo JText::_( 'Installing Joomla 1.6...' ); ?></p>
			<span id="pb3" class="progressBar"></span>
		</div>
		<div id="migration">
			<p><?php echo JText::_( 'Migration progress...' ); ?></p>
			<span id="pb4" class="progressBar"></span>
			<span id="status"></span>
		</div>
		<div id="done">
			<h2><?php echo JText::_( 'Joomla 1.6 Upgrade Finished!' ); ?></h2>
			<p>
				<?php echo JText::_( 'You can check your new site here: ' ); ?>
				<a href="<?php echo JURI::root(); ?>jupgrade/"><?php echo JText::_( 'Site' ); ?></a> and 
				<a href="<?php echo JURI::root(); ?>jupgrade/administrator/"><?php echo JText::_( 'Administrator' ); ?></a>
			</p>
		</div>
		<div id="test"></div>
   </tr>
</table>
